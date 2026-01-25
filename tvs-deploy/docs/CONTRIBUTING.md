# Contributing Guide

Guidelines for contributing to the TVS Beta website project.

## Table of Contents

- [Development Workflow](#development-workflow)
- [Branch Strategy](#branch-strategy)
- [Code Style Guidelines](#code-style-guidelines)
- [Security Best Practices](#security-best-practices)
- [Testing Checklist](#testing-checklist)
- [Pull Request Process](#pull-request-process)

---

## Development Workflow

### Setting Up Your Environment

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/TrivalleyStargazers.git
   cd TrivalleyStargazers
   ```

2. **Switch to the development branch**
   ```bash
   git checkout beta-ui-refresh
   git pull origin beta-ui-refresh
   ```

3. **Set up local environment**
   ```bash
   cd tvs-deploy
   cp .htaccess.example .htaccess
   # Edit .htaccess with your local database credentials
   ```

4. **Initialize the database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE tvs_beta"
   mysql -u root -p tvs_beta < data/schema.sql
   ```

5. **Start local server**
   ```bash
   php -S localhost:8000
   ```

### Daily Workflow

1. Pull latest changes
   ```bash
   git pull origin beta-ui-refresh
   ```

2. Create a feature branch
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. Make your changes

4. Test thoroughly

5. Commit with meaningful messages

6. Push and create pull request

---

## Branch Strategy

### Branch Types

| Branch | Purpose | Base | Merge To |
|--------|---------|------|----------|
| `main` | Production code | - | - |
| `beta-ui-refresh` | Active development | main | main |
| `feature/*` | New features | beta-ui-refresh | beta-ui-refresh |
| `fix/*` | Bug fixes | beta-ui-refresh | beta-ui-refresh |
| `hotfix/*` | Production fixes | main | main, beta-ui-refresh |

### Naming Conventions

```
feature/add-event-categories
feature/newsletter-search
fix/login-redirect-issue
fix/mobile-menu-overlap
hotfix/security-patch-csrf
```

### Branch Protection

- `main`: Requires pull request review
- `beta-ui-refresh`: Requires pull request review
- Direct commits to protected branches are not allowed

---

## Code Style Guidelines

### PHP

#### General

```php
<?php
// File header comment
/**
 * File Description
 * What this file does
 */

// PSR-12 style: 4-space indentation
// Opening braces on same line
// One blank line between functions

function doSomething($param): string
{
    if ($param === null) {
        return '';
    }

    return processValue($param);
}
```

#### Naming Conventions

```php
// Functions: camelCase
function getEventById($id) { }
function formatMeetingDate($date) { }

// Variables: camelCase
$eventData = [];
$currentUser = auth()->getUser();

// Constants: UPPER_SNAKE_CASE
define('MAX_UPLOAD_SIZE', 10485760);

// Classes: PascalCase
class DatabaseConnection { }
```

#### Database Queries

```php
// Always use prepared statements
$user = dbQueryOne(
    "SELECT * FROM users WHERE id = ?",
    [$userId]
);

// Never concatenate user input
// BAD - SQL Injection risk!
$user = dbQueryOne("SELECT * FROM users WHERE id = $userId");
```

#### Output Escaping

```php
// Always escape output
<p><?= e($user['name']) ?></p>

// Never output raw user data
// BAD - XSS risk!
<p><?= $user['name'] ?></p>
```

### CSS

#### Use Design Tokens

```css
/* Good - use CSS variables */
.component {
    color: var(--color-text);
    padding: var(--space-4);
    border-radius: var(--radius);
}

/* Bad - hard-coded values */
.component {
    color: #2c3e50;
    padding: 16px;
    border-radius: 8px;
}
```

#### Naming Convention

```css
/* Block-Element pattern with hyphens */
.card { }
.card-header { }
.card-title { }
.card-body { }

/* Modifier variants */
.card-accent { }
.card-large { }

/* State classes */
.is-active { }
.is-hidden { }
.has-error { }
```

#### Organization

```css
/* Group properties logically */
.element {
    /* Positioning */
    position: relative;
    top: 0;
    z-index: 1;

    /* Box model */
    display: flex;
    width: 100%;
    padding: var(--space-4);
    margin-bottom: var(--space-2);

    /* Typography */
    font-size: var(--text-base);
    font-weight: 500;
    color: var(--color-text);

    /* Visual */
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius);

    /* Animation */
    transition: all var(--transition-base);
}
```

### HTML

#### Semantic Elements

```html
<!-- Good - semantic -->
<header class="site-header">...</header>
<nav class="nav-menu">...</nav>
<main class="main-content">...</main>
<article class="event-card">...</article>
<footer class="site-footer">...</footer>

<!-- Avoid - non-semantic -->
<div class="header">...</div>
<div class="nav">...</div>
```

#### Accessibility

```html
<!-- Include alt text -->
<img src="event.jpg" alt="Members viewing Saturn through telescope">

<!-- Use proper headings hierarchy -->
<h1>Page Title</h1>
<h2>Section Title</h2>
<h3>Subsection Title</h3>

<!-- Label form inputs -->
<label for="email">Email Address</label>
<input type="email" id="email" name="email" required>

<!-- Use ARIA when needed -->
<button aria-label="Close menu" class="mobile-menu-toggle">
```

### JavaScript

```javascript
// Use const/let, not var
const events = [];
let currentIndex = 0;

// Use descriptive names
function toggleMobileMenu() {
    // ...
}

// Document complex logic
/**
 * Formats a date for display
 * @param {string} dateStr - ISO date string
 * @returns {string} Formatted date
 */
function formatDate(dateStr) {
    // ...
}
```

---

## Security Best Practices

### Input Validation

```php
// Validate all user input
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    $error = 'Invalid email address';
}

// Sanitize dates and times
$date = sanitizeDate($_POST['date']);
$time = sanitizeTime($_POST['time']);

// Use type casting
$id = (int) $_GET['id'];
$page = (int) $_GET['page'] ?: 1;
```

### CSRF Protection

```php
// Always include CSRF token in forms
<form method="post">
    <?= csrfField() ?>
    <!-- form fields -->
</form>

// Always validate on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    // process form
}
```

### Authentication

```php
// Check auth on all admin pages
require_once '../includes/auth.php';
requireAuth();

// Check admin role for sensitive features
requireAdmin();
```

### File Uploads

```php
// Validate file type and size
$result = validateUpload($_FILES['file']);
if (!$result['valid']) {
    $error = $result['error'];
}

// Never trust the filename
$filename = sprintf('tvsnews%02d%02d.pdf', $month, $year % 100);

// Store outside web root or validate path
$path = 'newsletters/' . $year . '/' . $filename;
```

### Error Handling

```php
// Don't expose internal errors
try {
    $result = riskyOperation();
} catch (Exception $e) {
    error_log($e->getMessage()); // Log for debugging
    $error = 'An error occurred'; // Generic message to user
}

// Production should have display_errors off
// Set in php.ini or .htaccess
```

---

## Testing Checklist

### Before Every Commit

- [ ] Code runs without PHP errors
- [ ] No JavaScript console errors
- [ ] Pages load correctly
- [ ] Forms submit successfully
- [ ] Flash messages display properly

### Feature Testing

- [ ] Feature works as expected
- [ ] Edge cases handled
- [ ] Error messages are clear
- [ ] Input validation works
- [ ] Database changes are correct

### Security Testing

- [ ] CSRF tokens required on all forms
- [ ] User input is escaped in output
- [ ] SQL uses prepared statements
- [ ] Auth checks on protected pages
- [ ] File uploads validated

### Browser Testing

Test on multiple browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Responsive Testing

Test at these breakpoints:
- [ ] Mobile (< 640px)
- [ ] Tablet (640px - 1024px)
- [ ] Desktop (> 1024px)

### Admin Testing

- [ ] Admin pages require login
- [ ] Admin-only features check role
- [ ] Audit log records changes
- [ ] Flash messages appear correctly

---

## Pull Request Process

### Before Creating a PR

1. **Pull latest changes**
   ```bash
   git checkout beta-ui-refresh
   git pull origin beta-ui-refresh
   git checkout your-branch
   git rebase beta-ui-refresh
   ```

2. **Run through testing checklist**

3. **Review your own changes**
   ```bash
   git diff beta-ui-refresh
   ```

### Creating the PR

1. **Push your branch**
   ```bash
   git push origin your-branch
   ```

2. **Create PR on GitHub**
   - Base: `beta-ui-refresh`
   - Compare: `your-branch`

3. **Write a clear description**
   ```markdown
   ## Summary
   Brief description of what this PR does.

   ## Changes
   - Added feature X
   - Fixed bug Y
   - Updated styling for Z

   ## Testing
   - [ ] Tested on Chrome
   - [ ] Tested on mobile
   - [ ] Tested admin functions

   ## Screenshots
   (If applicable)
   ```

### PR Review

- Address reviewer feedback promptly
- Explain your decisions if asked
- Update tests if requested
- Keep commits focused and atomic

### After Approval

1. **Squash and merge** (preferred for features)
2. **Delete your branch** after merge
3. **Pull the latest** beta-ui-refresh

---

## Commit Messages

### Format

```
type: brief description

Longer explanation if needed.
Explain what and why, not how.

Fixes #123
```

### Types

| Type | Use For |
|------|---------|
| feat | New feature |
| fix | Bug fix |
| style | CSS/formatting changes |
| refactor | Code restructuring |
| docs | Documentation only |
| chore | Maintenance tasks |

### Examples

```
feat: add event type filtering to admin panel

Allow admins to filter the events list by type
(H2O, Tesla, Announcement, etc.)

fix: correct date format on meeting card

Changed from MM/DD/YYYY to "Month Day, Year" format
to match the rest of the site.

style: improve mobile navigation spacing

Increased tap targets and added better padding
for mobile menu items.

docs: update README with deployment steps

Added detailed FTP deployment instructions and
post-deployment checklist.
```

---

## Getting Help

### Documentation

- Check the [Architecture](ARCHITECTURE.md) for system overview
- See [PHP Reference](PHP-REFERENCE.md) for function details
- Review [Design System](DESIGN-SYSTEM.md) for styling

### Questions

- Open a GitHub issue for bugs
- Start a discussion for feature ideas
- Contact the maintainers directly for urgent issues

### Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [MDN Web Docs](https://developer.mozilla.org/)
- [CSS Tricks](https://css-tricks.com/)

---

## Related Documentation

- [Architecture](ARCHITECTURE.md) - System design
- [Design System](DESIGN-SYSTEM.md) - CSS reference
- [PHP Reference](PHP-REFERENCE.md) - Backend API
- [Admin Guide](ADMIN-GUIDE.md) - Admin usage
