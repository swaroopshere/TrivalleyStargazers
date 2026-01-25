<?php
/**
 * TVS Admin Dashboard
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Dashboard';

// Get stats
$newsletterCount = dbQueryOne("SELECT COUNT(*) as count FROM newsletters")['count'] ?? 0;
$eventCount = dbQueryOne("SELECT COUNT(*) as count FROM events WHERE is_visible = 1")['count'] ?? 0;
$userCount = dbQueryOne("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count'] ?? 0;
$upcomingCalendarCount = dbQueryOne("SELECT COUNT(*) as count FROM calendar_cache WHERE event_date >= CURDATE()")['count'] ?? 0;

// Get current info
$publicMeeting = getCurrentPublicMeeting();
$boardMeeting = getCurrentBoardMeeting();
$currentPresentation = getCurrentPresentation();
$currentNewsletter = getCurrentNewsletter();

// Get recent audit log
$recentActivity = dbQuery(
    "SELECT a.*, u.username FROM audit_log a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.created_at DESC LIMIT 10"
);

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Welcome to the Tri-Valley Stargazers admin panel</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $newsletterCount ?></div>
        <div class="stat-label">Total Newsletters</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $eventCount ?></div>
        <div class="stat-label">Active Events</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $upcomingCalendarCount ?></div>
        <div class="stat-label">Upcoming Calendar Events</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $userCount ?></div>
        <div class="stat-label">Active Users</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card">
        <h3>Current Public Meeting</h3>
        <?php if ($publicMeeting): ?>
            <p><strong>Date:</strong> <?= formatDate($publicMeeting['meeting_date'], 'l, F j, Y') ?></p>
            <p><strong>Time:</strong> <?= formatTime($publicMeeting['meeting_time']) ?></p>
            <p><strong>Location:</strong> <?= e($publicMeeting['location']) ?></p>
            <p><strong>Format:</strong> <?= getMeetingFormatLabel($publicMeeting['meeting_format']) ?></p>
            <br>
            <a href="meetings.php" class="btn btn-small">Edit Meeting</a>
        <?php else: ?>
            <p>No meeting scheduled</p>
            <a href="meetings.php" class="btn btn-small">Add Meeting</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Current Presentation</h3>
        <?php if ($currentPresentation): ?>
            <p><strong>Month:</strong> <?= getMonthName($currentPresentation['month']) ?> <?= $currentPresentation['year'] ?></p>
            <p><strong>Topic:</strong> <?= e(truncate($currentPresentation['topic'], 80)) ?></p>
            <p><strong>Presenter:</strong> <?= e($currentPresentation['presenter_name']) ?></p>
            <br>
            <a href="presentation.php" class="btn btn-small">Edit Presentation</a>
        <?php else: ?>
            <p>No presentation for this month</p>
            <a href="presentation.php" class="btn btn-small">Add Presentation</a>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
    <div class="card">
        <h3>Current Newsletter</h3>
        <?php if ($currentNewsletter): ?>
            <p><strong>Issue:</strong> <?= getMonthName($currentNewsletter['month']) ?> <?= $currentNewsletter['year'] ?></p>
            <p><strong>File:</strong> <?= e($currentNewsletter['filename']) ?></p>
            <br>
            <a href="newsletter.php" class="btn btn-small">Manage Newsletters</a>
        <?php else: ?>
            <p>No current newsletter set</p>
            <a href="newsletter.php" class="btn btn-small">Add Newsletter</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Board Meeting</h3>
        <?php if ($boardMeeting): ?>
            <p><strong>Date:</strong> <?= formatDate($boardMeeting['meeting_date'], 'l, F j, Y') ?></p>
            <p><strong>Time:</strong> <?= formatTime($boardMeeting['meeting_time']) ?></p>
            <p><strong>Format:</strong> <?= getMeetingFormatLabel($boardMeeting['meeting_format']) ?></p>
            <br>
            <a href="meetings.php" class="btn btn-small">Edit Meeting</a>
        <?php else: ?>
            <p>No board meeting scheduled</p>
            <a href="meetings.php" class="btn btn-small">Add Meeting</a>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Recent Activity</h3>
    <?php if ($recentActivity): ?>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentActivity as $log): ?>
                    <tr>
                        <td><?= e($log['username'] ?? 'System') ?></td>
                        <td><?= e($log['action']) ?></td>
                        <td><?= e($log['table_name'] ?: '-') ?></td>
                        <td><?= formatDate($log['created_at'], 'M j, Y g:i A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No recent activity</p>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Quick Actions</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="meetings.php" class="btn">Update Meetings</a>
        <a href="presentation.php" class="btn">Update Presentation</a>
        <a href="events.php" class="btn">Manage Events</a>
        <a href="newsletter.php" class="btn">Upload Newsletter</a>
        <a href="sync-calendar.php" class="btn btn-secondary">Sync Calendar</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
