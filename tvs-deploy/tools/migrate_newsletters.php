<?php
/**
 * TVS Newsletter Migration Script
 * 
 * Migrates existing newsletter PDFs from directory structure into the database.
 * 
 * Usage: php tools/migrate_newsletters.php [--dry-run] [--force]
 * 
 * Directory structure: newsletters/<year>/tvsnews<month><year>.pdf
 * Example: newsletters/2023/tvsnews0923.pdf
 * 
 * This script will:
 * 1. Scan all newsletter directories
 * 2. Parse filenames to extract month/year
 * 3. Validate file structure and naming
 * 4. Insert into database with proper metadata
 * 5. Set the most recent newsletter as current
 * 
 * @author TVS Web Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Command line argument parsing
$dryRun = in_array('--dry-run', $argv);
$force = in_array('--force', $argv);
$verbose = in_array('--verbose', $argv);

// Get base directory from command line (default: current directory)
$baseDir = getBaseDirectory($argv);

/**
 * Parse command line arguments to get base directory
 */
function getBaseDirectory($argv) {
    // Look for --base-dir or -b parameter
    for ($i = 1; $i < count($argv); $i++) {
        if ($argv[$i] === '--base-dir' && isset($argv[$i + 1])) {
            return rtrim($argv[$i + 1], '/');
        }
        if (substr($argv[$i], 0, 2) === '-b' && strlen($argv[$i]) > 2) {
            return rtrim(substr($argv[$i], 2), '/');
        }
        if (substr($argv[$i], 0, 2) === '-b' && isset($argv[$i + 1])) {
            return rtrim($argv[$i + 1], '/');
        }
    }
    
    // Default to current directory
    return getcwd();
}

// Colors for output
$colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'reset' => "\033[0m",
    'bold' => "\033[1m"
];

function colorize($text, $color) {
    global $colors;
    return $colors[$color] . $text . $colors['reset'];
}

function logMessage($message, $type = 'info') {
    global $colors, $verbose;
    
    if ($type === 'info' && !$verbose) {
        return;
    }
    
    $prefix = match($type) {
        'success' => colorize('[✓]', 'green'),
        'error' => colorize('[✗]', 'red'),
        'warning' => colorize('[!]', 'yellow'),
        'info' => colorize('[i]', 'blue'),
        default => colorize('[ ]', 'reset')
    };
    
    echo $prefix . ' ' . $message . PHP_EOL;
}

function validateNewsletterFile($filePath, $year, $month) {
    // Check if file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['valid' => false, 'error' => 'File not accessible'];
    }
    
    // Check file size
    $size = filesize($filePath);
    if ($size === 0) {
        return ['valid' => false, 'error' => 'File is empty'];
    }
    
    // Check if it's a PDF (basic check)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    if ($mimeType !== 'application/pdf') {
        return ['valid' => false, 'error' => 'Not a valid PDF file'];
    }
    
    // Validate year and month ranges
    if ($year < 1990 || $year > 2100) {
        return ['valid' => false, 'error' => 'Invalid year: ' . $year];
    }
    
    if ($month < 1 || $month > 12) {
        return ['valid' => false, 'error' => 'Invalid month: ' . $month];
    }
    
    return ['valid' => true, 'size' => $size];
}

function parseNewsletterFilename($filename) {
    // Expected format: tvsnewsMMYY.pdf
    if (!preg_match('/^tvsnews(\d{2})(\d{2})\.pdf$/i', $filename, $matches)) {
        return ['valid' => false, 'error' => 'Invalid filename format'];
    }
    
    $month = (int)$matches[1];
    $yearSuffix = (int)$matches[2];
    
    // Determine full year (assuming 1990-2099 range)
    $currentYear = (int)date('Y');
    $currentCentury = floor($currentYear / 100) * 100;
    
    $fullYear = $currentCentury + $yearSuffix;
    
    // Handle edge case for years near century boundary
    if ($fullYear > $currentYear + 10) {
        $fullYear -= 100;
    }
    
    return [
        'valid' => true,
        'month' => $month,
        'year' => $fullYear
    ];
}

function getNewsletterPath($year, $month, $filename) {
    return "newsletters/{$year}/{$filename}";
}

