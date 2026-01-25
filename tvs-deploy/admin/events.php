<?php
/**
 * TVS Admin - Events Editor
 *
 * Manage H2O Open House, Tesla Vineyard dates, and announcements
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Events';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_event':
            $eventType = $_POST['event_type'] ?? '';
            $eventDate = sanitizeDate($_POST['event_date'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isVisible = isset($_POST['is_visible']) ? 1 : 0;

            if (!in_array($eventType, ['h2o', 'tesla', 'announcement', 'bbq', 'potluck'])) {
                redirect('events.php', 'Invalid event type.', 'error');
            }

            $id = dbInsert(
                "INSERT INTO events (event_type, event_date, title, description, is_visible, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$eventType, $eventDate, $title, $description, $isVisible, auth()->getUserId()]
            );
            logAudit(auth()->getUserId(), 'create_event', 'events', $id);
            redirect('events.php', 'Event added successfully.');
            break;

        case 'update_event':
            $eventId = (int)($_POST['event_id'] ?? 0);
            $eventDate = sanitizeDate($_POST['event_date'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isVisible = isset($_POST['is_visible']) ? 1 : 0;

            dbExecute(
                "UPDATE events SET event_date = ?, title = ?, description = ?, is_visible = ?,
                 updated_by = ?, updated_at = NOW() WHERE id = ?",
                [$eventDate, $title, $description, $isVisible, auth()->getUserId(), $eventId]
            );
            logAudit(auth()->getUserId(), 'update_event', 'events', $eventId);
            redirect('events.php', 'Event updated successfully.');
            break;

        case 'delete_event':
            $eventId = (int)($_POST['event_id'] ?? 0);
            dbExecute("DELETE FROM events WHERE id = ?", [$eventId]);
            logAudit(auth()->getUserId(), 'delete_event', 'events', $eventId);
            redirect('events.php', 'Event deleted successfully.');
            break;

        case 'toggle_visibility':
            $eventId = (int)($_POST['event_id'] ?? 0);
            dbExecute("UPDATE events SET is_visible = NOT is_visible WHERE id = ?", [$eventId]);
            logAudit(auth()->getUserId(), 'toggle_event_visibility', 'events', $eventId);
            redirect('events.php', 'Event visibility toggled.');
            break;
    }
}

// Get events by type
$h2oEvents = dbQuery("SELECT * FROM events WHERE event_type = 'h2o' ORDER BY event_date DESC");
$teslaEvents = dbQuery("SELECT * FROM events WHERE event_type = 'tesla' ORDER BY event_date DESC");
$announcements = dbQuery("SELECT * FROM events WHERE event_type = 'announcement' ORDER BY updated_at DESC");
$specialEvents = dbQuery("SELECT * FROM events WHERE event_type IN ('bbq', 'potluck') ORDER BY event_date DESC");

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Events Management</h2>
    <p>Manage H2O Open House, Tesla Vineyard star parties, and announcements</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- H2O Open House -->
    <div class="card">
        <h3>H2O Open House Dates</h3>

        <!-- Add new H2O date -->
        <form method="POST" action="" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_event">
            <input type="hidden" name="event_type" value="h2o">
            <input type="hidden" name="title" value="H2O Open House">

            <div class="form-row">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Add New Date</label>
                    <input type="date" name="event_date" required>
                </div>
                <div class="form-group" style="margin-bottom: 0; align-self: end;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_visible" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size: 12px;">Show</span>
                </div>
                <div class="form-group" style="margin-bottom: 0; align-self: end;">
                    <button type="submit" class="btn btn-small">Add</button>
                </div>
            </div>
        </form>

        <!-- Existing H2O dates -->
        <?php if ($h2oEvents): ?>
            <?php foreach ($h2oEvents as $event): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
                    <div>
                        <strong><?= formatDate($event['event_date']) ?></strong>
                        <?php if (!$event['is_visible']): ?>
                            <span style="color: #999; font-size: 12px;">(hidden)</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <form method="POST" action="" style="display: inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="toggle_visibility">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" class="btn btn-small btn-secondary">
                                <?= $event['is_visible'] ? 'Hide' : 'Show' ?>
                            </button>
                        </form>
                        <form method="POST" action="" style="display: inline;"
                              onsubmit="return confirm('Delete this date?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete_event">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #666;">No H2O dates scheduled.</p>
        <?php endif; ?>
    </div>

    <!-- Tesla Vineyard -->
    <div class="card">
        <h3>Tesla Vineyard Star Party Dates</h3>

        <!-- Add new Tesla date -->
        <form method="POST" action="" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_event">
            <input type="hidden" name="event_type" value="tesla">
            <input type="hidden" name="title" value="Tesla Vineyard Star Party">

            <div class="form-row">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Add New Date</label>
                    <input type="date" name="event_date" required>
                </div>
                <div class="form-group" style="margin-bottom: 0; align-self: end;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_visible" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size: 12px;">Show</span>
                </div>
                <div class="form-group" style="margin-bottom: 0; align-self: end;">
                    <button type="submit" class="btn btn-small">Add</button>
                </div>
            </div>
        </form>

        <!-- Existing Tesla dates -->
        <?php if ($teslaEvents): ?>
            <?php foreach ($teslaEvents as $event): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee;">
                    <div>
                        <strong><?= formatDate($event['event_date']) ?></strong>
                        <?php if (!$event['is_visible']): ?>
                            <span style="color: #999; font-size: 12px;">(hidden)</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <form method="POST" action="" style="display: inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="toggle_visibility">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" class="btn btn-small btn-secondary">
                                <?= $event['is_visible'] ? 'Hide' : 'Show' ?>
                            </button>
                        </form>
                        <form method="POST" action="" style="display: inline;"
                              onsubmit="return confirm('Delete this date?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete_event">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #666;">No Tesla dates scheduled.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Announcements -->
<div class="card" style="margin-top: 20px;">
    <h3>Announcements</h3>

    <!-- Add new announcement -->
    <form method="POST" action="" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_event">
        <input type="hidden" name="event_type" value="announcement">

        <div class="form-row">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="e.g., Board elections coming up" required>
            </div>
            <div class="form-group" style="flex: 0 0 150px;">
                <label>&nbsp;</label>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_visible" checked>
                    <span class="toggle-slider"></span>
                </label>
                <span style="font-size: 12px;">Show on homepage</span>
            </div>
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea name="description" rows="3" placeholder="Announcement details..."></textarea>
        </div>
        <button type="submit" class="btn">Add Announcement</button>
    </form>

    <!-- Existing announcements -->
    <?php if ($announcements): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $ann): ?>
                    <tr>
                        <td><?= e($ann['title']) ?></td>
                        <td><?= e(truncate($ann['description'] ?? '', 60)) ?></td>
                        <td>
                            <?php if ($ann['is_visible']): ?>
                                <span style="color: green;">Visible</span>
                            <?php else: ?>
                                <span style="color: gray;">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="" style="display: inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_visibility">
                                <input type="hidden" name="event_id" value="<?= $ann['id'] ?>">
                                <button type="submit" class="btn btn-small btn-secondary">
                                    <?= $ann['is_visible'] ? 'Hide' : 'Show' ?>
                                </button>
                            </form>
                            <form method="POST" action="" style="display: inline;"
                                  onsubmit="return confirm('Delete this announcement?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_event">
                                <input type="hidden" name="event_id" value="<?= $ann['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #666;">No announcements.</p>
    <?php endif; ?>
</div>

<!-- Special Events (BBQ, Potluck) -->
<div class="card" style="margin-top: 20px;">
    <h3>Special Events (BBQ, Potluck)</h3>

    <form method="POST" action="" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_event">

        <div class="form-row">
            <div class="form-group">
                <label>Event Type</label>
                <select name="event_type" required>
                    <option value="bbq">Summer BBQ</option>
                    <option value="potluck">Winter Potluck</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="event_date" required>
            </div>
            <div class="form-group" style="flex: 0 0 100px;">
                <label>&nbsp;</label>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_visible" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label>Description (optional)</label>
            <textarea name="description" rows="2"></textarea>
        </div>
        <input type="hidden" name="title" value="">
        <button type="submit" class="btn">Add Special Event</button>
    </form>

    <?php if ($specialEvents): ?>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($specialEvents as $event): ?>
                    <tr>
                        <td><?= $event['event_type'] === 'bbq' ? 'Summer BBQ' : 'Winter Potluck' ?></td>
                        <td><?= formatDate($event['event_date']) ?></td>
                        <td>
                            <?= $event['is_visible'] ? '<span style="color: green;">Visible</span>' : '<span style="color: gray;">Hidden</span>' ?>
                        </td>
                        <td>
                            <form method="POST" action="" style="display: inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_visibility">
                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                <button type="submit" class="btn btn-small btn-secondary">
                                    <?= $event['is_visible'] ? 'Hide' : 'Show' ?>
                                </button>
                            </form>
                            <form method="POST" action="" style="display: inline;"
                                  onsubmit="return confirm('Delete this event?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_event">
                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #666;">No special events scheduled.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
