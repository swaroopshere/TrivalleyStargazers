<?php
/**
 * Newsletter Upload Interface
 */
require_once __DIR__ . '/auth.php';
requireLogin();
checkSessionTimeout();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/pdf_processor.php';

$pdo = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = intval($_POST['year'] ?? 0);
    $month = intval($_POST['month'] ?? 0);
    
    if ($year < 2000 || $year > 2100) {
        $error = 'Invalid year';
    } elseif ($month < 1 || $month > 12) {
        $error = 'Invalid month';
    } elseif (!isset($_FILES['newsletter_file']) || $_FILES['newsletter_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload error. Please select a PDF file.';
    } else {
        $file = $_FILES['newsletter_file'];
        
        // Validate PDF
        if ($file['type'] !== 'application/pdf' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'pdf') {
            $error = 'File must be a PDF';
        } else {
            // Generate filename
            $shortYear = substr($year, -2);
            $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
            $filename = "tvsnews{$monthPadded}{$shortYear}.pdf";
            
            // Create year directory if it doesn't exist
            $yearDir = NEWSLETTERS_DIR . "/{$year}";
            if (!is_dir($yearDir)) {
                mkdir($yearDir, 0755, true);
            }
            
            $filepath = $yearDir . '/' . $filename;
            
            // Check if newsletter already exists
            $stmt = $pdo->prepare("SELECT id FROM newsletters WHERE year = ? AND month = ?");
            $stmt->execute([$year, $month]);
            if ($stmt->fetch()) {
                $error = "Newsletter for {$year}-{$month} already exists. Please delete it first or choose a different date.";
            } else {
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Extract first page as image
                    $coverGenerated = generateNewsletterCover($filepath);
                    
                    if ($coverGenerated) {
                        // Save to database
                        $stmt = $pdo->prepare("INSERT INTO newsletters (year, month, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $year,
                            $month,
                            $filename,
                            $filepath,
                            $_SESSION['admin_username'] ?? 'admin'
                        ]);
                        
                        // Regenerate newsletter pages
                        regenerateNewsletterPages($pdo);
                        
                        $message = "Newsletter uploaded successfully! Cover image generated.";
                    } else {
                        // File uploaded but cover generation failed
                        $message = "Newsletter uploaded, but cover image generation failed. You may need to manually create newscover.jpg";
                        // Still save to database
                        $stmt = $pdo->prepare("INSERT INTO newsletters (year, month, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $year,
                            $month,
                            $filename,
                            $filepath,
                            $_SESSION['admin_username'] ?? 'admin'
                        ]);
                        regenerateNewsletterPages($pdo);
                    }
                } else {
                    $error = 'Failed to save uploaded file';
                }
            }
        }
    }
}

// Get current year/month as defaults
$currentYear = date('Y');
$currentMonth = date('n');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Newsletter</title>
    <link href="../tvs.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #95a5a6;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Newsletter</h1>
        
        <a href="index.php" class="btn-secondary" style="text-decoration: none; padding: 10px 20px; display: inline-block; margin-bottom: 20px;">← Back to Dashboard</a>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="year">Year:</label>
                    <select id="year" name="year" required>
                        <?php
                        $startYear = 2000;
                        $endYear = date('Y') + 1;
                        for ($y = $endYear; $y >= $startYear; $y--) {
                            $selected = ($y == $currentYear) ? 'selected' : '';
                            echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="month">Month:</label>
                    <select id="month" name="month" required>
                        <?php
                        $months = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        foreach ($months as $num => $name) {
                            $selected = ($num == $currentMonth) ? 'selected' : '';
                            echo "<option value=\"{$num}\" {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="newsletter_file">PDF File:</label>
                <input type="file" id="newsletter_file" name="newsletter_file" accept=".pdf,application/pdf" required>
                <small style="color: #666;">Select the newsletter PDF file</small>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="submit">Upload Newsletter</button>
                <a href="index.php" class="btn-secondary" style="text-decoration: none; padding: 12px 24px; display: inline-block;">Cancel</a>
            </div>
        </form>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-radius: 4px;">
            <h3>Instructions:</h3>
            <ol>
                <li>Select the year and month for this newsletter</li>
                <li>Choose the PDF file to upload</li>
                <li>Click "Upload Newsletter"</li>
                <li>The system will automatically:
                    <ul>
                        <li>Save the PDF to the correct folder</li>
                        <li>Extract the first page as newscover.jpg</li>
                        <li>Update newsletter.shtml with the latest newsletter link</li>
                        <li>Update newsletterlinks.shtml with the new entry</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
</body>
</html>