function processNewsletterFile($filePath, $filename, $dryRun = false) {
    logMessage("Processing: {$filename}", 'info');
    
    // Parse filename to get month/year
    $parseResult = parseNewsletterFilename($filename);
    if (!$parseResult['valid']) {
        logMessage("  {$parseResult['error']}", 'error');
        return false;
    }
    
    $month = $parseResult['month'];
    $year = $parseResult['year'];
    
    // Validate the file
    $validation = validateNewsletterFile($filePath, $year, $month);
    if (!$validation['valid']) {
        logMessage("  {$validation['error']}", 'error');
        return false;
    }
    
    // Build database record
    $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
    $yearStr = substr($year, 2, 2);
    $expectedFilename = "tvsnews{$monthStr}{$yearStr}.pdf";
    
    if ($filename !== $expectedFilename) {
        logMessage("  Warning: Filename doesn't match expected pattern (expected: {$expectedFilename})", 'warning');
    }
    
    $relativePath = getNewsletterPath($year, $month, $filename);
    $fullPath = ROOT_PATH . '/' . $relativePath;
    
    // Check if newsletter already exists in database
    $existing = dbQueryOne(
        "SELECT id, filename, file_path FROM newsletters WHERE year = ? AND month = ?",
        [$year, $month]
    );
    
    if ($existing) {
        // Check if it's the same file
        if ($existing['filename'] === $filename && $existing['file_path'] === $relativePath) {
            logMessage("  Already exists in database (same file)", 'info');
            return true;
        } else {
            logMessage("  Newsletter for {$year}-{$monthStr} already exists with different file", 'warning');
            logMessage("    Existing: {$existing['filename']} at {$existing['file_path']}", 'info');
            logMessage("    New: {$filename} at {$relativePath}", 'info');
            return false;
        }
    }
    
    // Prepare database record
    $record = [
        'year' => $year,
        'month' => $month,
        'filename' => $filename,
        'file_path' => $relativePath,
        'file_type' => 'pdf',
        'file_size' => $validation['size'],
        'uploaded_by' => 1, // System user
        'uploaded_at' => date('Y-m-d H:i:s'),
        'is_current' => 0
    ];
    
    if ($dryRun) {
        logMessage("  Would insert: {$year}-{$monthStr} - {$filename} ({$validation['size']} bytes)", 'info');
        return true;
    }
    
    // Insert into database
    try {
        $newsletterId = dbInsert(
            "INSERT INTO newsletters (year, month, filename, file_path, file_type, file_size, uploaded_by, uploaded_at, is_current)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$record['year'], $record['month'], $record['filename'], $record['file_path'], 
             $record['file_type'], $record['file_size'], $record['uploaded_by'], 
             $record['uploaded_at'], $record['is_current']]
        );
        
        logMessage("  Successfully inserted: {$year}-{$monthStr} (ID: {$newsletterId})", 'success');
        
        // Log the migration action
        logAudit(1, 'migrate_newsletter', 'newsletters', $newsletterId, '', json_encode($record));
        
        return $newsletterId;
        
    } catch (Exception $e) {
        logMessage("  Database error: {$e->getMessage()}", 'error');
        return false;
    }
}

function setMostRecentAsCurrent($dryRun = false) {
    logMessage("Setting most recent newsletter as current...", 'info');
    
    // Find the most recent newsletter
    $mostRecent = dbQueryOne(
        "SELECT id, year, month FROM newsletters 
         WHERE file_type = 'pdf' 
         ORDER BY year DESC, month DESC 
         LIMIT 1"
    );
    
    if (!$mostRecent) {
        logMessage("  No newsletters found to set as current", 'warning');
        return false;
    }
    
    if ($dryRun) {
        logMessage("  Would set newsletter ID {$mostRecent['id']} ({$mostRecent['year']}-{$mostRecent['month']}) as current", 'info');
        return true;
    }
    
    try {
        // Clear current flag from all newsletters
        dbExecute("UPDATE newsletters SET is_current = 0");
        
        // Set most recent as current
        dbExecute("UPDATE newsletters SET is_current = 1 WHERE id = ?", [$mostRecent['id']]);
        
        logMessage("  Set newsletter ID {$mostRecent['id']} ({$mostRecent['year']}-{$mostRecent['month']}) as current", 'success');
        
        return true;
        
    } catch (Exception $e) {
        logMessage("  Database error setting current: {$e->getMessage()}", 'error');
        return false;
    }
}

