<?php
/**
 * Test script for newsletter migration
 * 
 * This script creates a temporary test environment to validate
 * the migration script functionality without affecting production data.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Create test directory structure
$testDir = sys_get_temp_dir() . '/tvs_migration_test';
$testNewslettersDir = $testDir . '/newsletters';

echo "Creating test environment...\n";
echo "Test directory: {$testDir}\n";

// Clean up any existing test directory
if (is_dir($testDir)) {
    rrmdir($testDir);
}

// Create test structure
mkdir($testNewslettersDir . '/2023', 0755, true);
mkdir($testNewslettersDir . '/2024', 0755, true);

// Create test PDF files (empty files for testing)
$testFiles = [
    '2023/tvsnews0123.pdf',
    '2023/tvsnews0623.pdf', 
    '2024/tvsnews0124.pdf',
    '2024/tvsnews1224.pdf'
];

foreach ($testFiles as $file) {
    $fullPath = $testNewslettersDir . '/' . $file;
    file_put_contents($fullPath, "Test PDF content for {$file}");
    echo "Created test file: {$file}\n";
}

echo "\nTest files created successfully!\n";
echo "You can now test the migration script with:\n";
echo "php tools/migrate_newsletters.php --base-dir {$testDir} --dry-run --verbose\n";
echo "\nTo clean up test files, run:\n";
echo "rm -rf {$testDir}\n";

/**
 * Recursive directory removal
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}