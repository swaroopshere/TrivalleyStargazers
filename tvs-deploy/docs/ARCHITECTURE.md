# Architecture Documentation

This document describes the high-level architecture of the TVS Beta website.

## Table of Contents

- [System Architecture](#system-architecture)
- [Directory Structure](#directory-structure)
- [Request Flow](#request-flow)
- [Database Schema](#database-schema)
- [Security Architecture](#security-architecture)
- [Environment Configuration](#environment-configuration)

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         Browser (Client)                             │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     Apache Web Server                                │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  .htaccess                                                   │    │
│  │  - HTTPS redirect                                           │    │
│  │  - Security headers                                         │    │
│  │  - Environment variables                                    │    │
│  │  - Directory protection                                     │    │
│  │  - Cache control                                            │    │
│  └─────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        PHP Application                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────────┐   │
│  │ Public Pages │  │ Admin Panel  │  │     API Endpoints        │   │
│  │  index.php   │  │  admin/*.php │  │  api/calendar-sync.php   │   │
│  │  about.php   │  │              │  │                          │   │
│  │  etc.        │  │              │  │                          │   │
│  └──────────────┘  └──────────────┘  └──────────────────────────┘   │
│         │                 │                      │                   │
│         └─────────────────┼──────────────────────┘                   │
│                           ▼                                          │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │                    includes/                                 │    │
│  │  ┌─────────┐ ┌──────┐ ┌───────────┐ ┌──────────────────┐    │    │
│  │  │config.php│ │db.php│ │functions.php│ │    auth.php      │    │    │
│  │  └─────────┘ └──────┘ └───────────┘ └──────────────────┘    │    │
│  │                                                              │    │
│  │  ┌─────────────────────────────────────────────────────┐    │    │
│  │  │                  templates/                          │    │    │
│  │  │  header.php  footer.php  admin_header.php            │    │    │
│  │  └─────────────────────────────────────────────────────┘    │    │
│  └─────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      MySQL Database                                  │
│  ┌─────────┐ ┌─────────────┐ ┌────────┐ ┌────────────────────┐      │
│  │  users  │ │  meetings   │ │ events │ │  presentations     │      │
│  └─────────┘ └─────────────┘ └────────┘ └────────────────────┘      │
│  ┌─────────────┐ ┌───────────────┐ ┌──────────┐ ┌──────────────┐    │
│  │ newsletters │ │ calendar_cache│ │audit_log │ │login_attempts│    │
│  └─────────────┘ └───────────────┘ └──────────┘ └──────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Directory Structure

```
tvs-deploy/
├── .well-known/              # ACME challenge (SSL certificates)
│
├── admin/                    # Admin panel (protected)
│   ├── index.php             # Dashboard with stats and activity
│   ├── login.php             # Authentication page
│   ├── logout.php            # Session termination
│   ├── meetings.php          # Public/board meeting editor
│   ├── presentation.php      # Monthly presentation editor
│   ├── events.php            # H2O/Tesla/announcement manager
│   ├── newsletter.php        # Newsletter upload and archive
│   ├── users.php             # User management (admin only)
│   ├── contacts.php          # Contact information editor
│   ├── sync-calendar.php     # Groups.io calendar sync trigger
│   └── setup.php             # Initial installation
│
├── api/                      # REST API endpoints
│   └── calendar-sync.php     # Calendar sync endpoint
│
├── data/                     # Database files (not web-accessible)
│   └── schema.sql            # Complete database schema
│
├── docs/                     # Developer documentation
│   ├── ARCHITECTURE.md       # This file
│   ├── DESIGN-SYSTEM.md      # CSS and styling guide
│   ├── PHP-REFERENCE.md      # Backend code reference
│   ├── ADMIN-GUIDE.md        # Admin panel usage
│   └── CONTRIBUTING.md       # Contributor guidelines
│
├── images/                   # Static media assets
│   ├── banners/              # Hero banner images
│   ├── fire/                 # Photo album
│   ├── marling/              # Photo album
│   ├── neowise/              # Photo album
│   ├── h2o_rebuild/          # Observatory photos
│   └── temp/                 # Temporary uploads
│
├── includes/                 # PHP libraries (not web-accessible)
│   ├── config.php            # Constants and configuration
│   ├── db.php                # Database abstraction layer
│   ├── functions.php         # Utility helper functions
│   ├── auth.php              # Authentication system
│   └── templates/            # Reusable HTML templates
│       ├── header.php        # Public site header
│       ├── footer.php        # Public site footer
│       ├── admin_header.php  # Admin panel header
│       └── admin_footer.php  # Admin panel footer
│
├── newsletters/              # Newsletter archive
│   └── YYYY/                 # Year directories (1996-2026)
│       └── tvsnewsMMYY.pdf   # Monthly newsletter PDFs
│
├── tools/                    # Maintenance utilities
│   └── add_contacts_table.php # Database migration
│
├── [Public PHP Pages]        # 40+ content pages
│   ├── index.php             # Home page
│   ├── about.php             # About the club
│   ├── membership.php        # Join/renew membership
│   ├── donation.php          # Donation page
│   ├── newsletter.php        # Newsletter viewer
│   ├── allEvents.php         # Events calendar
│   └── ...                   # Many more content pages
│
├── tvs.css                   # Main stylesheet (1900+ lines)
├── menubar.css               # Navigation styles
├── normalize.css             # CSS reset
├── tvs.js                    # Client-side JavaScript
│
├── .htaccess.example         # Server config template
└── CLAUDE.md                 # AI assistant context
```

### Directory Protection

The following directories are blocked from direct web access via `.htaccess`:

| Directory | Protection | Reason |
|-----------|------------|--------|
| `/includes/` | Deny all | Contains sensitive configuration |
| `/data/` | Deny all | Contains database schema |
| `/admin/` | Auth required | Administrative functions |

---

## Request Flow

### Public Page Request

```
1. Browser requests /about.php
         │
         ▼
2. Apache processes .htaccess
   - Applies security headers
   - Sets environment variables
         │
         ▼
3. PHP loads about.php
         │
         ▼
4. about.php includes config.php
   - Constants loaded
   - Timezone set
   - Error reporting configured
         │
         ▼
5. about.php includes db.php
   - Database singleton initialized
   - PDO connection established (lazy)
         │
         ▼
6. about.php includes functions.php
   - Helper functions available
         │
         ▼
7. about.php includes header.php template
   - HTML <head> rendered
   - Navigation rendered
   - Hero banner rendered
         │
         ▼
8. about.php renders page content
   - Database queries via dbQuery()
   - Output escaped via e()
         │
         ▼
9. about.php includes footer.php template
   - Footer HTML rendered
   - JavaScript loaded
         │
         ▼
10. Response sent to browser
```

### Admin Page Request

```
1. Browser requests /admin/meetings.php
         │
         ▼
2. Apache processes .htaccess
         │
         ▼
3. PHP loads meetings.php
         │
         ▼
4. meetings.php includes config.php
         │
         ▼
5. meetings.php includes auth.php
         │
         ▼
6. requireAuth() called
   ├── Session exists? ──No──▶ Redirect to login.php
   │
   └── Session valid? ──No──▶ Redirect to login.php
         │
        Yes
         ▼
7. CSRF validation for POST requests
   ├── Token valid? ──No──▶ 403 Forbidden
   │
   └── Token matches? ──Yes──▶ Continue
         │
         ▼
8. Process form submission (if POST)
   - Validate input
   - Execute database operation
   - Log to audit_log
   - Set flash message
         │
         ▼
9. Include admin_header.php
   - Admin navigation rendered
         │
         ▼
10. Render admin form/content
         │
         ▼
11. Include admin_footer.php
         │
         ▼
12. Response sent to browser
```

---

## Database Schema

### Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────┐
│     users       │       │    meetings     │
├─────────────────┤       ├─────────────────┤
│ id (PK)         │◄──┐   │ id (PK)         │
│ username        │   │   │ meeting_type    │
│ password_hash   │   │   │ meeting_date    │
│ email           │   │   │ meeting_time    │
│ role            │   │   │ location        │
│ created_at      │   │   │ location_address│
│ last_login      │   │   │ description     │
│ is_active       │   └───┤ updated_by (FK) │
└─────────────────┘       │ updated_at      │
        │                 │ meeting_format  │
        │                 │ is_active       │
        │                 └─────────────────┘
        │
        │                 ┌─────────────────┐
        │                 │  presentations  │
        │                 ├─────────────────┤
        │                 │ id (PK)         │
        │                 │ month           │
        │                 │ year            │
        │                 │ topic           │
        │                 │ presenter_name  │
        │                 │ presenter_title │
        │                 │ abstract        │
        │                 │ bio             │
        │                 │ is_hybrid       │
        └────────────────►│ updated_by (FK) │
        │                 │ updated_at      │
        │                 └─────────────────┘
        │
        │                 ┌─────────────────┐
        │                 │     events      │
        │                 ├─────────────────┤
        │                 │ id (PK)         │
        │                 │ event_type      │
        │                 │ event_date      │
        │                 │ title           │
        │                 │ description     │
        │                 │ is_visible      │
        │                 │ sort_order      │
        └────────────────►│ updated_by (FK) │
        │                 │ updated_at      │
        │                 └─────────────────┘
        │
        │                 ┌─────────────────┐
        │                 │   newsletters   │
        │                 ├─────────────────┤
        │                 │ id (PK)         │
        │                 │ year            │
        │                 │ month           │
        │                 │ filename        │
        │                 │ file_path       │
        │                 │ file_type       │
        │                 │ file_size       │
        └────────────────►│ uploaded_by (FK)│
        │                 │ uploaded_at     │
        │                 │ is_current      │
        │                 └─────────────────┘
        │
        │                 ┌─────────────────┐
        │                 │   audit_log     │
        │                 ├─────────────────┤
        │                 │ id (PK)         │
        └────────────────►│ user_id (FK)    │
                          │ action          │
                          │ table_name      │
                          │ record_id       │
                          │ old_value       │
                          │ new_value       │
                          │ ip_address      │
                          │ created_at      │
                          └─────────────────┘

┌─────────────────┐       ┌─────────────────┐
│ calendar_cache  │       │ login_attempts  │
├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │
│ event_id        │       │ username        │
│ event_date      │       │ ip_address      │
│ event_time      │       │ success         │
│ title           │       │ attempted_at    │
│ description     │       └─────────────────┘
│ location        │
│ fetched_at      │       ┌─────────────────┐
└─────────────────┘       │  site_content   │
                          ├─────────────────┤
                          │ id (PK)         │
                          │ content_key     │
                          │ content_value   │
                          │ updated_by (FK) │
                          │ updated_at      │
                          └─────────────────┘
```

### Table Details

#### `users` - User Accounts
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| username | TEXT UNIQUE | Login username |
| password_hash | TEXT | bcrypt password hash |
| email | TEXT | User email address |
| role | TEXT | 'admin', 'publisher', or 'viewer' |
| created_at | DATETIME | Account creation timestamp |
| last_login | DATETIME | Last successful login |
| is_active | INT | 1=active, 0=deactivated |

#### `meetings` - Monthly Meetings
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| meeting_type | TEXT | 'public' or 'board' |
| meeting_date | DATE | Meeting date |
| meeting_time | TIME | Meeting start time |
| location | TEXT | Venue name |
| location_address | TEXT | Full address |
| description | TEXT | Meeting description |
| meeting_format | TEXT | 'in-person', 'zoom', or 'hybrid' |
| is_active | INT | 1=current, 0=archived |
| updated_by | INT FK | User who last updated |
| updated_at | DATETIME | Last update timestamp |

#### `presentations` - Monthly Speakers
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| month | INT | Month (1-12) |
| year | INT | Year |
| topic | TEXT | Presentation title |
| presenter_name | TEXT | Speaker name |
| presenter_title | TEXT | Speaker credentials/affiliation |
| abstract | TEXT | Presentation summary |
| bio | TEXT | Speaker biography |
| is_hybrid | INT | 1=hybrid format available |
| updated_by | INT FK | User who last updated |
| updated_at | DATETIME | Last update timestamp |

**Unique Constraint**: (month, year)

#### `events` - Club Events
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| event_type | TEXT | 'h2o', 'tesla', 'announcement', 'bbq', 'potluck' |
| event_date | DATE | Event date |
| title | TEXT | Event title |
| description | TEXT | Event details |
| is_visible | INT | 1=show on website |
| sort_order | INT | Display ordering |
| updated_by | INT FK | User who last updated |
| updated_at | DATETIME | Last update timestamp |

**Indexes**: `idx_events_type`, `idx_events_date`

#### `newsletters` - Newsletter Archive
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| year | INT | Publication year |
| month | INT | Publication month (1-12) |
| filename | TEXT | e.g., 'tvsnews0126.pdf' |
| file_path | TEXT | e.g., 'newsletters/2026/tvsnews0126.pdf' |
| file_type | TEXT | 'pdf' or 'html' |
| file_size | INT | File size in bytes |
| uploaded_by | INT FK | User who uploaded |
| uploaded_at | DATETIME | Upload timestamp |
| is_current | INT | 1=featured newsletter |

**Unique Constraint**: (year, month)
**Index**: `idx_newsletters_year_month`

#### `calendar_cache` - Groups.io Events
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| event_id | TEXT UNIQUE | Groups.io event ID |
| event_date | DATE | Event date |
| event_time | TEXT | Event time |
| title | TEXT | Event title |
| description | TEXT | Event description |
| location | TEXT | Event location |
| fetched_at | DATETIME | Last sync timestamp |

**Index**: `idx_calendar_cache_date`

#### `audit_log` - Activity Logging
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| user_id | INT FK | User who performed action |
| action | TEXT | Action type (login, update_meeting, etc.) |
| table_name | TEXT | Table affected |
| record_id | INT | Record ID affected |
| old_value | TEXT | Previous value (JSON) |
| new_value | TEXT | New value (JSON) |
| ip_address | TEXT | Client IP address |
| created_at | DATETIME | Action timestamp |

**Index**: `idx_audit_log_user`

#### `login_attempts` - Rate Limiting
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| username | TEXT | Attempted username |
| ip_address | TEXT | Client IP address |
| success | INT | 1=successful, 0=failed |
| attempted_at | DATETIME | Attempt timestamp |

**Index**: `idx_login_attempts_ip`

---

## Security Architecture

### Authentication Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      Login Request                               │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   CSRF Token Validation                          │
│  - Token from hidden form field                                  │
│  - Compared with session token using hash_equals()               │
└─────────────────────────────────────────────────────────────────┘
                              │
                    Valid?───No──▶ 403 Forbidden
                              │
                             Yes
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Rate Limiting Check                         │
│  - Count attempts in last 15 minutes                            │
│  - By username OR IP address                                    │
│  - Max 5 attempts before lockout                                │
└─────────────────────────────────────────────────────────────────┘
                              │
                   Locked?───Yes──▶ "Too many attempts"
                              │
                              No
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Credential Verification                       │
│  - Query user by username                                       │
│  - password_verify() against bcrypt hash                        │
│  - Check is_active = 1                                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                    Valid?───No──▶ Log failed attempt
                              │          Return error
                             Yes
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Session Creation                             │
│  - session_regenerate_id(true)                                  │
│  - Store user data in $_SESSION                                 │
│  - Update last_login timestamp                                  │
│  - Log successful login to audit_log                            │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Redirect to Dashboard                         │
└─────────────────────────────────────────────────────────────────┘
```

### Security Layers

| Layer | Protection | Implementation |
|-------|------------|----------------|
| Transport | HTTPS only | .htaccess redirect |
| Headers | XSS, Clickjacking, MIME | Security headers in .htaccess |
| Session | Fixation, Hijacking | HttpOnly, Secure, SameSite cookies |
| CSRF | Cross-site request forgery | Token in all forms |
| Authentication | Brute force | Rate limiting (5 attempts/15 min) |
| Passwords | Rainbow tables | bcrypt with cost factor |
| Input | SQL Injection | PDO prepared statements |
| Output | XSS | htmlspecialchars() via e() |
| Files | Directory traversal | Integer validation, allowed paths |
| Audit | Non-repudiation | Complete audit log with IP |

### Password Requirements

- Minimum 12 characters
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one digit (0-9)
- At least one special character (!@#$%^&*)
- Not in common password blacklist

---

## Environment Configuration

### Environment Variables

Set in `.htaccess` for Apache:

```apache
# Database Configuration
SetEnv TVS_DB_HOST "localhost"
SetEnv TVS_DB_NAME "tvs_database"
SetEnv TVS_DB_USER "username"
SetEnv TVS_DB_PASS "password"

# Optional: Base path if not in root
SetEnv TVS_BASE_PATH "/beta"

# API Keys
SetEnv GROUPS_IO_API_KEY "your_api_key"
```

### PHP Constants (config.php)

| Constant | Default | Description |
|----------|---------|-------------|
| `SITE_NAME` | "Tri-Valley Stargazers" | Display name |
| `SITE_URL` | "https://trivalleystargazers.org" | Base URL |
| `SITE_EMAIL` | "info@trivalleystargazers.org" | Contact email |
| `SESSION_TIMEOUT` | 1800 | Session timeout in seconds (30 min) |
| `MAX_LOGIN_ATTEMPTS` | 5 | Before lockout |
| `LOGIN_LOCKOUT_TIME` | 900 | Lockout duration in seconds (15 min) |
| `MAX_UPLOAD_SIZE` | 10485760 | Max upload in bytes (10 MB) |
| `ROLE_ADMIN` | "admin" | Admin role identifier |
| `ROLE_PUBLISHER` | "publisher" | Publisher role identifier |
| `ROLE_VIEWER` | "viewer" | Viewer role identifier |

### Security Headers (.htaccess)

```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'self'"
```

---

## Related Documentation

- [Design System](DESIGN-SYSTEM.md) - CSS architecture and components
- [PHP Reference](PHP-REFERENCE.md) - Backend function documentation
- [Admin Guide](ADMIN-GUIDE.md) - Admin panel usage
- [Contributing](CONTRIBUTING.md) - Development workflow