function main() {
    global $dryRun, $force, $verbose, $baseDir;
    
    echo colorize("\n=== TVS Newsletter Migration Script ===\n", 'bold');
    echo colorize("Starting newsletter migration...\n\n", 'blue');
    
    if ($dryRun) {
        echo colorize("DRY RUN MODE - No changes will be made\n", 'yellow');
    }
    
    if ($verbose) {
        echo colorize("Verbose mode enabled\n", 'blue');
    }
    
    echo PHP_EOL;
    
    // Newsletter directory path
    $newsletterDir = $baseDir . '/newsletters';
    
    if (!is_dir($newsletterDir)) {
        logMessage("Newsletter directory not found: {$newsletterDir}", 'error');
        exit(1);
    }
    
    logMessage("Using base directory: {$baseDir}", 'info');
    logMessage("Newsletter directory: {$newsletterDir}", 'info');
    
    // Scan all year directories
    $yearDirs = glob($newsletterDir . '/*', GLOB_ONLYDIR);
    $totalFiles = 0;
    $processedFiles = 0;
    $failedFiles = 0;
    $existingFiles = 0;
    
    logMessage("Found " . count($yearDirs) . " year directories", 'info');
    
    foreach ($yearDirs as $yearDir) {
        $year = basename($yearDir);
        
        // Validate year directory name
        if (!is_numeric($year) || strlen($year) !== 4) {
            logMessage("Skipping invalid year directory: {$year}", 'warning');
            continue;
        }
        
        logMessage("Processing year: {$year}", 'info');
        
        // Find all PDF files in this year directory
        $pdfFiles = glob($yearDir . '/tvsnews*.pdf');
        
        if (empty($pdfFiles)) {
            logMessage("  No newsletter files found in {$year}", 'info');
            continue;
        }
        
        logMessage("  Found " . count($pdfFiles) . " newsletter files", 'info');
        
        foreach ($pdfFiles as $filePath) {
            $filename = basename($filePath);
            $totalFiles++;
            
            $result = processNewsletterFile($filePath, $filename, $dryRun);
            
            if ($result === true) {
                $processedFiles++;
            } elseif ($result === false) {
                $failedFiles++;
            } else {
                // Already exists
                $existingFiles++;
            }
        }
    }
    
    echo PHP_EOL;
    logMessage("Migration Summary:", 'bold');
    logMessage("  Total files scanned: {$totalFiles}", 'info');
    logMessage("  Successfully processed: {$processedFiles}", 'success');
    logMessage("  Already existed: {$existingFiles}", 'info');
    logMessage("  Failed to process: {$failedFiles}", 'error');
    
    // Set most recent as current
    if ($processedFiles > 0 || !$dryRun) {
        echo PHP_EOL;
        setMostRecentAsCurrent($dryRun);
    }
    
    echo PHP_EOL;
    
    if ($dryRun) {
        echo colorize("DRY RUN COMPLETE - Use without --dry-run to apply changes\n", 'yellow');
    } else {
        echo colorize("MIGRATION COMPLETE\n", 'green');
    }
    
    // Final statistics
    $totalInDb = dbQueryOne("SELECT COUNT(*) as count FROM newsletters WHERE file_type = 'pdf'")['count'];
    $currentCount = dbQueryOne("SELECT COUNT(*) as count FROM newsletters WHERE is_current = 1")['count'];
    
    echo PHP_EOL;
    logMessage("Final Database Statistics:", 'bold');
    logMessage("  Total newsletters in database: {$totalInDb}", 'info');
    logMessage("  Current newsletters: {$currentCount}", 'info');
    
    // Show years covered
    $years = dbQuery("SELECT DISTINCT year FROM newsletters WHERE file_type = 'pdf' ORDER BY year DESC");
    $yearList = array_column($years, 'year');
    logMessage("  Years covered: " . implode(', ', $yearList), 'info');
}

// Run the migration
try {
    main();
} catch (Exception $e) {
    logMessage("Fatal error: {$e->getMessage()}", 'error');
    exit(1);
}