<?php
/**
 * TVS Admin - Calendar Sync
 *
 * Manual trigger to sync events from groups.io
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Calendar Sync';

$syncResult = null;
$error = null;

// Handle sync request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    try {
        // Call the sync API
        $apiUrl = SITE_URL . '/api/calendar-sync.php';

        // For local testing, use relative path
        include_once ROOT_PATH . '/api/calendar-sync.php';

        $result = syncGroupsIoCalendar();

        if ($result['success']) {
            $syncResult = $result;
            logAudit(auth()->getUserId(), 'sync_calendar', 'calendar_cache', 0);
        } else {
            $error = $result['error'];
        }
    } catch (Exception $e) {
        $error = 'Sync failed: ' . $e->getMessage();
    }
}

// Get current calendar cache stats
$cacheStats = dbQueryOne(
    "SELECT COUNT(*) as total,
            MIN(event_date) as earliest,
            MAX(event_date) as latest,
            MAX(fetched_at) as last_sync
     FROM calendar_cache"
);

$upcomingEvents = getUpcomingEvents(10);

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Calendar Sync</h2>
    <p>Sync star party events from groups.io calendar</p>
</div>

<?php if ($syncResult): ?>
    <div class="alert alert-success">
        Calendar synced successfully! <?= $syncResult['count'] ?? 0 ?> events updated.
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Sync Control -->
    <div class="card">
        <h3>Sync from Groups.io</h3>
        <p style="margin-bottom: 20px;">
            Click the button below to fetch the latest star party events from the
            <a href="https://groups.io/g/trivalleystargazers/calendar" target="_blank">groups.io calendar</a>.
        </p>

        <form method="POST" action="">
            <?= csrfField() ?>
            <button type="submit" class="btn">Sync Calendar Now</button>
        </form>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <h4>Cache Statistics</h4>
            <table style="margin-top: 10px;">
                <tr>
                    <td><strong>Total Events:</strong></td>
                    <td><?= $cacheStats['total'] ?? 0 ?></td>
                </tr>
                <tr>
                    <td><strong>Date Range:</strong></td>
                    <td>
                        <?php if ($cacheStats['earliest']): ?>
                            <?= formatDate($cacheStats['earliest']) ?> - <?= formatDate($cacheStats['latest']) ?>
                        <?php else: ?>
                            No events
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Last Sync:</strong></td>
                    <td>
                        <?= $cacheStats['last_sync'] ? formatDate($cacheStats['last_sync'], 'M j, Y g:i A') : 'Never' ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="card">
        <h3>Upcoming Events (from cache)</h3>
        <?php if ($upcomingEvents): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Event</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <tr>
                            <td><?= formatDate($event['event_date'], 'M j') ?></td>
                            <td><?= e(truncate($event['title'], 40)) ?></td>
                            <td><?= e(truncate($event['location'] ?? '-', 30)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #666;">No upcoming events in cache. Try syncing from groups.io.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Automatic Sync</h3>
    <p>
        For automatic daily syncing, add this cron job to your server:
    </p>
    <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
0 6 * * * php <?= e(ROOT_PATH) ?>/api/calendar-sync.php</pre>
    <p style="margin-top: 10px; color: #666;">
        This will sync the calendar every day at 6:00 AM.
    </p>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
