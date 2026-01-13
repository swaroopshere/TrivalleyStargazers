<?php
/**
 * Change Admin Password
 * Run this script once to set your admin password
 */
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword)) {
        $error = 'Password cannot be empty';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        // Update config.php
        $configFile = __DIR__ . '/config.php';
        $configContent = file_get_contents($configFile);
        
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $newHashEscaped = addslashes($newHash);
        
        // Replace the password hash line
        $configContent = preg_replace(
            "/define\('ADMIN_PASSWORD_HASH',\s*'[^']+'\);/",
            "define('ADMIN_PASSWORD_HASH', '{$newHashEscaped}');",
            $configContent
        );
        
        if (file_put_contents($configFile, $configContent)) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Failed to update config file. Please check file permissions.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Change Admin Password</title>
    <link href="../tvs.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
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
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
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
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Admin Password</h1>
        
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <p><a href="login.php">Go to Login</a></p>
        <?php else: ?>
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                <button type="submit">Change Password</button>
            </form>
            
            <div class="info">
                <strong>Note:</strong> After changing the password, delete this file (change_password.php) for security.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

