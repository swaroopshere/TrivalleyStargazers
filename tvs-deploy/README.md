# TVS Beta Website

A modern, responsive website for the Tri-Valley Stargazers astronomy club featuring a complete content management system.

## Quick Start

### Prerequisites
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled
- FTP client for deployment (FileZilla recommended)

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/TrivalleyStargazers.git
   cd TrivalleyStargazers/tvs-deploy
   ```

2. **Configure environment**
   ```bash
   cp .htaccess.example .htaccess
   ```
   Edit `.htaccess` and set your database credentials:
   ```apache
   SetEnv TVS_DB_HOST "localhost"
   SetEnv TVS_DB_NAME "tvs_beta"
   SetEnv TVS_DB_USER "your_username"
   SetEnv TVS_DB_PASS "your_password"
   ```

3. **Create the database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE tvs_beta"
   mysql -u root -p tvs_beta < data/schema.sql
   ```

4. **Start a local server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the site**
   - Public site: http://localhost:8000
   - Admin panel: http://localhost:8000/admin/login.php
   - Default credentials: `admin` / `ChangeMe123!` (change immediately)

## Directory Structure

```
tvs-deploy/
├── admin/              # Admin panel (authentication required)
├── api/                # REST API endpoints
├── data/               # Database schema and migrations
├── docs/               # Developer documentation
├── images/             # Media assets (banners, albums)
├── includes/           # PHP libraries and templates
│   ├── auth.php        # Authentication system
│   ├── config.php      # Configuration constants
│   ├── db.php          # Database abstraction layer
│   ├── functions.php   # Helper functions
│   └── templates/      # Header/footer templates
├── newsletters/        # Newsletter archive (1996-present)
├── tools/              # Database migration tools
├── tvs.css             # Main stylesheet
├── menubar.css         # Navigation styles
├── tvs.js              # Client-side JavaScript
└── *.php               # Public content pages
```

## Technology Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 7.4+ (vanilla, no framework) |
| Database | MySQL/MariaDB with PDO |
| Frontend | Vanilla CSS3 with custom properties |
| JavaScript | Vanilla JS (minimal) |
| Server | Apache with mod_rewrite |

## Documentation

| Document | Description |
|----------|-------------|
| [Architecture](docs/ARCHITECTURE.md) | System design, request flow, database schema |
| [Design System](docs/DESIGN-SYSTEM.md) | CSS tokens, components, responsive design |
| [PHP Reference](docs/PHP-REFERENCE.md) | Backend functions, API, authentication |
| [Admin Guide](docs/ADMIN-GUIDE.md) | Admin panel features, user management |
| [Contributing](docs/CONTRIBUTING.md) | Development workflow, code standards |

## Deployment to iPage

### FTP Deployment

1. **Connect via FTP**
   - Host: `ftp.trivalleystargazers.org`
   - Use your iPage FTP credentials
   - Navigate to the web root directory

2. **Upload files**
   - Upload all files from `tvs-deploy/` to the server
   - Ensure `.htaccess` is uploaded (may be hidden)

3. **Configure production environment**
   Create/edit `.htaccess` on the server with production credentials:
   ```apache
   SetEnv TVS_DB_HOST "your_ipage_mysql_host"
   SetEnv TVS_DB_NAME "your_database_name"
   SetEnv TVS_DB_USER "your_username"
   SetEnv TVS_DB_PASS "your_password"
   SetEnv GROUPS_IO_API_KEY "your_api_key"
   ```

4. **Initialize database**
   - Access phpMyAdmin from iPage control panel
   - Import `data/schema.sql`

5. **Verify deployment**
   - Visit the public site
   - Test admin login
   - Change default admin password immediately

### Post-Deployment Checklist

- [ ] HTTPS redirect working
- [ ] Admin login functional
- [ ] Default password changed
- [ ] Newsletter uploads working
- [ ] Calendar sync configured
- [ ] Security headers present (check with securityheaders.com)

## Key Features

- **Public Site**: Meeting info, presentations, newsletter archive, observing guides
- **Admin Panel**: Manage meetings, events, presentations, newsletters, users
- **Security**: CSRF protection, rate limiting, audit logging, bcrypt passwords
- **Calendar Sync**: Integration with Groups.io calendar
- **Responsive**: Mobile-first design with breakpoints at 640px, 768px, 1024px

## Testing

The project includes comprehensive unit tests for both backend (PHP) and frontend (JavaScript) code.

### Prerequisites

```bash
# Install PHP dependencies (PHPUnit)
composer install

# Install JavaScript dependencies (Jest)
npm install
```

### Running PHP Tests

```bash
# Run unit tests (excludes database tests that require a connection)
composer test

# Run with test output details
vendor/bin/phpunit --testdox

# Run database tests (requires MySQL connection)
vendor/bin/phpunit --testsuite "Database Tests"

# Run all tests including database tests
vendor/bin/phpunit --testsuite "All Tests"
```

### Running JavaScript Tests

```bash
# Run all JavaScript tests
npm test

# Run in watch mode (re-runs on file changes)
npm run test:watch

# Run with coverage report
npm run test:coverage
```

### Test Coverage

| Category | Test File | Coverage |
|----------|-----------|----------|
| Authentication | `tests/php/AuthTest.php` | Password validation, CSRF, sessions, roles |
| Utilities | `tests/php/FunctionsTest.php` | Date/time formatting, escaping, pagination |
| Validation | `tests/php/ValidationTest.php` | Input sanitization, XSS prevention |
| Database | `tests/php/DatabaseTest.php` | PDO wrapper, queries, transactions |
| Forms | `tests/php/FormHandlingTest.php` | Form processing, access control |
| Frontend | `tests/js/tvs.test.js` | Banner animation, PayPal, lightbox, navigation |

## Support

- **Bug Reports**: Open an issue on GitHub
- **Questions**: Contact the webmaster via the club website

## License

Copyright Tri-Valley Stargazers. All rights reserved.
