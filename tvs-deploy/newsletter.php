<?php
/**
 * TVS Newsletter Page
 *
 * Displays the current newsletter and archive
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Newsletter - Tri-Valley Stargazers';
$pageId = 'm_newsletter';

// Get current newsletter
$currentNewsletter = getCurrentNewsletter();

// Get all newsletter years
$years = getNewsletterYears();

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title">Prime Focus Newsletter</h1>

<?php if ($currentNewsletter): ?>
<div class="card card-accent mb-6">
    <div class="card-body">
        <h3 class="card-title mb-4">Current Issue: <?= getMonthName($currentNewsletter['month']) ?> <?= $currentNewsletter['year'] ?></h3>
        <a href="<?= e($currentNewsletter['file_path']) ?>" target="_blank" class="btn btn-primary">
            Download PDF
        </a>
    </div>
</div>

<div class="newsletter-viewer mb-8">
    <object data="<?= e($currentNewsletter['file_path']) ?>"
            type="application/pdf"
            width="100%"
            height="800px">
        <p class="text-muted">
            Your browser does not support embedded PDFs.
            <a href="<?= e($currentNewsletter['file_path']) ?>" target="_blank">Click here to download the newsletter</a>.
        </p>
    </object>
</div>
<?php else: ?>
<p class="text-muted">No current newsletter available.</p>
<?php endif; ?>

<section class="mt-8">
    <h2 class="section-title">Newsletter Archive</h2>

    <p class="mb-6">
        Click on a year to view available newsletters, then click on a month to view that issue.
    </p>

    <div class="newsletter-archive">
        <?php foreach ($years as $yearData): ?>
            <?php
            $year = $yearData['year'];
            $newsletters = getNewslettersByYear($year);
            ?>
            <details class="archive-year">
                <summary>
                    <?= $year ?> <span class="text-muted">(<?= count($newsletters) ?> issues)</span>
                </summary>
                <div class="archive-months">
                    <?php foreach ($newsletters as $nl): ?>
                        <a href="<?= e($nl['file_path']) ?>" target="_blank" class="archive-month-link">
                            <?= getMonthName($nl['month']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="mt-8">
    <p>
        The newsletter is published monthly (except for August) and is available to members and non-members alike.
        If you would like to contribute an article, please contact the
        <span class="contact-link" data-user="editor" data-domain="trivalleystargazers.org">newsletter editor</span>.
    </p>
</section>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
