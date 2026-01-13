<?php
/**
 * Setup script - Run this once to initialize the system
 * Usage: php setup.php
 */
require_once __DIR__ . '/config.php';

echo "Newsletter Admin System Setup\n";
echo "============================\n\n";

// Check directories
echo "1. Checking directories...\n";
$dirs = [
    dirname(DB_PATH) => 'Database directory',
    NEWSLETTERS_DIR => 'Newsletters directory',
    IMAGES_DIR => 'Images directory',
    dirname(NEWSCOVER_PATH) => 'Newscover directory'
];

foreach ($dirs as $dir => $name) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "   ✓ Created: {$name} ({$dir})\n";
        } else {
            echo "   ✗ Failed to create: {$name} ({$dir})\n";
            exit(1);
        }
    } else {
        echo "   ✓ Exists: {$name} ({$dir})\n";
    }
}

// Check write permissions
echo "\n2. Checking write permissions...\n";
foreach ($dirs as $dir => $name) {
    if (is_writable($dir)) {
        echo "   ✓ Writable: {$name}\n";
    } else {
        echo "   ✗ Not writable: {$name} (chmod 755 recommended)\n";
    }
}

// Initialize database
echo "\n3. Initializing database...\n";
try {
    initDatabase();
    echo "   ✓ Database initialized\n";
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check PDF processing tools
echo "\n4. Checking PDF processing tools...\n";
$tools = [
    IMAGEMAGICK_PATH => 'ImageMagick',
    GHOSTSCRIPT_PATH => 'Ghostscript'
];

$foundTool = false;
foreach ($tools as $path => $name) {
    if (is_executable($path)) {
        echo "   ✓ Found: {$name} ({$path})\n";
        $foundTool = true;
    }
}

// Check for common command names
$commands = ['convert', 'gs', 'pdftoppm'];
foreach ($commands as $cmd) {
    $output = [];
    $return = 0;
    exec("which {$cmd} 2>&1", $output, $return);
    if ($return === 0 && !empty($output)) {
        echo "   ✓ Found: {$cmd} ({$output[0]})\n";
        $foundTool = true;
    }
}

// Check PHP extensions
if (extension_loaded('imagick')) {
    echo "   ✓ PHP Imagick extension loaded\n";
    $foundTool = true;
}

if (!$foundTool) {
    echo "   ⚠ No PDF processing tools found. Install ImageMagick, Ghostscript, or PHP Imagick extension.\n";
    echo "      Cover image generation may not work.\n";
}

// Summary
echo "\n5. Setup Summary\n";
echo "   - Database: " . DB_PATH . "\n";
echo "   - Newsletters: " . NEWSLETTERS_DIR . "\n";
echo "   - Images: " . IMAGES_DIR . "\n";
echo "   - Admin username: " . ADMIN_USERNAME . "\n";
echo "   - Default password: changeme123\n";
echo "\n⚠ IMPORTANT: Change the default password using change_password.php\n";
echo "\nNext steps:\n";
echo "1. Change admin password: php change_password.php (or via web browser)\n";
echo "2. Migrate existing newsletters: php migrate_existing.php\n";
echo "3. Access admin panel: http://yoursite.com/admin/\n";
echo "\nSetup complete!\n";
?>

