<?php
/**
 * Migration script to import existing newsletters into the database
 * Run this once to populate the database with existing newsletter files
 */
require_once __DIR__ . '/config.php';

$pdo = getDB();
$imported = 0;
$skipped = 0;
$errors = [];

// Scan newsletters directory
$newslettersDir = NEWSLETTERS_DIR;

if (!is_dir($newslettersDir)) {
    die("Newsletters directory not found: {$newslettersDir}\n");
}

// Scan each year directory
$years = glob($newslettersDir . '/*', GLOB_ONLYDIR);

foreach ($years as $yearDir) {
    $year = basename($yearDir);
    
    if (!is_numeric($year) || $year < 1996 || $year > 2100) {
        continue;
    }
    
    // Find PDF files
    $pdfFiles = glob($yearDir . '/tvsnews*.pdf');
    
    foreach ($pdfFiles as $pdfFile) {
        $filename = basename($pdfFile);
        
        // Extract month and year from filename: tvsnewsMMYY.pdf
        if (preg_match('/tvsnews(\d{2})(\d{2})\.pdf$/', $filename, $matches)) {
            $month = intval($matches[1]);
            $fileYear = intval('20' . $matches[2]); // Convert YY to YYYY
            
            // Handle year 2000 correctly
            if ($matches[2] == '00') {
                $fileYear = 2000;
            }
            
            // Verify the file is in the correct year directory
            if ($fileYear != $year) {
                $errors[] = "Mismatch: File {$filename} in {$year} directory but filename suggests {$fileYear}";
                continue;
            }
            
            // Check if already in database
            $stmt = $pdo->prepare("SELECT id FROM newsletters WHERE year = ? AND month = ?");
            $stmt->execute([$fileYear, $month]);
            
            if ($stmt->fetch()) {
                $skipped++;
                echo "Skipped (already exists): {$filename}\n";
                continue;
            }
            
            // Insert into database
            try {
                $stmt = $pdo->prepare("INSERT INTO newsletters (year, month, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $fileYear,
                    $month,
                    $filename,
                    $pdfFile,
                    'migration_script'
                ]);
                
                $imported++;
                echo "Imported: {$filename} ({$fileYear}-{$month})\n";
            } catch (PDOException $e) {
                $errors[] = "Error importing {$filename}: " . $e->getMessage();
                echo "ERROR: {$filename} - " . $e->getMessage() . "\n";
            }
        } else {
            $errors[] = "Could not parse filename: {$filename}";
            echo "WARNING: Could not parse filename: {$filename}\n";
        }
    }
}

echo "\n=== Migration Complete ===\n";
echo "Imported: {$imported}\n";
echo "Skipped: {$skipped}\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

// Regenerate pages
echo "\nRegenerating newsletter pages...\n";
require_once __DIR__ . '/pdf_processor.php';
regenerateNewsletterPages($pdo);
echo "Done!\n";
?>

