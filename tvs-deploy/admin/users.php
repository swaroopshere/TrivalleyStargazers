<?php
/**
 * TVS Admin - User Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin(); // Only admins can manage users

$pageTitle = 'Users';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? ROLE_PUBLISHER;

            if (empty($username) || empty($password)) {
                redirect('users.php', 'Username and password are required.', 'error');
            }

            $result = auth()->createUser($username, $password, $email, $role);
            if ($result['success']) {
                redirect('users.php', 'User created successfully.');
            } else {
                redirect('users.php', $result['error'], 'error');
            }
            break;

        case 'update':
            $userId = (int)($_POST['user_id'] ?? 0);
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? ROLE_PUBLISHER;

            // Prevent changing own role
            if ($userId === auth()->getUserId()) {
                redirect('users.php', 'You cannot change your own role.', 'error');
            }

            auth()->updateUser($userId, $email, $role);
            redirect('users.php', 'User updated successfully.');
            break;

        case 'change_password':
            $userId = (int)($_POST['user_id'] ?? 0);
            $newPassword = $_POST['new_password'] ?? '';

            if (empty($newPassword)) {
                redirect('users.php', 'Password cannot be empty.', 'error');
            }

            $result = auth()->updatePassword($userId, $newPassword);
            if ($result['success']) {
                redirect('users.php', 'Password updated successfully.');
            } else {
                redirect('users.php', $result['error'], 'error');
            }
            break;

        case 'toggle_active':
            $userId = (int)($_POST['user_id'] ?? 0);

            // Prevent deactivating self
            if ($userId === auth()->getUserId()) {
                redirect('users.php', 'You cannot deactivate your own account.', 'error');
            }

            $user = dbQueryOne("SELECT is_active FROM users WHERE id = ?", [$userId]);
            if ($user) {
                if ($user['is_active']) {
                    auth()->deactivateUser($userId);
                    redirect('users.php', 'User deactivated.');
                } else {
                    auth()->activateUser($userId);
                    redirect('users.php', 'User activated.');
                }
            }
            break;
    }
}

// Get all users
$users = dbQuery("SELECT * FROM users ORDER BY username");

include __DIR__ . '/../includes/templates/admin_header.php';
?>

<div class="page-header">
    <h2>User Management</h2>
    <p>Manage admin and publisher accounts</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    <!-- Create New User -->
    <div class="card">
        <h3>Create New User</h3>
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" required minlength="3">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required minlength="8">
                <p class="help-text">Minimum 8 characters</p>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="<?= ROLE_PUBLISHER ?>">Publisher</option>
                    <option value="<?= ROLE_ADMIN ?>">Admin</option>
                </select>
            </div>

            <button type="submit" class="btn">Create User</button>
        </form>
    </div>

    <!-- User List -->
    <div class="card">
        <h3>Existing Users</h3>
        <?php if ($users): ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?= e($user['username']) ?>
                                <?php if ($user['id'] === auth()->getUserId()): ?>
                                    <span style="color: #666; font-size: 12px;">(you)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($user['email'] ?: '-') ?></td>
                            <td>
                                <span style="background: <?= $user['role'] === ROLE_ADMIN ? '#003354' : '#6c757d' ?>;
                                             color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span style="color: green;">Active</span>
                                <?php else: ?>
                                    <span style="color: red;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $user['last_login'] ? formatDate($user['last_login'], 'M j, Y g:i A') : 'Never' ?>
                            </td>
                            <td>
                                <?php if ($user['id'] !== auth()->getUserId()): ?>
                                    <button onclick="showEditModal(<?= htmlspecialchars(json_encode($user)) ?>)"
                                            class="btn btn-small btn-secondary">Edit</button>
                                    <form method="POST" action="" style="display: inline;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-small <?= $user['is_active'] ? 'btn-danger' : '' ?>">
                                            <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Change Own Password -->
<div class="card" style="margin-top: 20px; max-width: 400px;">
    <h3>Change Your Password</h3>
    <form method="POST" action="">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="change_password">
        <input type="hidden" name="user_id" value="<?= auth()->getUserId() ?>">

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="8">
        </div>

        <button type="submit" class="btn">Update Password</button>
    </form>
</div>

<!-- Edit User Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                           background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 100%; max-width: 400px;">
        <h3 style="margin-bottom: 20px;">Edit User</h3>
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="user_id" id="edit_user_id">

            <div class="form-group">
                <label>Username</label>
                <input type="text" id="edit_username" disabled>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="edit_email">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit_role">
                    <option value="<?= ROLE_PUBLISHER ?>">Publisher</option>
                    <option value="<?= ROLE_ADMIN ?>">Admin</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn">Save Changes</button>
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>

        <hr style="margin: 20px 0;">

        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="user_id" id="edit_user_id_pw">

            <div class="form-group">
                <label>Reset Password</label>
                <input type="password" name="new_password" placeholder="New password" minlength="8">
            </div>

            <button type="submit" class="btn btn-secondary">Reset Password</button>
        </form>
    </div>
</div>

<script>
function showEditModal(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_user_id_pw').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<?php include __DIR__ . '/../includes/templates/admin_footer.php'; ?>
