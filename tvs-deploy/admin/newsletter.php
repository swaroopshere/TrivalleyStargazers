<?php
/**
 * TVS Admin - Newsletter Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Newsletters';

$scanResults = null;
$scanSummary = null;
$scanError   = null;
$rootReal    = realpath(ROOT_PATH);

// Directory browser state (GET)
$browseRaw = $_GET['browse'] ?? '';
if (strpos($browseRaw, '..') !== false) {
    $browseRaw = '';
}
if ($browseRaw !== '') {
    $browseAbs = realpath(ROOT_PATH . '/' . ltrim($browseRaw, '/\\'));
    if ($browseAbs === false || strpos($browseAbs . DIRECTORY_SEPARATOR, $rootReal . DIRECTORY_SEPARATOR) !== 0) {
        $browseAbs = $rootReal;
        $browseRaw = '';
    }
} else {
    $browseAbs = $rootReal;
}
$browseRelative = str_replace(
    DIRECTORY_SEPARATOR,
    '/',
    trim(substr($browseAbs, strlen($rootReal)), DIRECTORY_SEPARATOR)
);
$browseListing = array_values(array_filter(
    glob($browseAbs . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [],
    fn($d) => basename($d)[0] !== '.'
));
sort($browseListing);

$breadcrumbs = [['label' => 'Site Root', 'path' => '']];
if ($browseRelative !== '') {
    $parts       = explode('/', $browseRelative);
    $accumulated = '';
    foreach ($parts as $part) {
        $accumulated       = $accumulated !== '' ? $accumulated . '/' . $part : $part;
        $breadcrumbs[]     = ['label' => $part, 'path' => $accumulated];
    }
}

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

        case 'scan_directory':
            $rawDir = trim($_POST['scan_dir'] ?? '');
            $dryRun = isset($_POST['dry_run']);

            if ($rawDir === '') {
                $scanError = 'No directory selected.';
                break;
            }
            if (strpos($rawDir, '..') !== false) {
                $scanError = 'Invalid directory path.';
                break;
            }

            $absPath = realpath(ROOT_PATH . '/' . ltrim($rawDir, '/\\'));

            if ($absPath === false || !is_dir($absPath)) {
                $scanError = 'Directory not found: ' . e($rawDir);
                break;
            }
            if (strpos($absPath . DIRECTORY_SEPARATOR, $rootReal . DIRECTORY_SEPARATOR) !== 0) {
                $scanError = 'Directory must be within the site root.';
                break;
            }

            $scanResults = [];
            $inserted    = 0;
            $skipped     = 0;
            $failed      = 0;

            $yearDirs = glob($absPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            sort($yearDirs);

            foreach ($yearDirs as $yearDir) {
                $yearName = basename($yearDir);
                if (!ctype_digit($yearName) || strlen($yearName) !== 4) continue;

                foreach (glob($yearDir . DIRECTORY_SEPARATOR . 'tvsnews*.pdf') ?: [] as $filePath) {
                    $filename = basename($filePath);
                    $entry    = ['file' => "$yearName/$filename", 'status' => '', 'note' => ''];

                    if (!preg_match('/^tvsnews(\d{2})(\d{2})\.pdf$/i', $filename, $mtch)) {
                        $entry['status'] = 'failed';
                        $entry['note']   = 'Filename does not match tvsnews<MM><YY>.pdf';
                        $scanResults[] = $entry; $failed++; continue;
                    }

                    $month      = (int)$mtch[1];
                    $cy         = (int)date('Y');
                    $century    = (int)(floor($cy / 100) * 100);
                    $parsedYear = $century + (int)$mtch[2];
                    if ($parsedYear > $cy + 10) $parsedYear -= 100;

                    if ($month < 1 || $month > 12 || $parsedYear < 1990) {
                        $entry['status'] = 'failed';
                        $entry['note']   = 'Invalid month or year parsed from filename';
                        $scanResults[] = $entry; $failed++; continue;
                    }

                    if (!is_readable($filePath) || filesize($filePath) === 0) {
                        $entry['status'] = 'failed';
                        $entry['note']   = 'File not readable or empty';
                        $scanResults[] = $entry; $failed++; continue;
                    }

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $filePath);
                    finfo_close($finfo);
                    if ($mime !== 'application/pdf') {
                        $entry['status'] = 'failed';
                        $entry['note']   = 'Not a valid PDF (detected: ' . e($mime) . ')';
                        $scanResults[] = $entry; $failed++; continue;
                    }

                    if (dbQueryOne("SELECT id FROM newsletters WHERE year = ? AND month = ?", [$parsedYear, $month])) {
                        $entry['status'] = 'skipped';
                        $entry['note']   = 'Already in database';
                        $scanResults[] = $entry; $skipped++; continue;
                    }

                    $relativePath = 'newsletters/' . $parsedYear . '/' . $filename;
                    $fileSize     = filesize($filePath);
                    $monthLabel   = getMonthName($month) . ' ' . $parsedYear;

                    if ($dryRun) {
                        $entry['status'] = 'preview';
                        $entry['note']   = $monthLabel . ' — ' . formatFileSize($fileSize);
                        $scanResults[] = $entry; $inserted++; continue;
                    }

                    try {
                        $nlId = dbInsert(
                            "INSERT INTO newsletters (year, month, filename, file_path, file_type, file_size, uploaded_by, uploaded_at, is_current)
                             VALUES (?, ?, ?, ?, 'pdf', ?, ?, NOW(), 0)",
                            [$parsedYear, $month, $filename, $relativePath, $fileSize, auth()->getUserId()]
                        );
                        logAudit(auth()->getUserId(), 'migrate_newsletter', 'newsletters', $nlId);
                        $entry['status'] = 'inserted';
                        $entry['note']   = $monthLabel;
                        $scanResults[] = $entry; $inserted++;
                    } catch (Exception $ex) {
                        $entry['status'] = 'failed';
                        $entry['note']   = $ex->getMessage();
                        $scanResults[] = $entry; $failed++;
                    }
                }
            }

            if (!$dryRun && $inserted > 0) {
                $mostRecent = dbQueryOne(
                    "SELECT id FROM newsletters ORDER BY year DESC, month DESC LIMIT 1"
                );
                if ($mostRecent) {
                    dbExecute("UPDATE newsletters SET is_current = 0");
                    dbExecute("UPDATE newsletters SET is_current = 1 WHERE id = ?", [$mostRecent['id']]);
                }
            }

            $scanSummary = [
                'inserted' => $inserted,
                'skipped'  => $skipped,
                'failed'   => $failed,
                'dryRun'   => $dryRun,
                'dir'      => $rawDir,
            ];
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
                        <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
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

<!-- Bulk Import from Directory -->
<div class="card" style="margin-top: 20px;">
    <h3>Bulk Import from Directory</h3>
    <p style="color: #666; margin-bottom: 15px;">Navigate to the directory containing year subfolders (e.g. <code>2023/</code>, <code>2024/</code>), then scan it to import newsletters into the database.</p>

    <!-- Breadcrumb -->
    <div style="margin-bottom: 10px; font-size: 0.9em;">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php if ($i > 0): ?><span style="color: #bbb; margin: 0 4px;">/</span><?php endif; ?>
            <?php if ($i < count($breadcrumbs) - 1): ?>
                <a href="?browse=<?= urlencode($crumb['path']) ?>"><?= e($crumb['label']) ?></a>
            <?php else: ?>
                <strong><?= e($crumb['label']) ?></strong>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Directory listing -->
    <?php if ($browseListing): ?>
        <div style="margin-bottom: 15px; padding: 8px 12px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
            <?php foreach ($browseListing as $dir): ?>
                <?php
                    $dirRel = str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        trim(substr($dir, strlen($rootReal)), DIRECTORY_SEPARATOR)
                    );
                ?>
                <div style="padding: 3px 0;">
                    <a href="?browse=<?= urlencode($dirRel) ?>" style="font-family: monospace; text-decoration: none; color: #003354;">
                        &#128193; <?= e(basename($dir)) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: #999; font-style: italic; margin-bottom: 15px;">No subdirectories here.</p>
    <?php endif; ?>

    <!-- Scan form -->
    <form method="POST" action="newsletter.php<?= $browseRelative !== '' ? '?browse=' . urlencode($browseRelative) : '' ?>">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="scan_directory">
        <input type="hidden" name="scan_dir" value="<?= e($browseRelative) ?>">
        <div class="form-group" style="margin-bottom: 10px;">
            <label>
                <input type="checkbox" name="dry_run" <?= isset($_POST['dry_run']) ? 'checked' : '' ?>>
                Dry run (preview only, no changes saved)
            </label>
        </div>
        <button type="submit" class="btn"<?= $browseRelative === '' ? ' disabled style="opacity: 0.5; cursor: default;"' : '' ?>>
            Scan: <?= $browseRelative !== '' ? e($browseRelative) : 'select a directory above' ?>
        </button>
    </form>

    <?php if ($scanError): ?>
        <div style="margin-top: 15px; padding: 10px; background: #fdd; border: 1px solid #c00; border-radius: 4px; color: #900;">
            <?= $scanError ?>
        </div>
    <?php endif; ?>

    <?php if ($scanSummary !== null): ?>
        <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
            <p style="margin-bottom: 8px;"><strong>Results for <code><?= e($scanSummary['dir']) ?></code><?= $scanSummary['dryRun'] ? ' <em>(dry run)</em>' : '' ?>:</strong>
                <span style="color: green; margin-left: 12px;"><?= $scanSummary['inserted'] ?> <?= $scanSummary['dryRun'] ? 'to insert' : 'inserted' ?></span>
                <span style="color: #666; margin-left: 12px;"><?= $scanSummary['skipped'] ?> skipped</span>
                <?php if ($scanSummary['failed']): ?>
                    <span style="color: #c00; margin-left: 12px;"><?= $scanSummary['failed'] ?> failed</span>
                <?php endif; ?>
            </p>
            <?php if ($scanResults): ?>
                <table style="width: 100%; font-size: 0.9em; margin-top: 8px;">
                    <thead><tr><th>File</th><th>Status</th><th>Note</th></tr></thead>
                    <tbody>
                        <?php foreach ($scanResults as $r):
                            $c = match($r['status']) {
                                'inserted' => 'green',
                                'preview'  => '#0066cc',
                                'skipped'  => '#888',
                                'failed'   => '#c00',
                                default    => 'inherit',
                            }; ?>
                            <tr>
                                <td style="font-family: monospace;"><?= e($r['file']) ?></td>
                                <td style="color: <?= $c ?>; font-weight: 600;"><?= e($r['status']) ?></td>
                                <td style="color: #555;"><?= e($r['note']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666; margin-top: 8px;">No newsletter PDF files found in this directory.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
