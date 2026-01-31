<?php
/**
 * TVS Official Documents Page
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Official TVS Documents';
$pageId = 'm_documents';

// Get all active documents with files
$documents = dbQuery(
    "SELECT * FROM official_documents WHERE is_active = 1 ORDER BY sort_order ASC, title ASC"
);

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title">Official TVS Documents</h1>

<div class="documents-page">
    <p class="intro">
        The Tri-Valley Stargazers is a registered 501(c)(3) non-profit organization.
        Below you will find our official organizational documents available for download.
    </p>

    <?php if ($documents): ?>
    <div class="documents-list">
        <?php foreach ($documents as $doc): ?>
        <div class="document-item">
            <div class="document-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="document-info">
                <h3><?= e($doc['title']) ?></h3>
                <?php if ($doc['description']): ?>
                <p><?= e($doc['description']) ?></p>
                <?php endif; ?>
                <?php if ($doc['file_path'] && file_exists(ROOT_PATH . '/' . $doc['file_path'])): ?>
                <p class="document-meta">
                    <a href="<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-primary">
                        Download PDF
                    </a>
                    <span class="file-size">(<?= formatFileSize($doc['file_size']) ?>)</span>
                </p>
                <?php else: ?>
                <p class="document-unavailable">
                    <em>Document not yet available</em>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>No documents are currently available.</p>
    <?php endif; ?>

    <div class="documents-contact">
        <h2 class="subtitle">Questions?</h2>
        <p>
            If you have any questions about these documents or need additional information about the
            Tri-Valley Stargazers organization, please contact us at
            <span class="contact-link" data-user="secretary" data-domain="trivalleystargazers.org">secretary@trivalleystargazers.org</span>.
        </p>
        <p>
            Our mailing address is: P.O. Box 2476, Livermore, CA 94551<br>
            Tax Identification Number: 68-0243508
        </p>
    </div>
</div>

<style>
.documents-page {
    max-width: 800px;
}

.documents-page .intro {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    color: #555;
}

.documents-list {
    margin: 2rem 0;
}

.document-item {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.document-icon {
    flex-shrink: 0;
    color: #003354;
}

.document-info h3 {
    margin: 0 0 0.5rem 0;
    color: #003354;
    font-size: 1.25rem;
}

.document-info p {
    margin: 0 0 0.75rem 0;
    color: #666;
}

.document-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.file-size {
    color: #888;
    font-size: 0.9rem;
}

.document-unavailable {
    color: #999;
}

.documents-contact {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #ddd;
}

.documents-contact p {
    color: #555;
}
</style>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
