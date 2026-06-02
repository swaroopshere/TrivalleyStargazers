# PHP Reference Documentation

Complete API reference for the TVS Beta website backend.

## Table of Contents

- [Configuration (config.php)](#configuration-configphp)
- [Database Functions (db.php)](#database-functions-dbphp)
- [Helper Functions (functions.php)](#helper-functions-functionsphp)
- [Authentication System (auth.php)](#authentication-system-authphp)
- [Template System](#template-system)
- [Creating a New Page](#creating-a-new-page)

---

## Configuration (config.php)

The configuration file defines all site-wide constants and settings.

### Site Identity

```php
define('SITE_NAME', 'Tri-Valley Stargazers');
define('SITE_URL', 'https://trivalleystargazers.org');
define('SITE_EMAIL', 'info@trivalleystargazers.org');
```

### Path Constants

```php
define('ROOT_PATH', __DIR__ . '/..');      // Web root directory
define('INCLUDES_PATH', __DIR__);           // includes/ directory
define('DATA_PATH', ROOT_PATH . '/data');   // data/ directory
define('ADMIN_PATH', ROOT_PATH . '/admin'); // admin/ directory
```

### Database Configuration

Database credentials are loaded from environment variables:

```php
define('DB_HOST', getenv('TVS_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('TVS_DB_NAME') ?: 'tvs_beta');
define('DB_USER', getenv('TVS_DB_USER') ?: 'root');
define('DB_PASS', getenv('TVS_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
```

### Security Settings

```php
define('SESSION_TIMEOUT', 1800);       // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);       // Before lockout
define('LOGIN_LOCKOUT_TIME', 900);     // 15 minutes
define('CSRF_TOKEN_NAME', 'csrf_token');
```

### User Roles

```php
define('ROLE_ADMIN', 'admin');
define('ROLE_PUBLISHER', 'publisher');
define('ROLE_VIEWER', 'viewer');
```

### File Upload Settings

```php
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);  // 10 MB
define('ALLOWED_UPLOAD_TYPES', ['application/pdf']);
```

### Meeting Defaults

```php
define('DEFAULT_MEETING_LOCATION', 'Unitarian Universalist Church');
define('DEFAULT_MEETING_ADDRESS', '1893 N. Vasco Road, Livermore, CA');
```

### API Configuration

```php
define('GROUPS_IO_API_KEY', getenv('GROUPS_IO_API_KEY') ?: '');
define('GROUPS_IO_GROUP', 'trivalleystargazers');
```

---

## Database Functions (db.php)

### Database Class

Singleton pattern database class using PDO.

#### `Database::getInstance()`

Returns the singleton database instance.

```php
$db = Database::getInstance();
```

#### `Database::query(string $sql, array $params = []): array`

Execute a SELECT query and return all results.

```php
$results = $db->query(
    "SELECT * FROM events WHERE event_type = ? AND is_visible = 1",
    ['h2o']
);
// Returns: array of associative arrays
```

#### `Database::queryOne(string $sql, array $params = []): ?array`

Execute a SELECT query and return a single row.

```php
$user = $db->queryOne(
    "SELECT * FROM users WHERE username = ?",
    ['admin']
);
// Returns: associative array or null
```

#### `Database::execute(string $sql, array $params = []): int`

Execute an INSERT, UPDATE, or DELETE query.

```php
$affected = $db->execute(
    "UPDATE meetings SET location = ? WHERE id = ?",
    ['New Location', 1]
);
// Returns: number of affected rows
```

#### `Database::insert(string $sql, array $params = []): int`

Execute an INSERT query and return the last insert ID.

```php
$id = $db->insert(
    "INSERT INTO events (event_type, title, description) VALUES (?, ?, ?)",
    ['h2o', 'Open House', 'Monthly star party']
);
// Returns: last insert ID
```

#### Transaction Methods

```php
$db->beginTransaction();
try {
    $db->execute("UPDATE ...");
    $db->execute("UPDATE ...");
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

### Helper Functions

These functions provide shorthand access to database operations.

#### `db(): Database`

Get the database singleton instance.

```php
$db = db();
```

#### `dbQuery(string $sql, array $params = []): array`

Shorthand for `Database::query()`.

```php
$events = dbQuery("SELECT * FROM events WHERE is_visible = 1");
```

#### `dbQueryOne(string $sql, array $params = []): ?array`

Shorthand for `Database::queryOne()`.

```php
$meeting = dbQueryOne("SELECT * FROM meetings WHERE meeting_type = ?", ['public']);
```

#### `dbExecute(string $sql, array $params = []): int`

Shorthand for `Database::execute()`.

```php
$rows = dbExecute("DELETE FROM events WHERE id = ?", [5]);
```

#### `dbInsert(string $sql, array $params = []): int`

Shorthand for `Database::insert()`.

```php
$id = dbInsert("INSERT INTO events (title) VALUES (?)", ['New Event']);
```

### Business Logic Functions

#### `getCurrentPublicMeeting(): ?array`

Get the current active public meeting.

```php
$meeting = getCurrentPublicMeeting();
// Returns: ['id', 'meeting_date', 'meeting_time', 'location',
//           'location_address', 'description', 'meeting_format']
```

#### `getCurrentBoardMeeting(): ?array`

Get the current active board meeting.

```php
$board = getCurrentBoardMeeting();
```

#### `getCurrentPresentation(): ?array`

Get the presentation for the current month.

```php
$presentation = getCurrentPresentation();
// Returns: ['id', 'month', 'year', 'topic', 'presenter_name',
//           'presenter_title', 'abstract', 'bio', 'is_hybrid']
```

#### `getVisibleEvents(string $type): array`

Get all visible events of a specific type.

```php
$h2oEvents = getVisibleEvents('h2o');
$teslaEvents = getVisibleEvents('tesla');
$announcements = getVisibleEvents('announcement');
```

**Event types:** `'h2o'`, `'tesla'`, `'announcement'`, `'bbq'`, `'potluck'`

#### `getUpcomingEvents(int $limit = 10): array`

Get upcoming calendar events from the cache.

```php
$events = getUpcomingEvents(5);
// Returns: array of events ordered by date
```

#### `getCurrentNewsletter(): ?array`

Get the current/featured newsletter.

```php
$newsletter = getCurrentNewsletter();
// Returns: ['id', 'year', 'month', 'filename', 'file_path']
```

#### `getNewslettersByYear(int $year): array`

Get all newsletters for a specific year.

```php
$newsletters = getNewslettersByYear(2026);
```

#### `getAllUpcomingEvents(int $limit = 15): array`

Get all upcoming events combining meetings, presentations, events, and calendar.

```php
$allEvents = getAllUpcomingEvents(15);
// Returns: unified array of all event types with:
// ['event_type', 'title', 'event_date', 'event_time', 'description', 'location']
```

#### `logAudit(...)`

Log an action to the audit table.

```php
logAudit(
    int $userId,        // User performing action
    string $action,     // Action type: 'login', 'update_meeting', etc.
    string $tableName,  // Table affected
    int $recordId,      // Record ID affected
    ?string $oldValue,  // Previous value (JSON)
    ?string $newValue   // New value (JSON)
);

// Example
logAudit($userId, 'update_meeting', 'meetings', $meetingId,
         json_encode($old), json_encode($new));
```

---

## Helper Functions (functions.php)

### Output Functions

#### `e(string $str): string`

Escape HTML for safe output. **Always use this for user-provided content.**

```php
<p><?= e($user['name']) ?></p>
```

#### `formatDate(?string $date, string $format = 'F j, Y'): string`

Format a date for display.

```php
formatDate('2026-01-17');              // "January 17, 2026"
formatDate('2026-01-17', 'M j');       // "Jan 17"
formatDate('2026-01-17', 'l, F j');    // "Friday, January 17"
```

#### `formatTime(?string $time, string $format = 'g:i A'): string`

Format a time for display.

```php
formatTime('19:30:00');                // "7:30 PM"
formatTime('19:30:00', 'H:i');         // "19:30"
```

#### `formatMeetingDateTime(?string $date, ?string $time): string`

Format combined date and time for meetings.

```php
formatMeetingDateTime('2026-01-17', '19:30:00');
// "Friday, January 17, 2026 at 7:30 PM"
```

#### `getMonthName(int $month): string`

Get month name from number (1-12).

```php
getMonthName(1);   // "January"
getMonthName(12);  // "December"
```

#### `getMeetingFormatLabel(string $format): string`

Get human-readable meeting format label.

```php
getMeetingFormatLabel('in-person');  // "In-Person"
getMeetingFormatLabel('zoom');       // "Zoom Only"
getMeetingFormatLabel('hybrid');     // "Hybrid (In-Person + Zoom)"
```

#### `truncate(string $str, int $length = 100, string $suffix = '...'): string`

Safely truncate a string to specified length.

```php
truncate('Long text here...', 50);      // "Long text here..." (truncated)
truncate('Short', 50);                   // "Short" (unchanged)
```

#### `slugify(string $str): string`

Generate URL-safe slug from string.

```php
slugify('Hello World!');           // "hello-world"
slugify('January 2026 Newsletter'); // "january-2026-newsletter"
```

#### `formatFileSize(int $bytes): string`

Format file size in human-readable format.

```php
formatFileSize(1024);        // "1.00 KB"
formatFileSize(1048576);     // "1.00 MB"
formatFileSize(5242880);     // "5.00 MB"
```

### Contact Functions

#### `contactEmail(string $user, string $domain, string $name): string`

Generate obfuscated email link (basic spam protection).

```php
echo contactEmail('info', 'trivalleystargazers.org', 'Contact Us');
// <a href="mailto:info@trivalleystargazers.org">Contact Us</a>
```

#### `contactLink(string $user, string $domain, string $name): string`

Generate contact link with progressive enhancement.

```php
echo contactLink('membership', 'trivalleystargazers.org', 'Membership Chair');
```

### File Functions

#### `fileExistsPublic(string $path): bool`

Check if a file exists and is readable.

```php
if (fileExistsPublic('newsletters/2026/tvsnews0126.pdf')) {
    // File exists and is readable
}
```

#### `buildNewsletterPath(int $year, int $month, string $type = 'pdf'): string`

Construct newsletter file path.

```php
buildNewsletterPath(2026, 1, 'pdf');
// "newsletters/2026/tvsnews0126.pdf"
```

#### `validateUpload(array $file): array`

Validate an uploaded file.

```php
$result = validateUpload($_FILES['newsletter']);
if ($result['valid']) {
    move_uploaded_file($file['tmp_name'], $destination);
} else {
    echo $result['error'];
}
```

**Returns:**
```php
['valid' => true]
// or
['valid' => false, 'error' => 'Error message']
```

### Navigation Functions

#### `getCurrentPage(): string`

Get current page name from URL.

```php
$page = getCurrentPage();  // e.g., "about" for about.php
```

#### `isCurrentPage(string $page): bool`

Check if specified page is the current page.

```php
<a class="<?= isCurrentPage('about') ? 'active' : '' ?>" href="about.php">About</a>
```

#### `navLink(string $url, string $title, ?string $page = null, ?string $id = null): string`

Generate navigation link with active class handling.

```php
echo navLink('about.php', 'About Us', 'about');
// <a href="about.php" class="nav-link active">About Us</a>
// (when on about.php)
```

#### `isAjax(): bool`

Detect if current request is AJAX.

```php
if (isAjax()) {
    jsonResponse(['success' => true]);
}
```

#### `jsonResponse(array $data, int $status = 200): void`

Send JSON response and exit.

```php
jsonResponse(['success' => true, 'message' => 'Saved']);
// Sets Content-Type: application/json
// Outputs: {"success":true,"message":"Saved"}
// Exits
```

### Session/Redirect Functions

#### `redirect(string $url, ?string $message = null, string $type = 'success'): void`

Redirect with optional flash message.

```php
redirect('admin/index.php', 'Changes saved successfully');
redirect('admin/events.php', 'Error saving event', 'error');
```

#### `getFlashMessage(): ?array`

Get and clear flash message from session.

```php
$flash = getFlashMessage();
if ($flash) {
    echo $flash['message'];  // Message text
    echo $flash['type'];     // 'success', 'error', 'warning'
}
```

#### `showFlashMessage(): void`

Display flash message HTML if present.

```php
<?php showFlashMessage(); ?>
// Outputs: <div class="alert alert-success">Message here</div>
```

### Validation Functions

#### `sanitizeDate(?string $date): ?string`

Validate and sanitize date string.

```php
sanitizeDate('2026-01-17');   // "2026-01-17"
sanitizeDate('invalid');       // null
sanitizeDate('');              // null
```

#### `sanitizeTime(?string $time): ?string`

Validate and sanitize time string.

```php
sanitizeTime('19:30');        // "19:30"
sanitizeTime('19:30:00');     // "19:30:00"
sanitizeTime('invalid');      // null
```

### Pagination Function

#### `paginate(int $total, int $perPage, int $currentPage): array`

Calculate pagination information.

```php
$pagination = paginate(100, 10, 3);
// Returns:
// [
//   'total' => 100,
//   'per_page' => 10,
//   'current_page' => 3,
//   'total_pages' => 10,
//   'offset' => 20,
//   'has_prev' => true,
//   'has_next' => true,
//   'prev_page' => 2,
//   'next_page' => 4
// ]
```

---

## Authentication System (auth.php)

### Auth Class

Singleton pattern authentication class.

#### `Auth::getInstance(): Auth`

Get the authentication singleton.

```php
$auth = Auth::getInstance();
```

#### `Auth::login(string $username, string $password): array`

Authenticate user with rate limiting.

```php
$result = auth()->login($_POST['username'], $_POST['password']);

if ($result['success']) {
    redirect('admin/index.php');
} else {
    echo $result['error'];  // "Invalid credentials" or "Too many attempts"
}
```

**Returns:**
```php
['success' => true]
// or
['success' => false, 'error' => 'Error message']
```

#### `Auth::logout(): void`

End session and clear authentication.

```php
auth()->logout();
redirect('admin/login.php');
```

#### `Auth::isLoggedIn(): bool`

Check if user is authenticated.

```php
if (auth()->isLoggedIn()) {
    // User is logged in
}
```

#### `Auth::getUser(): ?array`

Get current authenticated user data.

```php
$user = auth()->getUser();
// ['id', 'username', 'email', 'role', 'created_at', 'last_login']
```

#### `Auth::getUserId(): ?int`

Get current user's ID.

```php
$userId = auth()->getUserId();
```

#### `Auth::hasRole(string $role): bool`

Check if user has specific role.

```php
if (auth()->hasRole('admin')) {
    // User is admin
}

if (auth()->hasRole('publisher')) {
    // User is publisher (or admin)
}
```

#### `Auth::isAdmin(): bool`

Check if user is admin.

```php
if (auth()->isAdmin()) {
    // Show admin-only features
}
```

#### `Auth::createUser(...): array`

Create a new user account.

```php
$result = auth()->createUser(
    'newuser',           // username
    'SecurePass123!',    // password
    'user@example.com',  // email
    'publisher'          // role
);

if ($result['success']) {
    $userId = $result['user_id'];
} else {
    echo $result['error'];
}
```

#### `Auth::updatePassword(int $userId, string $newPassword): array`

Update user's password.

```php
$result = auth()->updatePassword($userId, 'NewSecurePass123!');
```

#### `Auth::updateUser(int $userId, string $email, string $role): bool`

Update user's email and role.

```php
auth()->updateUser(5, 'newemail@example.com', 'admin');
```

#### `Auth::deactivateUser(int $userId): bool`

Deactivate a user account.

```php
auth()->deactivateUser(5);
```

#### `Auth::activateUser(int $userId): bool`

Reactivate a user account.

```php
auth()->activateUser(5);
```

#### `Auth::getCSRFToken(): string`

Get or generate CSRF token.

```php
$token = auth()->getCSRFToken();
```

#### `Auth::validateCSRF(string $token): bool`

Validate a CSRF token.

```php
if (!auth()->validateCSRF($_POST['csrf_token'])) {
    die('Invalid request');
}
```

#### `Auth::validatePassword(string $password): array`

Validate password meets requirements.

```php
$result = auth()->validatePassword('weak');
// ['valid' => false, 'errors' => ['Password must be at least 12 characters']]

$result = auth()->validatePassword('SecurePass123!');
// ['valid' => true, 'errors' => []]
```

**Password Requirements:**
- Minimum 12 characters
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one digit (0-9)
- At least one special character (!@#$%^&*)
- Not in common password blacklist

### Helper Functions

#### `auth(): Auth`

Get the Auth singleton.

```php
$auth = auth();
```

#### `requireAuth(): void`

Enforce authentication. Redirects to login if not authenticated.

```php
<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAuth();  // Redirects if not logged in

// Rest of admin page...
```

#### `requireAdmin(): void`

Enforce admin role. Redirects if not admin.

```php
requireAdmin();  // Only admins can access
```

#### `csrfField(): string`

Generate CSRF hidden input field.

```php
<form method="post">
    <?= csrfField() ?>
    <!-- rest of form -->
</form>
// Outputs: <input type="hidden" name="csrf_token" value="abc123...">
```

#### `validateCSRF(): bool`

Validate CSRF token from POST request.

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF()) {
        die('Invalid request');
    }
}
```

#### `requireCSRF(): void`

Validate CSRF or die with error.

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    // Process form...
}
```

---

## Template System

### Public Site Templates

#### Header Template (`includes/templates/header.php`)

Include at the start of public pages.

```php
<?php
$pageTitle = 'About Us';  // Set page title
$currentPage = 'about';   // For navigation highlighting
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';
include 'includes/templates/header.php';
?>
```

**Variables used by header.php:**
- `$pageTitle` - Browser tab title
- `$currentPage` - Current page identifier for nav highlighting
- `$heroImage` - Optional hero banner image path
- `$heroTitle` - Optional hero section title

#### Footer Template (`includes/templates/footer.php`)

Include at the end of public pages.

```php
<?php include 'includes/templates/footer.php'; ?>
```

### Admin Templates

#### Admin Header (`includes/templates/admin_header.php`)

Include at the start of admin pages.

```php
<?php
$pageTitle = 'Manage Meetings';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

requireAuth();  // Must be before header include

include '../includes/templates/admin_header.php';
?>
```

#### Admin Footer (`includes/templates/admin_footer.php`)

Include at the end of admin pages.

```php
<?php include '../includes/templates/admin_footer.php'; ?>
```

---

## Creating a New Page

### Public Page Template

Create a new file in the `tvs-deploy` root:

```php
<?php
/**
 * Page Name
 * Description of what this page does
 */

// Configuration and includes
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Page configuration
$pageTitle = 'Page Title';
$currentPage = 'page-name';
$heroImage = 'images/banners/default.jpg';
$heroTitle = 'Page Title';

// Get data from database
$data = dbQuery("SELECT * FROM table WHERE condition = ?", ['value']);

// Include header
include 'includes/templates/header.php';
?>

<!-- Page Content -->
<section class="section">
    <div class="container">
        <div class="intro">
            <p>Introduction paragraph with gold left border.</p>
        </div>

        <h2>Section Heading</h2>

        <?php foreach ($data as $item): ?>
        <div class="card">
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <!-- Alternate background section -->
    </div>
</section>

<?php include 'includes/templates/footer.php'; ?>
```

### Admin Page Template

Create a new file in `tvs-deploy/admin/`:

```php
<?php
/**
 * Admin Feature Name
 * Description of admin functionality
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

requireAuth();
// requireAdmin();  // Uncomment if admin-only

$pageTitle = 'Feature Name';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    // Get and validate input
    $field = trim($_POST['field'] ?? '');

    if (empty($field)) {
        $error = 'Field is required';
    } else {
        // Save to database
        dbExecute("UPDATE table SET field = ? WHERE id = ?", [$field, $id]);

        // Log the action
        logAudit(auth()->getUserId(), 'update_feature', 'table', $id,
                 $oldValue, $newValue);

        redirect('feature.php', 'Changes saved successfully');
    }
}

// Get data for display
$data = dbQueryOne("SELECT * FROM table WHERE id = ?", [1]);

include '../includes/templates/admin_header.php';
?>

<div class="admin-content">
    <h1>Feature Name</h1>

    <?php showFlashMessage(); ?>

    <?php if (isset($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="admin-form">
        <?= csrfField() ?>

        <div class="form-group">
            <label class="form-label" for="field">Field Label</label>
            <input type="text" id="field" name="field" class="form-input"
                   value="<?= e($data['field'] ?? '') ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<?php include '../includes/templates/admin_footer.php'; ?>
```

### Key Implementation Notes

1. **Always escape output** with `e()` function
2. **Use prepared statements** - never concatenate user input into SQL
3. **Validate CSRF** on all POST requests
4. **Check authentication** on admin pages with `requireAuth()`
5. **Log important actions** with `logAudit()`
6. **Use flash messages** for user feedback after redirects

---

## Related Documentation

- [Architecture](ARCHITECTURE.md) - System design and database schema
- [Design System](DESIGN-SYSTEM.md) - CSS and styling
- [Admin Guide](ADMIN-GUIDE.md) - Admin panel usage
- [Contributing](CONTRIBUTING.md) - Development workflow
