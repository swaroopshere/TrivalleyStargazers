<?php
/**
 * TVS Admin - Meetings Editor
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Meetings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $meetingType = $_POST['meeting_type'] ?? '';

    if (in_array($meetingType, ['public', 'board'])) {
        $meetingDate = sanitizeDate($_POST['meeting_date'] ?? '');
        $meetingTime = sanitizeTime($_POST['meeting_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $locationAddress = trim($_POST['location_address'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $meetingFormat = $_POST['meeting_format'] ?? 'hybrid';

        // Presentation fields (for public meetings only)
        $presentationTopic = trim($_POST['presentation_topic'] ?? '');
        $presenterName = trim($_POST['presenter_name'] ?? '');
        $presenterTitle = trim($_POST['presenter_title'] ?? '');
        $presentationAbstract = trim($_POST['presentation_abstract'] ?? '');
        $presenterBio = trim($_POST['presenter_bio'] ?? '');

        if (!$meetingDate) {
            redirect('meetings.php', 'Invalid date provided.', 'error');
        }

        // Check if meeting exists for this type
        $existing = dbQueryOne(
            "SELECT id FROM meetings WHERE meeting_type = ? AND is_active = 1",
            [$meetingType]
        );

        if ($meetingType === 'public') {
            // Public meeting includes presentation data
            if ($existing) {
                dbExecute(
                    "UPDATE meetings SET meeting_date = ?, meeting_time = ?, location = ?,
                     location_address = ?, description = ?, meeting_format = ?,
                     presentation_topic = ?, presenter_name = ?, presenter_title = ?,
                     presentation_abstract = ?, presenter_bio = ?,
                     updated_by = ?, updated_at = NOW()
                     WHERE id = ?",
                    [$meetingDate, $meetingTime, $location, $locationAddress, $description,
                     $meetingFormat, $presentationTopic, $presenterName, $presenterTitle,
                     $presentationAbstract, $presenterBio, auth()->getUserId(), $existing['id']]
                );
                logAudit(auth()->getUserId(), 'update_meeting', 'meetings', $existing['id']);
            } else {
                $id = dbInsert(
                    "INSERT INTO meetings (meeting_type, meeting_date, meeting_time, location,
                     location_address, description, meeting_format, presentation_topic,
                     presenter_name, presenter_title, presentation_abstract, presenter_bio, updated_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$meetingType, $meetingDate, $meetingTime, $location, $locationAddress,
                     $description, $meetingFormat, $presentationTopic, $presenterName,
                     $presenterTitle, $presentationAbstract, $presenterBio, auth()->getUserId()]
                );
                logAudit(auth()->getUserId(), 'create_meeting', 'meetings', $id);
            }
        } else {
            // Board meeting (no presentation data)
            if ($existing) {
                dbExecute(
                    "UPDATE meetings SET meeting_date = ?, meeting_time = ?, location = ?,
                     location_address = ?, description = ?, meeting_format = ?,
                     updated_by = ?, updated_at = NOW()
                     WHERE id = ?",
                    [$meetingDate, $meetingTime, $location, $locationAddress, $description,
                     $meetingFormat, auth()->getUserId(), $existing['id']]
                );
                logAudit(auth()->getUserId(), 'update_meeting', 'meetings', $existing['id']);
            } else {
                $id = dbInsert(
                    "INSERT INTO meetings (meeting_type, meeting_date, meeting_time, location,
                     location_address, description, meeting_format, updated_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$meetingType, $meetingDate, $meetingTime, $location, $locationAddress,
                     $description, $meetingFormat, auth()->getUserId()]
                );
                logAudit(auth()->getUserId(), 'create_meeting', 'meetings', $id);
            }
        }

        redirect('meetings.php', ucfirst($meetingType) . ' meeting updated successfully.');
    }
}

// Get current meetings
$publicMeeting = getCurrentPublicMeeting() ?: [
    'meeting_date' => date('Y-m-d'),
    'meeting_time' => '19:30',
    'location' => DEFAULT_MEETING_LOCATION,
    'location_address' => DEFAULT_MEETING_ADDRESS,
    'description' => '',
    'meeting_format' => 'hybrid',
    'presentation_topic' => '',
    'presenter_name' => '',
    'presenter_title' => '',
    'presentation_abstract' => '',
    'presenter_bio' => ''
];

$boardMeeting = getCurrentBoardMeeting() ?: [
    'meeting_date' => date('Y-m-d'),
    'meeting_time' => '19:30',
    'location' => 'Video Conference',
    'location_address' => '',
    'description' => 'Board meetings are usually held using video conferencing. Members are always welcome at board meetings.',
    'meeting_format' => 'zoom'
];

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Meeting Settings</h2>
    <p>Update the date, time, location, and presentation details for meetings</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Public Meeting -->
    <div class="card">
        <h3>Public Meeting</h3>
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="meeting_type" value="public">

            <h4 style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Meeting Details</h4>

            <div class="form-row">
                <div class="form-group">
                    <label for="public_date">Date</label>
                    <input type="date" id="public_date" name="meeting_date"
                           value="<?= e($publicMeeting['meeting_date']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="public_time">Time</label>
                    <input type="time" id="public_time" name="meeting_time"
                           value="<?= e($publicMeeting['meeting_time']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="public_location">Location</label>
                <input type="text" id="public_location" name="location"
                       value="<?= e($publicMeeting['location']) ?>" required>
            </div>

            <div class="form-group">
                <label for="public_address">Address</label>
                <input type="text" id="public_address" name="location_address"
                       value="<?= e($publicMeeting['location_address'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="public_format">Meeting Format</label>
                <select id="public_format" name="meeting_format">
                    <option value="in-person" <?= ($publicMeeting['meeting_format'] ?? '') === 'in-person' ? 'selected' : '' ?>>In-person only</option>
                    <option value="zoom" <?= ($publicMeeting['meeting_format'] ?? '') === 'zoom' ? 'selected' : '' ?>>Zoom only</option>
                    <option value="hybrid" <?= ($publicMeeting['meeting_format'] ?? 'hybrid') === 'hybrid' ? 'selected' : '' ?>>Hybrid (In-person and Zoom)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="public_description">Additional Notes</label>
                <textarea id="public_description" name="description" rows="2"><?= e($publicMeeting['description'] ?? '') ?></textarea>
            </div>

            <h4 style="border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-top: 20px;">Presentation Details</h4>

            <div class="form-group">
                <label for="presentation_topic">Presentation Topic</label>
                <input type="text" id="presentation_topic" name="presentation_topic"
                       value="<?= e($publicMeeting['presentation_topic'] ?? '') ?>"
                       placeholder="e.g., Exploring the Moons of Jupiter">
            </div>

            <div class="form-group">
                <label for="presenter_name">Presenter Name</label>
                <input type="text" id="presenter_name" name="presenter_name"
                       value="<?= e($publicMeeting['presenter_name'] ?? '') ?>"
                       placeholder="e.g., Dr. Jane Smith">
            </div>

            <div class="form-group">
                <label for="presenter_title">Presenter Title/Affiliation</label>
                <input type="text" id="presenter_title" name="presenter_title"
                       value="<?= e($publicMeeting['presenter_title'] ?? '') ?>"
                       placeholder="e.g., Professor of Astronomy, UC Berkeley">
            </div>

            <div class="form-group">
                <label for="presentation_abstract">Abstract</label>
                <textarea id="presentation_abstract" name="presentation_abstract" rows="4"
                          placeholder="Brief description of the presentation topic..."><?= e($publicMeeting['presentation_abstract'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="presenter_bio">Presenter Bio</label>
                <textarea id="presenter_bio" name="presenter_bio" rows="3"
                          placeholder="Brief biography of the presenter..."><?= e($publicMeeting['presenter_bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">Save Public Meeting</button>
        </form>
    </div>

    <!-- Board Meeting -->
    <div class="card">
        <h3>Board Meeting</h3>
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="meeting_type" value="board">

            <div class="form-row">
                <div class="form-group">
                    <label for="board_date">Date</label>
                    <input type="date" id="board_date" name="meeting_date"
                           value="<?= e($boardMeeting['meeting_date']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="board_time">Time</label>
                    <input type="time" id="board_time" name="meeting_time"
                           value="<?= e($boardMeeting['meeting_time']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="board_location">Location</label>
                <input type="text" id="board_location" name="location"
                       value="<?= e($boardMeeting['location']) ?>" required>
            </div>

            <div class="form-group">
                <label for="board_address">Address (if applicable)</label>
                <input type="text" id="board_address" name="location_address"
                       value="<?= e($boardMeeting['location_address'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="board_format">Meeting Format</label>
                <select id="board_format" name="meeting_format">
                    <option value="in-person" <?= ($boardMeeting['meeting_format'] ?? '') === 'in-person' ? 'selected' : '' ?>>In-person only</option>
                    <option value="zoom" <?= ($boardMeeting['meeting_format'] ?? 'zoom') === 'zoom' ? 'selected' : '' ?>>Zoom only</option>
                    <option value="hybrid" <?= ($boardMeeting['meeting_format'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid (In-person and Zoom)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="board_description">Description</label>
                <textarea id="board_description" name="description" rows="3"><?= e($boardMeeting['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn">Save Board Meeting</button>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Meeting History</h3>
    <?php
    $pastMeetings = dbQuery(
        "SELECT * FROM meetings ORDER BY meeting_date DESC LIMIT 20"
    );
    ?>
    <?php if ($pastMeetings): ?>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Presentation</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pastMeetings as $meeting): ?>
                    <tr>
                        <td><?= e(ucfirst($meeting['meeting_type'])) ?></td>
                        <td><?= formatDate($meeting['meeting_date']) ?></td>
                        <td><?= formatTime($meeting['meeting_time']) ?></td>
                        <td><?= e($meeting['location']) ?></td>
                        <td><?= $meeting['meeting_type'] === 'public' && !empty($meeting['presentation_topic']) ? e(truncate($meeting['presentation_topic'], 40)) : '-' ?></td>
                        <td><?= $meeting['is_active'] ? '<span style="color: green;">Active</span>' : '<span style="color: gray;">Past</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No meeting history available.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
