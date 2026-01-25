<?php
/**
 * TVS Admin - Presentation Editor
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Presentation';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $month = (int)($_POST['month'] ?? 0);
    $year = (int)($_POST['year'] ?? 0);
    $topic = trim($_POST['topic'] ?? '');
    $presenterName = trim($_POST['presenter_name'] ?? '');
    $presenterTitle = trim($_POST['presenter_title'] ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $isHybrid = isset($_POST['is_hybrid']) ? 1 : 0;

    if (!$month || !$year || empty($topic)) {
        redirect('presentation.php', 'Month, year, and topic are required.', 'error');
    }

    // Check if presentation exists for this month/year
    $existing = dbQueryOne(
        "SELECT id FROM presentations WHERE month = ? AND year = ?",
        [$month, $year]
    );

    if ($existing) {
        // Update existing
        dbExecute(
            "UPDATE presentations SET topic = ?, presenter_name = ?, presenter_title = ?,
             abstract = ?, bio = ?, is_hybrid = ?, updated_by = ?, updated_at = NOW()
             WHERE id = ?",
            [$topic, $presenterName, $presenterTitle, $abstract, $bio, $isHybrid,
             auth()->getUserId(), $existing['id']]
        );
        logAudit(auth()->getUserId(), 'update_presentation', 'presentations', $existing['id']);
    } else {
        // Insert new
        $id = dbInsert(
            "INSERT INTO presentations (month, year, topic, presenter_name, presenter_title,
             abstract, bio, is_hybrid, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$month, $year, $topic, $presenterName, $presenterTitle, $abstract, $bio,
             $isHybrid, auth()->getUserId()]
        );
        logAudit(auth()->getUserId(), 'create_presentation', 'presentations', $id);
    }

    redirect('presentation.php?month=' . $month . '&year=' . $year, 'Presentation saved successfully.');
}

// Determine which month/year to show
$selectedMonth = (int)($_GET['month'] ?? date('n'));
$selectedYear = (int)($_GET['year'] ?? date('Y'));

// Get presentation for selected month
$presentation = dbQueryOne(
    "SELECT * FROM presentations WHERE month = ? AND year = ?",
    [$selectedMonth, $selectedYear]
) ?: [
    'topic' => '',
    'presenter_name' => '',
    'presenter_title' => '',
    'abstract' => '',
    'bio' => '',
    'is_hybrid' => 1
];

// Get list of all presentations
$allPresentations = dbQuery(
    "SELECT month, year, topic, presenter_name FROM presentations ORDER BY year DESC, month DESC LIMIT 24"
);

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Monthly Presentation</h2>
    <p>Update the speaker, topic, abstract, and bio for monthly meetings</p>
</div>

<div class="card">
    <h3><?= getMonthName($selectedMonth) ?> <?= $selectedYear ?> Presentation</h3>

    <!-- Month/Year Selector -->
    <form method="GET" action="" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label>Month</label>
            <select name="month" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                        <?= getMonthName($m) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label>Year</label>
            <select name="year" onchange="this.form.submit()">
                <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </form>

    <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="month" value="<?= $selectedMonth ?>">
        <input type="hidden" name="year" value="<?= $selectedYear ?>">

        <div class="form-group">
            <label for="topic">Topic / Title *</label>
            <input type="text" id="topic" name="topic" value="<?= e($presentation['topic']) ?>" required>
            <p class="help-text">The title of the presentation (e.g., "Stars Without Nuclear Fusion")</p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="presenter_name">Presenter Name</label>
                <input type="text" id="presenter_name" name="presenter_name"
                       value="<?= e($presentation['presenter_name']) ?>">
            </div>
            <div class="form-group">
                <label for="presenter_title">Presenter Title / Affiliation</label>
                <input type="text" id="presenter_title" name="presenter_title"
                       value="<?= e($presentation['presenter_title']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="abstract">Abstract</label>
            <textarea id="abstract" name="abstract" rows="6"><?= e($presentation['abstract']) ?></textarea>
            <p class="help-text">A brief description of what the presentation will cover</p>
        </div>

        <div class="form-group">
            <label for="bio">Presenter Bio</label>
            <textarea id="bio" name="bio" rows="4"><?= e($presentation['bio']) ?></textarea>
            <p class="help-text">A short biography of the presenter</p>
        </div>

        <div class="form-group">
            <label class="toggle-switch">
                <input type="checkbox" name="is_hybrid" <?= $presentation['is_hybrid'] ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
            </label>
            <span style="margin-left: 10px;">Hybrid meeting (in-person and Zoom)</span>
        </div>

        <button type="submit" class="btn">Save Presentation</button>
    </form>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Recent Presentations</h3>
    <?php if ($allPresentations): ?>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Topic</th>
                    <th>Presenter</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allPresentations as $pres): ?>
                    <tr>
                        <td><?= getMonthName($pres['month']) ?> <?= $pres['year'] ?></td>
                        <td><?= e(truncate($pres['topic'], 50)) ?></td>
                        <td><?= e($pres['presenter_name'] ?: '-') ?></td>
                        <td>
                            <a href="?month=<?= $pres['month'] ?>&year=<?= $pres['year'] ?>"
                               class="btn btn-small btn-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No presentations recorded yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
