<?php
/**
 * TVS Admin Header Template
 */

if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config.php';
    require_once dirname(__DIR__) . '/db.php';
    require_once dirname(__DIR__) . '/auth.php';
    require_once dirname(__DIR__) . '/functions.php';
}

requireAuth();

$user = auth()->getUser();
$currentPage = getCurrentPage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> - TVS Admin</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .admin-header {
            background: #003354;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            font-size: 20px;
            font-weight: 600;
        }

        .admin-header h1 a {
            color: white;
            text-decoration: none;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user span {
            font-size: 14px;
        }

        .admin-user a {
            color: #7eb8e2;
            text-decoration: none;
            font-size: 14px;
        }

        .admin-user a:hover {
            color: white;
        }

        .admin-nav {
            background: #004d7a;
            padding: 0 20px;
        }

        .admin-nav ul {
            list-style: none;
            display: flex;
            gap: 5px;
        }

        .admin-nav li a {
            display: block;
            padding: 12px 18px;
            color: #ccc;
            text-decoration: none;
            font-size: 14px;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .admin-nav li a:hover,
        .admin-nav li a.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-bottom-color: #7eb8e2;
        }

        .admin-content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }

        .card h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group input[type="time"],
        .form-group input[type="number"],
        .form-group input[type="url"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #003354;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #003354;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #004d7a;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            font-weight: 600;
            color: #333;
            background: #f9f9f9;
        }

        table tbody tr:hover {
            background: #f5f5f5;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #003354;
        }

        .stat-card .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.3s;
            border-radius: 26px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #003354;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1><a href="index.php">TVS Admin</a></h1>
        <div class="admin-user">
            <span>Welcome, <?= e($user['username']) ?></span>
            <a href="../index.php" target="_blank">View Site</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <nav class="admin-nav">
        <ul>
            <li><a href="index.php" <?= $currentPage === 'index' ? 'class="active"' : '' ?>>Dashboard</a></li>
            <li><a href="meetings.php" <?= $currentPage === 'meetings' ? 'class="active"' : '' ?>>Meetings</a></li>
            <li><a href="presentation.php" <?= $currentPage === 'presentation' ? 'class="active"' : '' ?>>Presentation</a></li>
            <li><a href="events.php" <?= $currentPage === 'events' ? 'class="active"' : '' ?>>Events</a></li>
            <li><a href="newsletter.php" <?= $currentPage === 'newsletter' ? 'class="active"' : '' ?>>Newsletters</a></li>
            <li><a href="contacts.php" <?= $currentPage === 'contacts' ? 'class="active"' : '' ?>>Contacts</a></li>
            <?php if (auth()->isAdmin()): ?>
            <li><a href="users.php" <?= $currentPage === 'users' ? 'class="active"' : '' ?>>Users</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <main class="admin-content">
        <?php showFlashMessage(); ?>
