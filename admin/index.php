<?php
/**
 * Newsletter Admin Dashboard
 */
require_once __DIR__ . '/auth.php';
requireLogin();
checkSessionTimeout();

require_once __DIR__ . '/config.php';

$pdo = getDB();

// Get recent newsletters
$stmt = $pdo->query("SELECT * FROM newsletters ORDER BY year DESC, month DESC LIMIT 10");
$recentNewsletters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Newsletter Admin Dashboard</title>
    <link href="../tvs.css" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <h1>Newsletter Admin Dashboard</h1>
            <div>
                <a href="upload.php" class="btn">Upload New Newsletter</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
        
        <h2>Recent Newsletters</h2>
        <?php if (empty($recentNewsletters)): ?>
            <p>No newsletters uploaded yet. <a href="upload.php">Upload your first newsletter</a>.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Month</th>
                        <th>Filename</th>
                        <th>Uploaded</th>
                        <th>Uploaded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentNewsletters as $newsletter): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($newsletter['year']); ?></td>
                            <td><?php echo date('F', mktime(0, 0, 0, $newsletter['month'], 1)); ?></td>
                            <td><?php echo htmlspecialchars($newsletter['filename']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($newsletter['uploaded_at'])); ?></td>
                            <td><?php echo htmlspecialchars($newsletter['uploaded_by'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <h2>Actions</h2>
        <ul>
            <li><a href="upload.php">Upload New Newsletter</a></li>
            <li><a href="../newsletter.shtml" target="_blank">View Newsletter Page</a></li>
            <li><a href="../newsletterlinks.shtml" target="_blank">View Newsletter Links Page</a></li>
        </ul>
    </div>
</body>
</html>

