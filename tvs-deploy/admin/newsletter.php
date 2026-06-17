<?php
/**
 * TVS Admin - Newsletter Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Newsletters';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'upload':
            $month = (int)($_POST['month'] ?? 0);
            $year = (int)($_POST['year'] ?? 0);
            $setAsCurrent = isset($_POST['set_current']);

            if (!$month || !$year) {
                redirect('newsletter.php', 'Month and year are required.', 'error');
            }

            // Security: Validate month and year to prevent directory traversal
            if ($month < 1 || $month > 12) {
                redirect('newsletter.php', 'Invalid month value.', 'error');
            }
            if ($year < 1990 || $year > 2100) {
                redirect('newsletter.php', 'Invalid year value.', 'error');
            }

            if (!isset($_FILES['newsletter_file']) || $_FILES['newsletter_file']['error'] !== UPLOAD_ERR_OK) {
                redirect('newsletter.php', 'Please select a PDF file to upload.', 'error');
            }

            $validation = validateUpload($_FILES['newsletter_file']);
            if (!$validation['success']) {
                redirect('newsletter.php', $validation['error'], 'error');
            }

            // Build filename and path (using validated integers)
            $monthStr = str_pad((int)$month, 2, '0', STR_PAD_LEFT);
            $yearStr = substr((string)(int)$year, 2, 2);
            $filename = "tvsnews{$monthStr}{$yearStr}.pdf";
            $relativePath = "newsletters/" . (int)$year . "/{$filename}";
            $fullPath = ROOT_PATH . '/' . $relativePath;

            // Create year directory if needed (using validated integer)
            $yearDir = ROOT_PATH . "/newsletters/" . (int)$year;
            if (!is_dir($yearDir)) {
                mkdir($yearDir, 0755, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($_FILES['newsletter_file']['tmp_name'], $fullPath)) {
                redirect('newsletter.php', 'Failed to save uploaded file.', 'error');
            }

            // Check if entry exists
            $existing = dbQueryOne(
                "SELECT id FROM newsletters WHERE year = ? AND month = ?",
                [$year, $month]
            );

            if ($existing) {
                dbExecute(
                    "UPDATE newsletters SET filename = ?, file_path = ?, file_size = ?,
                     uploaded_by = ?, uploaded_at = NOW() WHERE id = ?",
                    [$filename, $relativePath, filesize($fullPath), auth()->getUserId(), $existing['id']]
                );
                $newsletterId = $existing['id'];
            } else {
                $newsletterId = dbInsert(
                    "INSERT INTO newsletters (year, month, filename, file_path, file_type, file_size, uploaded_by)
                     VALUES (?, ?, ?, ?, 'pdf', ?, ?)",
                    [$year, $month, $filename, $relativePath, filesize($fullPath), auth()->getUserId()]
                );
            }

            // Set as current if requested
            if ($setAsCurrent) {
                dbExecute("UPDATE newsletters SET is_current = 0");
                dbExecute("UPDATE newsletters SET is_current = 1 WHERE id = ?", [$newsletterId]);
            }

            logAudit(auth()->getUserId(), 'upload_newsletter', 'newsletters', $newsletterId);
            redirect('newsletter.php', 'Newsletter uploaded successfully.');
            break;

        case 'set_current':
            $newsletterId = (int)($_POST['newsletter_id'] ?? 0);
            dbExecute("UPDATE newsletters SET is_current = 0");
            dbExecute("UPDATE newsletters SET is_current = 1 WHERE id = ?", [$newsletterId]);
            logAudit(auth()->getUserId(), 'set_current_newsletter', 'newsletters', $newsletterId);
            redirect('newsletter.php', 'Current newsletter updated.');
            break;

        case 'delete':
            $newsletterId = (int)($_POST['newsletter_id'] ?? 0);
            $newsletter = dbQueryOne("SELECT * FROM newsletters WHERE id = ?", [$newsletterId]);

            if ($newsletter) {
                // Don't delete the actual file (keep archive)
                dbExecute("DELETE FROM newsletters WHERE id = ?", [$newsletterId]);
                logAudit(auth()->getUserId(), 'delete_newsletter', 'newsletters', $newsletterId);
                redirect('newsletter.php', 'Newsletter entry deleted.');
            }
            break;
    }
}

// Get current newsletter
$currentNewsletter = getCurrentNewsletter();

// Get newsletters grouped by year
$years = getNewsletterYears();

// Pagination for year view
$selectedYear = (int)($_GET['year'] ?? date('Y'));
$newsletters = getNewslettersByYear($selectedYear);

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Newsletter Management</h2>
    <p>Upload and manage monthly newsletters</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Upload New Newsletter -->
    <div class="card">
        <h3>Upload Newsletter</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="upload">

            <div class="form-row">
                <div class="form-group">
                    <label>Month</label>
                    <select name="month" required>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                                <?= getMonthName($m) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Year</label>
                    <select name="year" required>
                        <?php for ($y = date('Y') + 1; $y >= 1996; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>PDF File</label>
                <input type="file" name="newsletter_file" accept="application/pdf" required>
                <p class="help-text">Maximum file size: <?= formatFileSize(MAX_UPLOAD_SIZE) ?></p>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="set_current" checked>
                    Set as current newsletter (displayed on homepage)
                </label>
            </div>

            <button type="submit" class="btn">Upload Newsletter</button>
        </form>
    </div>

    <!-- Current Newsletter -->
    <div class="card">
        <h3>Current Newsletter</h3>
        <?php if ($currentNewsletter): ?>
            <div style="padding: 20px; background: #f9f9f9; border-radius: 6px; margin-bottom: 15px;">
                <p style="font-size: 18px; font-weight: 600; color: #003354;">
                    <?= getMonthName($currentNewsletter['month']) ?> <?= $currentNewsletter['year'] ?>
                </p>
                <p style="color: #666; margin-top: 5px;">
                    <?= e($currentNewsletter['filename']) ?>
                    <?php if ($currentNewsletter['file_size']): ?>
                        (<?= formatFileSize($currentNewsletter['file_size']) ?>)
                    <?php endif; ?>
                </p>
                <p style="margin-top: 10px;">
                    <a href="../<?= e($currentNewsletter['file_path']) ?>" target="_blank" class="btn btn-small">
                        View PDF
                    </a>
                </p>
            </div>
        <?php else: ?>
            <p style="color: #666;">No current newsletter set.</p>
        <?php endif; ?>

        <h4 style="margin-top: 20px; margin-bottom: 10px;">Quick Stats</h4>
        <p>Total newsletters in database: <?= dbQueryOne("SELECT COUNT(*) as c FROM newsletters")['c'] ?></p>
        <p>Years covered: <?= count($years) ?></p>
    </div>
</div>

<!-- Newsletter Archive -->
<div class="card" style="margin-top: 20px;">
    <h3>Newsletter Archive</h3>

    <!-- Year selector -->
    <div style="margin-bottom: 20px;">
        <label>View Year: </label>
        <?php foreach ($years as $y): ?>
            <a href="?year=<?= $y['year'] ?>"
               class="btn btn-small <?= $y['year'] == $selectedYear ? '' : 'btn-secondary' ?>"
               style="margin-right: 5px;">
                <?= $y['year'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($newsletters): ?>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($newsletters as $nl): ?>
                    <tr>
                        <td><?= getMonthName($nl['month']) ?></td>
                        <td><?= e($nl['filename']) ?></td>
                        <td><?= $nl['file_size'] ? formatFileSize($nl['file_size']) : '-' ?></td>
                        <td><?= strtoupper($nl['file_type']) ?></td>
                        <td>
                            <?php if ($nl['is_current']): ?>
                                <span style="color: green; font-weight: 600;">Current</span>
                            <?php else: ?>
                                <span style="color: #666;">Archive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="../<?= e($nl['file_path']) ?>" target="_blank"
                               class="btn btn-small btn-secondary">View</a>

                            <?php if (!$nl['is_current']): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="set_current">
                                    <input type="hidden" name="newsletter_id" value="<?= $nl['id'] ?>">
                                    <button type="submit" class="btn btn-small">Set Current</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #666;">No newsletters for <?= $selectedYear ?>.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
