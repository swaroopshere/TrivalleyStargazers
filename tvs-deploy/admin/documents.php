<?php
/**
 * TVS Admin - Official Documents Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Official Documents';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $action = $_POST['action'] ?? '';

    if ($action === 'add_type') {
        // Add new document type
        $docType = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($_POST['doc_type'] ?? '')));
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($docType) || empty($title)) {
            redirect('documents.php', 'Document type and title are required.', 'error');
        }

        // Check if type already exists
        $existing = dbQueryOne("SELECT id FROM official_documents WHERE doc_type = ?", [$docType]);
        if ($existing) {
            redirect('documents.php', 'Document type already exists.', 'error');
        }

        dbInsert(
            "INSERT INTO official_documents (doc_type, title, description, sort_order, uploaded_by, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$docType, $title, $description, $sortOrder, auth()->getUserId()]
        );
        logAudit(auth()->getUserId(), 'create_document_type', 'official_documents', 0);
        redirect('documents.php', 'Document type added successfully.');

    } elseif ($action === 'update') {
        // Update document type info
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($id && $title) {
            dbExecute(
                "UPDATE official_documents SET title = ?, description = ?, sort_order = ?, is_active = ?, updated_at = NOW()
                 WHERE id = ?",
                [$title, $description, $sortOrder, $isActive, $id]
            );
            logAudit(auth()->getUserId(), 'update_document_type', 'official_documents', $id);
            redirect('documents.php', 'Document type updated successfully.');
        }

    } elseif ($action === 'upload') {
        // Upload document file
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            redirect('documents.php', 'Invalid document type.', 'error');
        }

        $doc = dbQueryOne("SELECT * FROM official_documents WHERE id = ?", [$id]);
        if (!$doc) {
            redirect('documents.php', 'Document type not found.', 'error');
        }

        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            redirect('documents.php', 'Please select a file to upload.', 'error');
        }

        $file = $_FILES['document'];
        $allowedTypes = ['application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            redirect('documents.php', 'Only PDF files are allowed.', 'error');
        }

        if ($file['size'] > $maxSize) {
            redirect('documents.php', 'File size must be less than 10MB.', 'error');
        }

        // Create documents directory if it doesn't exist
        $uploadDir = ROOT_PATH . '/documents';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $doc['doc_type'] . '_' . date('Y') . '.' . $extension;
        $filePath = 'documents/' . $filename;
        $fullPath = ROOT_PATH . '/' . $filePath;

        // Delete old file if exists
        if ($doc['file_path'] && file_exists(ROOT_PATH . '/' . $doc['file_path'])) {
            unlink(ROOT_PATH . '/' . $doc['file_path']);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            dbExecute(
                "UPDATE official_documents SET filename = ?, file_path = ?, file_size = ?, uploaded_by = ?, updated_at = NOW()
                 WHERE id = ?",
                [$filename, $filePath, $file['size'], auth()->getUserId(), $id]
            );
            logAudit(auth()->getUserId(), 'upload_document', 'official_documents', $id);
            redirect('documents.php', 'Document uploaded successfully.');
        } else {
            redirect('documents.php', 'Failed to upload file.', 'error');
        }

    } elseif ($action === 'delete_file') {
        // Delete document file (not the type)
        $id = (int)($_POST['id'] ?? 0);

        $doc = dbQueryOne("SELECT * FROM official_documents WHERE id = ?", [$id]);
        if ($doc && $doc['file_path'] && file_exists(ROOT_PATH . '/' . $doc['file_path'])) {
            unlink(ROOT_PATH . '/' . $doc['file_path']);
        }

        dbExecute(
            "UPDATE official_documents SET filename = NULL, file_path = NULL, file_size = NULL, updated_at = NOW()
             WHERE id = ?",
            [$id]
        );
        logAudit(auth()->getUserId(), 'delete_document_file', 'official_documents', $id);
        redirect('documents.php', 'Document file deleted.');

    } elseif ($action === 'delete_type') {
        // Delete document type entirely (admin only)
        if (!auth()->isAdmin()) {
            redirect('documents.php', 'Only administrators can delete document types.', 'error');
        }

        $id = (int)($_POST['id'] ?? 0);
        $doc = dbQueryOne("SELECT * FROM official_documents WHERE id = ?", [$id]);

        if ($doc) {
            // Delete file if exists
            if ($doc['file_path'] && file_exists(ROOT_PATH . '/' . $doc['file_path'])) {
                unlink(ROOT_PATH . '/' . $doc['file_path']);
            }

            dbExecute("DELETE FROM official_documents WHERE id = ?", [$id]);
            logAudit(auth()->getUserId(), 'delete_document_type', 'official_documents', $id);
            redirect('documents.php', 'Document type deleted.');
        }
    }
}

// Get all document types
$documents = dbQuery("SELECT * FROM official_documents ORDER BY sort_order ASC, title ASC");

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>Official Documents</h2>
    <p>Manage official club documents (Bylaws, Articles of Incorporation, 501(c)(3) status, etc.)</p>
</div>

<!-- Add New Document Type -->
<div class="card">
    <h3>Add New Document Type</h3>
    <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_type">

        <div class="form-row">
            <div class="form-group">
                <label for="doc_type">Type Slug</label>
                <input type="text" id="doc_type" name="doc_type" required
                       placeholder="e.g., bylaws, minutes-2024"
                       pattern="[a-z0-9_-]+"
                       title="Lowercase letters, numbers, hyphens, and underscores only">
                <p class="help-text">Unique identifier (lowercase, no spaces)</p>
            </div>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required
                       placeholder="e.g., Club Bylaws">
            </div>
            <div class="form-group" style="max-width: 100px;">
                <label for="sort_order">Order</label>
                <input type="number" id="sort_order" name="sort_order" value="0" min="0">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="2"
                      placeholder="Description shown on the public documents page..."></textarea>
        </div>

        <button type="submit" class="btn">Add Document Type</button>
    </form>
</div>

<!-- Existing Document Types -->
<div class="card" style="margin-top: 20px;">
    <h3>Manage Documents</h3>

    <?php if ($documents): ?>
        <?php foreach ($documents as $doc): ?>
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; background: <?= $doc['is_active'] ? '#fff' : '#f9f9f9' ?>;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                <div>
                    <h4 style="margin: 0;"><?= e($doc['title']) ?></h4>
                    <small style="color: #666;">Type: <?= e($doc['doc_type']) ?></small>
                    <?php if (!$doc['is_active']): ?>
                        <span style="color: #dc3545; margin-left: 10px;">(Inactive)</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Current Document Display -->
            <?php if ($doc['file_path'] && file_exists(ROOT_PATH . '/' . $doc['file_path'])): ?>
            <div style="background: #e8f4fd; border: 1px solid #b8daff; border-radius: 6px; padding: 15px; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="color: #004085;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <div style="flex: 1;">
                        <strong style="color: #004085;"><?= e($doc['filename']) ?></strong>
                        <div style="color: #666; font-size: 13px; margin-top: 3px;">
                            Size: <?= formatFileSize($doc['file_size']) ?> |
                            Uploaded: <?= formatDate($doc['updated_at'], 'F j, Y \a\t g:i A') ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="../<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-small">View PDF</a>
                        <a href="../<?= e($doc['file_path']) ?>" download class="btn btn-small btn-secondary">Download</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px; margin-bottom: 15px; color: #856404;">
                <strong>No document uploaded yet.</strong> Use the form below to upload a PDF file.
            </div>
            <?php endif; ?>

            <!-- Upload/Replace Form -->
            <div style="padding: 15px; background: #f5f5f5; border-radius: 4px; margin-bottom: 15px;">
                <label style="font-weight: 600; display: block; margin-bottom: 10px;">
                    <?= $doc['file_path'] ? 'Replace Document' : 'Upload Document' ?>
                </label>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="upload">
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="file" name="document" accept=".pdf" required style="flex: 1;">
                        <button type="submit" class="btn btn-small"><?= $doc['file_path'] ? 'Replace PDF' : 'Upload PDF' ?></button>
                    </div>
                </form>
                <?php if ($doc['file_path']): ?>
                <form method="POST" action="" style="margin-top: 10px;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete_file">
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                    <button type="submit" class="btn btn-small btn-danger"
                            onclick="return confirm('Delete this file? This cannot be undone.')">Delete Current File</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Edit Form -->
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $doc['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?= e($doc['title']) ?>" required>
                    </div>
                    <div class="form-group" style="max-width: 100px;">
                        <label>Order</label>
                        <input type="number" name="sort_order" value="<?= $doc['sort_order'] ?>" min="0">
                    </div>
                    <div class="form-group" style="max-width: 100px;">
                        <label>Active</label>
                        <label class="toggle-switch" style="margin-top: 8px;">
                            <input type="checkbox" name="is_active" <?= $doc['is_active'] ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2"><?= e($doc['description']) ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-small">Save Changes</button>
                    <?php if (auth()->isAdmin()): ?>
                    <button type="submit" name="action" value="delete_type" class="btn btn-small btn-danger"
                            onclick="return confirm('Delete this document type and its file? This cannot be undone.')">Delete Type</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No document types defined yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
