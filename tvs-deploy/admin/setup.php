<?php
/**
 * TVS Admin Setup Script
 * 
 * Run this once to set up the initial admin user password.
 * Delete this file after setup for security.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Check if already set up
$admin = dbQueryOne("SELECT * FROM users WHERE username = 'admin'");
if ($admin && $admin['password_hash'] !== '$2y$10$placeholder_hash_change_on_first_run') {
    die('Admin user already configured. Please delete this setup file.');
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        dbExecute(
            "UPDATE users SET password_hash = ? WHERE username = 'admin'",
            [$hash]
        );
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TVS Admin Setup</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #003354;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
        }
        input:focus {
            outline: none;
            border-color: #003354;
        }
        .error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #059669;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .btn {
            width: 100%;
            padding: 14px 20px;
            background: #003354;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn:hover {
            background: #004d7a;
        }
        .warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>TVS Admin Setup</h1>
        <p>Set the initial password for the admin user.</p>
        
        <?php if ($success): ?>
            <div class="success">
                ✅ Admin password set successfully!<br><br>
                <strong>IMPORTANT:</strong> Delete this setup file (setup.php) for security.
            </div>
            <a href="login.php" class="btn">Go to Login</a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="warning">
                ⚠️ This setup script should be deleted after use for security.
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Admin Password</label>
                    <input type="password" id="password" name="password" required autofocus
                           minlength="8" placeholder="Minimum 8 characters">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           minlength="8" placeholder="Re-enter password">
                </div>
                
                <button type="submit" class="btn">Set Admin Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
