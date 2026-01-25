<?php
/**
 * TVS Admin - Contacts Management
 *
 * Manage officers, board members, volunteers, and astrophoto links
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Contacts';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_contact':
            $category = $_POST['category'] ?? '';
            $position = trim($_POST['position'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $emailUser = trim($_POST['email_user'] ?? '') ?: null;
            $emailDomain = trim($_POST['email_domain'] ?? 'trivalleystargazers.org');
            $title = trim($_POST['title'] ?? '') ?: null;
            $websiteUrl = trim($_POST['website_url'] ?? '') ?: null;
            $websiteTitle = trim($_POST['website_title'] ?? '') ?: null;
            $sortOrder = (int)($_POST['sort_order'] ?? 0);

            if (!in_array($category, ['officer', 'board', 'volunteer', 'astrophoto'])) {
                redirect('contacts.php', 'Invalid category.', 'error');
            }

            if (empty($position) || empty($name)) {
                redirect('contacts.php', 'Position and name are required.', 'error');
            }

            $id = dbInsert(
                "INSERT INTO contacts (category, position, name, email_user, email_domain, title, website_url, website_title, sort_order, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$category, $position, $name, $emailUser, $emailDomain, $title, $websiteUrl, $websiteTitle, $sortOrder, auth()->getUserId()]
            );
            logAudit(auth()->getUserId(), 'create_contact', 'contacts', $id);
            redirect('contacts.php?tab=' . $category, 'Contact added successfully.');
            break;

        case 'update_contact':
            $contactId = (int)($_POST['contact_id'] ?? 0);
            $position = trim($_POST['position'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $emailUser = trim($_POST['email_user'] ?? '') ?: null;
            $emailDomain = trim($_POST['email_domain'] ?? 'trivalleystargazers.org');
            $title = trim($_POST['title'] ?? '') ?: null;
            $websiteUrl = trim($_POST['website_url'] ?? '') ?: null;
            $websiteTitle = trim($_POST['website_title'] ?? '') ?: null;
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($position) || empty($name)) {
                redirect('contacts.php', 'Position and name are required.', 'error');
            }

            dbExecute(
                "UPDATE contacts SET position = ?, name = ?, email_user = ?, email_domain = ?,
                 title = ?, website_url = ?, website_title = ?, sort_order = ?, is_active = ?,
                 updated_by = ? WHERE id = ?",
                [$position, $name, $emailUser, $emailDomain, $title, $websiteUrl, $websiteTitle, $sortOrder, $isActive, auth()->getUserId(), $contactId]
            );
            logAudit(auth()->getUserId(), 'update_contact', 'contacts', $contactId);

            $contact = getContactById($contactId);
            redirect('contacts.php?tab=' . ($contact['category'] ?? ''), 'Contact updated successfully.');
            break;

        case 'delete_contact':
            $contactId = (int)($_POST['contact_id'] ?? 0);
            $contact = getContactById($contactId);
            dbExecute("DELETE FROM contacts WHERE id = ?", [$contactId]);
            logAudit(auth()->getUserId(), 'delete_contact', 'contacts', $contactId);
            redirect('contacts.php?tab=' . ($contact['category'] ?? ''), 'Contact deleted successfully.');
            break;

        case 'toggle_active':
            $contactId = (int)($_POST['contact_id'] ?? 0);
            dbExecute("UPDATE contacts SET is_active = NOT is_active, updated_by = ? WHERE id = ?",
                [auth()->getUserId(), $contactId]);
            logAudit(auth()->getUserId(), 'toggle_contact_active', 'contacts', $contactId);
            redirect('contacts.php', 'Contact status toggled.');
            break;

        case 'reorder':
            $contactId = (int)($_POST['contact_id'] ?? 0);
            $direction = $_POST['direction'] ?? '';
            $contact = getContactById($contactId);

            if ($contact) {
                $newOrder = $contact['sort_order'] + ($direction === 'up' ? -1 : 1);
                $newOrder = max(0, $newOrder);
                dbExecute("UPDATE contacts SET sort_order = ?, updated_by = ? WHERE id = ?",
                    [$newOrder, auth()->getUserId(), $contactId]);
            }
            redirect('contacts.php?tab=' . ($contact['category'] ?? ''));
            break;
    }
}

// Get current tab
$currentTab = $_GET['tab'] ?? 'officer';
if (!in_array($currentTab, ['officer', 'board', 'volunteer', 'astrophoto'])) {
    $currentTab = 'officer';
}

// Get contacts by category
$officers = dbQuery("SELECT * FROM contacts WHERE category = 'officer' ORDER BY sort_order ASC");
$boardMembers = dbQuery("SELECT * FROM contacts WHERE category = 'board' ORDER BY sort_order ASC");
$volunteers = dbQuery("SELECT * FROM contacts WHERE category = 'volunteer' ORDER BY sort_order ASC");
$astrophotoLinks = dbQuery("SELECT * FROM contacts WHERE category = 'astrophoto' ORDER BY sort_order ASC");

// Get contact for editing if specified
$editContact = null;
if (isset($_GET['edit'])) {
    $editContact = getContactById((int)$_GET['edit']);
}

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<style>
.tabs {
    display: flex;
    gap: 5px;
    margin-bottom: 20px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 0;
}

.tab {
    padding: 10px 20px;
    background: #f0f0f0;
    border: none;
    border-radius: 6px 6px 0 0;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    color: #333;
    margin-bottom: -2px;
}

.tab:hover {
    background: #e0e0e0;
}

.tab.active {
    background: #003354;
    color: white;
    border-bottom: 2px solid #003354;
}

.contact-table {
    width: 100%;
    border-collapse: collapse;
}

.contact-table th,
.contact-table td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.contact-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.contact-table tr:hover {
    background: #f8f9fa;
}

.contact-table .inactive {
    opacity: 0.5;
}

.btn-group {
    display: flex;
    gap: 5px;
}

.btn-icon {
    padding: 5px 8px;
    font-size: 12px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-grid .full-width {
    grid-column: 1 / -1;
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-inactive {
    background: #f8d7da;
    color: #721c24;
}

.open-position {
    color: #856404;
    background: #fff3cd;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}
</style>

<div class="page-header">
    <h2>Contacts Management</h2>
    <p>Manage officers, board members, volunteers, and member astrophoto links</p>
</div>

<!-- Tabs -->
<div class="tabs">
    <a href="?tab=officer" class="tab <?= $currentTab === 'officer' ? 'active' : '' ?>">
        Officers (<?= count($officers) ?>)
    </a>
    <a href="?tab=board" class="tab <?= $currentTab === 'board' ? 'active' : '' ?>">
        Board (<?= count($boardMembers) ?>)
    </a>
    <a href="?tab=volunteer" class="tab <?= $currentTab === 'volunteer' ? 'active' : '' ?>">
        Volunteers (<?= count($volunteers) ?>)
    </a>
    <a href="?tab=astrophoto" class="tab <?= $currentTab === 'astrophoto' ? 'active' : '' ?>">
        Astrophoto Links (<?= count($astrophotoLinks) ?>)
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Contact List -->
    <div class="card">
        <h3>
            <?php
            $titles = [
                'officer' => 'Officers',
                'board' => 'Board Members',
                'volunteer' => 'Volunteer Positions',
                'astrophoto' => 'Member Astrophoto Links'
            ];
            echo $titles[$currentTab];
            ?>
        </h3>

        <?php
        $contacts = match($currentTab) {
            'officer' => $officers,
            'board' => $boardMembers,
            'volunteer' => $volunteers,
            'astrophoto' => $astrophotoLinks,
            default => []
        };
        ?>

        <?php if (empty($contacts)): ?>
            <p style="color: #666; padding: 20px;">No contacts in this category yet.</p>
        <?php else: ?>
            <table class="contact-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Position</th>
                        <th>Name</th>
                        <?php if ($currentTab === 'astrophoto'): ?>
                            <th>Website</th>
                        <?php else: ?>
                            <th>Email</th>
                        <?php endif; ?>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr class="<?= $contact['is_active'] ? '' : 'inactive' ?>">
                        <td><?= $contact['sort_order'] ?></td>
                        <td><?= e($contact['position']) ?></td>
                        <td>
                            <?= e($contact['name']) ?>
                            <?php if (strtolower($contact['name']) === 'open'): ?>
                                <span class="open-position">Open</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($currentTab === 'astrophoto'): ?>
                            <td>
                                <?php if ($contact['website_url']): ?>
                                    <a href="<?= e($contact['website_url']) ?>" target="_blank" style="font-size: 12px;">
                                        <?= e(truncate($contact['website_url'], 30)) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        <?php else: ?>
                            <td>
                                <?php if ($contact['email_user']): ?>
                                    <span style="font-size: 12px; color: #666;">
                                        <?= e($contact['email_user']) ?>@<?= e($contact['email_domain']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?php if ($contact['is_active']): ?>
                                <span class="badge badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge badge-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="?tab=<?= $currentTab ?>&edit=<?= $contact['id'] ?>" class="btn btn-small btn-secondary btn-icon" title="Edit">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="reorder">
                                    <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-small btn-secondary btn-icon" title="Move up">&uarr;</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="reorder">
                                    <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-small btn-secondary btn-icon" title="Move down">&darr;</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Add/Edit Form -->
    <div class="card">
        <h3><?= $editContact ? 'Edit Contact' : 'Add New Contact' ?></h3>

        <?php if ($editContact): ?>
            <p style="margin-bottom: 15px;">
                <a href="?tab=<?= $currentTab ?>" class="btn btn-small btn-secondary">Cancel Edit</a>
            </p>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="<?= $editContact ? 'update_contact' : 'add_contact' ?>">
            <?php if ($editContact): ?>
                <input type="hidden" name="contact_id" value="<?= $editContact['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required <?= $editContact ? 'disabled' : '' ?>>
                    <option value="officer" <?= ($editContact ? $editContact['category'] : $currentTab) === 'officer' ? 'selected' : '' ?>>Officer</option>
                    <option value="board" <?= ($editContact ? $editContact['category'] : $currentTab) === 'board' ? 'selected' : '' ?>>Board Member</option>
                    <option value="volunteer" <?= ($editContact ? $editContact['category'] : $currentTab) === 'volunteer' ? 'selected' : '' ?>>Volunteer</option>
                    <option value="astrophoto" <?= ($editContact ? $editContact['category'] : $currentTab) === 'astrophoto' ? 'selected' : '' ?>>Astrophoto Link</option>
                </select>
                <?php if ($editContact): ?>
                    <input type="hidden" name="category" value="<?= e($editContact['category']) ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Position/Role *</label>
                <input type="text" name="position" required placeholder="e.g., President, Newsletter Editor"
                       value="<?= e($editContact['position'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required placeholder="Full name or 'Open' if vacant"
                       value="<?= e($editContact['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Email Username</label>
                <input type="text" name="email_user" placeholder="e.g., president"
                       value="<?= e($editContact['email_user'] ?? '') ?>">
                <p class="help-text">Just the part before @ (leave blank for no email)</p>
            </div>

            <div class="form-group">
                <label>Email Domain</label>
                <input type="text" name="email_domain" placeholder="trivalleystargazers.org"
                       value="<?= e($editContact['email_domain'] ?? 'trivalleystargazers.org') ?>">
            </div>

            <div class="form-group">
                <label>Title (optional)</label>
                <input type="text" name="title" placeholder="Additional title or description"
                       value="<?= e($editContact['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Website URL (for astrophoto links)</label>
                <input type="url" name="website_url" placeholder="https://..."
                       value="<?= e($editContact['website_url'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Website Title</label>
                <input type="text" name="website_title" placeholder="e.g., John's Astrophoto Page"
                       value="<?= e($editContact['website_title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" min="0" max="100"
                       value="<?= e($editContact['sort_order'] ?? count($contacts) + 1) ?>">
                <p class="help-text">Lower numbers appear first</p>
            </div>

            <?php if ($editContact): ?>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" <?= $editContact['is_active'] ? 'checked' : '' ?>>
                    Active (visible on website)
                </label>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <button type="submit" class="btn"><?= $editContact ? 'Update Contact' : 'Add Contact' ?></button>

                <?php if ($editContact): ?>
                    <button type="button" class="btn btn-secondary" onclick="confirmDelete(<?= $editContact['id'] ?>)">
                        Delete
                    </button>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($editContact): ?>
        <form method="POST" id="deleteForm" style="display: none;">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete_contact">
            <input type="hidden" name="contact_id" value="<?= $editContact['id'] ?>">
        </form>
        <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this contact? This cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }
        </script>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Tips -->
<div class="card" style="margin-top: 20px;">
    <h3>Tips</h3>
    <ul style="margin: 0; padding-left: 20px; color: #666;">
        <li>Use "Open" as the name for vacant positions - it will be styled differently on the website</li>
        <li>Email addresses are obfuscated on the website to reduce spam</li>
        <li>Use the arrow buttons to reorder contacts within each category</li>
        <li>Inactive contacts won't appear on the public website</li>
        <li>For astrophoto links, the website URL and title are required</li>
    </ul>
</div>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
