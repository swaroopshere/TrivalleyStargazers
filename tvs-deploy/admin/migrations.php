<?php
/**
 * TVS Admin - Database Migrations
 *
 * View and run database migrations.
 * Admin only.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/migrations.php';

// Require admin access
requireAdmin();

$pageTitle = 'Database Migrations';
$messages = [];
$messageType = 'info';

// Handle migration run
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $action = $_POST['action'] ?? '';

    if ($action === 'run_all') {
        $result = migrations()->runAll();
        $messages = $result['messages'];
        $messageType = $result['success'] ? 'success' : 'error';

        if ($result['success'] && $result['run'] > 0) {
            logAudit(auth()->getUserId(), 'run_migrations', 'migrations', $result['run']);
        }
    } elseif ($action === 'run_single') {
        $filename = $_POST['migration'] ?? '';
        $pending = migrations()->getPendingMigrations();

        $found = null;
        foreach ($pending as $migration) {
            if ($migration['filename'] === $filename) {
                $found = $migration;
                break;
            }
        }

        if ($found) {
            $result = migrations()->runMigration($found);
            $messages = $result['messages'];
            $messageType = $result['success'] ? 'success' : 'error';

            if ($result['success']) {
                logAudit(auth()->getUserId(), 'run_migration', 'migrations', 0, '', $filename);
            }
        } else {
            $messages[] = "Migration not found or already executed.";
            $messageType = 'error';
        }
    }
}

// Get current status
$status = migrations()->getStatus();
$pending = migrations()->getPendingMigrations();

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Database Migrations</h2>
    <p>Manage database schema changes across environments</p>
</div>

<?php if (!empty($messages)): ?>
<div class="alert alert-<?= $messageType ?>">
    <?php foreach ($messages as $msg): ?>
        <div><?= e($msg) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Run Migrations -->
<?php if (!empty($pending)): ?>
<div class="card">
    <h3>Pending Migrations</h3>
    <p style="margin-bottom: 15px; color: #666;">
        <?= count($pending) ?> migration(s) pending. These will update your database schema.
    </p>

    <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="run_all">
        <button type="submit" class="btn" onclick="return confirm('Run all pending migrations? This will modify the database.')">
            Run All Pending Migrations
        </button>
    </form>
</div>
<?php else: ?>
<div class="card">
    <h3>Migrations Up To Date</h3>
    <p style="color: #28a745;">All migrations have been executed. Your database schema is current.</p>
</div>
<?php endif; ?>

<!-- Migration Status -->
<div class="card" style="margin-top: 20px;">
    <h3>Migration History</h3>

    <?php if (empty($status)): ?>
        <p>No migrations found in <code>data/migrations/</code></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Migration</th>
                    <th>Status</th>
                    <th>Executed</th>
                    <th>Batch</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($status as $migration): ?>
                <tr>
                    <td><code><?= e($migration['version']) ?></code></td>
                    <td><?= e($migration['name']) ?></td>
                    <td>
                        <?php if ($migration['executed']): ?>
                            <span style="color: #28a745;">✓ Executed</span>
                        <?php else: ?>
                            <span style="color: #dc3545;">○ Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $migration['executed_at'] ? e(date('M j, Y g:i A', strtotime($migration['executed_at']))) : '—' ?>
                    </td>
                    <td><?= $migration['batch'] ?? '—' ?></td>
                    <td>
                        <?php if (!$migration['executed']): ?>
                        <form method="POST" action="" style="display: inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="run_single">
                            <input type="hidden" name="migration" value="<?= e($migration['filename']) ?>">
                            <button type="submit" class="btn btn-small"
                                    onclick="return confirm('Run this migration?')">Run</button>
                        </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- How to Create Migrations -->
<div class="card" style="margin-top: 20px;">
    <h3>Creating New Migrations</h3>
    <p>To create a new migration:</p>
    <ol style="margin: 15px 0; padding-left: 20px; line-height: 1.8;">
        <li>Create a file in <code>data/migrations/</code> with format: <code>XXX_description.php</code></li>
        <li>Example: <code>003_add_user_preferences.php</code></li>
        <li>The file must return an object with an <code>up()</code> method</li>
    </ol>

    <details style="margin-top: 15px;">
        <summary style="cursor: pointer; font-weight: 600;">View Migration Template</summary>
        <pre style="background: #f5f5f5; padding: 15px; margin-top: 10px; overflow-x: auto; font-size: 13px;">&lt;?php
/**
 * Migration: 003_add_user_preferences
 * Description: Add user preferences table
 */

return new class {
    public function up(PDO $pdo): array {
        $messages = [];

        // Check if table exists
        $exists = $pdo->query("SHOW TABLES LIKE 'user_preferences'")->rowCount() > 0;

        if ($exists) {
            $messages[] = "Table 'user_preferences' already exists - skipping";
        } else {
            $pdo->exec("
                CREATE TABLE user_preferences (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    preference_key VARCHAR(100) NOT NULL,
                    preference_value TEXT,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    UNIQUE KEY unique_user_pref (user_id, preference_key)
                )
            ");
            $messages[] = "Created table 'user_preferences'";
        }

        return $messages;
    }
};</pre>
    </details>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
