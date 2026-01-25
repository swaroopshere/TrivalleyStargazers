# Admin Panel Guide

Complete guide for using the TVS Beta website admin panel.

## Table of Contents

- [Getting Started](#getting-started)
- [Dashboard Overview](#dashboard-overview)
- [User Roles and Permissions](#user-roles-and-permissions)
- [Managing Meetings](#managing-meetings)
- [Managing Presentations](#managing-presentations)
- [Managing Events](#managing-events)
- [Managing Newsletters](#managing-newsletters)
- [Managing Users](#managing-users)
- [Calendar Sync](#calendar-sync)
- [Audit Logging](#audit-logging)

---

## Getting Started

### Accessing the Admin Panel

1. Navigate to: `https://your-site.com/admin/login.php`
2. Enter your username and password
3. Click "Log In"

### First-Time Login

If this is a fresh installation:
- Default username: `admin`
- Default password: `ChangeMe123!`

**Important:** Change the default password immediately after first login.

### Session Timeout

For security, sessions expire after 30 minutes of inactivity. You will be redirected to the login page automatically.

### Rate Limiting

After 5 failed login attempts, your account will be locked for 15 minutes. This applies to both username and IP address.

---

## Dashboard Overview

The dashboard (`admin/index.php`) provides a quick overview of site status.

### Statistics Cards

| Card | Description |
|------|-------------|
| Total Newsletters | Count of all newsletters in the archive |
| Active Events | Count of currently visible events |
| Calendar Events | Upcoming events from Groups.io sync |
| Active Users | Count of active admin accounts |

### Current Information Panels

- **Public Meeting**: Next scheduled public meeting with date, time, location, and format
- **Presentation**: Current month's speaker topic and presenter name
- **Newsletter**: Current/featured newsletter month and year
- **Board Meeting**: Next board meeting details

### Activity Log

Shows the 10 most recent admin actions including:
- User who performed the action
- Action type (login, update, create, delete)
- Table affected
- Timestamp

### Quick Actions

Links to commonly used admin functions:
- Edit Meetings
- Edit Presentation
- Manage Events
- Manage Newsletters

---

## User Roles and Permissions

### Role Hierarchy

| Role | Access Level |
|------|--------------|
| **admin** | Full access to all features including user management |
| **publisher** | Can edit meetings, events, presentations, and newsletters |
| **viewer** | Read-only access to admin dashboard (not currently used) |

### Permission Matrix

| Feature | Admin | Publisher | Viewer |
|---------|-------|-----------|--------|
| Dashboard | Yes | Yes | Yes |
| Edit Meetings | Yes | Yes | No |
| Edit Presentations | Yes | Yes | No |
| Manage Events | Yes | Yes | No |
| Upload Newsletters | Yes | Yes | No |
| Manage Users | Yes | No | No |
| View Audit Log | Yes | Yes | No |
| Sync Calendar | Yes | Yes | No |

---

## Managing Meetings

Navigate to: **Admin > Meetings** (`admin/meetings.php`)

### Meeting Types

The system manages two types of meetings:

1. **Public Meeting**: Monthly general meeting open to all members
2. **Board Meeting**: Monthly board of directors meeting

### Editing Meetings

The meetings page shows side-by-side forms for both meeting types.

#### Fields

| Field | Description | Example |
|-------|-------------|---------|
| Date | Meeting date | 2026-01-17 |
| Time | Start time | 7:30 PM |
| Location | Venue name | Unitarian Universalist Church |
| Address | Full address | 1893 N. Vasco Road, Livermore, CA |
| Description | Meeting notes | Annual meeting with elections |
| Format | Meeting type | In-Person, Zoom Only, or Hybrid |

#### Meeting Formats

| Format | Description |
|--------|-------------|
| In-Person | Physical attendance only |
| Zoom Only | Virtual meeting via Zoom |
| Hybrid | Both in-person and Zoom options |

### Saving Changes

1. Update the fields as needed
2. Click "Save Changes" for the respective meeting
3. Changes are immediately visible on the public site
4. All changes are logged in the audit log

---

## Managing Presentations

Navigate to: **Admin > Presentation** (`admin/presentation.php`)

### Overview

Each monthly public meeting features a presentation by a guest speaker. This page manages the speaker information displayed on the homepage.

### Selecting a Month

1. Use the month/year dropdown at the top
2. Click "Edit" to load that month's presentation
3. The form will populate with existing data (if any)

### Fields

| Field | Description | Example |
|-------|-------------|---------|
| Topic | Presentation title | "Black Holes: From Theory to Image" |
| Presenter Name | Speaker's name | Dr. Jane Smith |
| Presenter Title | Credentials/affiliation | Professor, UC Berkeley |
| Abstract | Presentation summary | Detailed description of the talk |
| Biography | Speaker bio | Brief background of the speaker |
| Is Hybrid | Zoom option available | Checked if hybrid |

### Recent Presentations

The sidebar shows the last 24 presentations for quick navigation. Click any entry to edit.

### Tips

- Keep abstracts concise (2-3 paragraphs)
- Include speaker credentials to build interest
- Update the presentation early in the month

---

## Managing Events

Navigate to: **Admin > Events** (`admin/events.php`)

### Event Types

| Type | Icon | Description |
|------|------|-------------|
| H2O Open House | Telescope | Dark sky observing at H2O site |
| Tesla Vineyard | Star | Partner events at Tesla Vineyard |
| Announcement | Bell | General club announcements |
| BBQ | Grill | Summer BBQ events |
| Potluck | Food | Potluck dinner events |

### Adding a New Event

1. Click "Add New Event"
2. Fill in the form:
   - **Type**: Select event category
   - **Date**: Event date
   - **Title**: Event title
   - **Description**: Event details
   - **Visible**: Check to show on public site
3. Click "Save Event"

### Editing Events

1. Find the event in the list
2. Click "Edit"
3. Modify fields as needed
4. Click "Save Changes"

### Visibility Toggle

Events can be hidden without deleting them:
- **Visible**: Shows on public site
- **Hidden**: Not shown but preserved in database

This is useful for:
- Cancelled events
- Past events to keep for records
- Events not yet ready to announce

### Deleting Events

1. Click "Delete" on the event row
2. Confirm the deletion
3. This action cannot be undone

---

## Managing Newsletters

Navigate to: **Admin > Newsletter** (`admin/newsletter.php`)

### Newsletter Archive

The system maintains a complete archive of newsletters from 1996 to present.

### Uploading a Newsletter

1. Select the **Month** (1-12)
2. Select the **Year** (1990-2100)
3. Click "Choose File" and select the PDF
4. Optionally check "Set as Current"
5. Click "Upload Newsletter"

#### Upload Requirements

| Requirement | Value |
|-------------|-------|
| File Type | PDF only |
| Max Size | 10 MB |
| Naming | Automatic (tvsnewsMMYY.pdf) |

### Setting Current Newsletter

The "current" newsletter is featured prominently on the homepage.

To change the current newsletter:
1. Find the newsletter in the archive list
2. Click "Set Current"
3. Only one newsletter can be current at a time

### Viewing the Archive

Newsletters are organized by year. Use the year dropdown to browse different years.

Each entry shows:
- Month and year
- Filename
- File size
- Upload date
- Current status

### Deleting Newsletters

1. Click "Delete" on the newsletter row
2. Confirm the deletion
3. The PDF file is removed from the server

**Note:** This cannot be undone. Consider keeping backups.

---

## Managing Users

Navigate to: **Admin > Users** (`admin/users.php`)

**Note:** This feature requires admin role.

### User List

Shows all user accounts with:
- Username
- Email
- Role
- Created date
- Last login
- Status (Active/Inactive)

### Creating a New User

1. Click "Add New User"
2. Enter required information:
   - **Username**: Unique, minimum 3 characters
   - **Email**: Valid email address
   - **Password**: Must meet security requirements
   - **Role**: Select admin, publisher, or viewer
3. Click "Create User"

#### Password Requirements

New passwords must have:
- Minimum 12 characters
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one number (0-9)
- At least one special character (!@#$%^&*)
- Cannot be a common password

### Editing Users

1. Click "Edit" on the user row
2. Modify email or role
3. Click "Save Changes"

**Note:** You cannot change your own role.

### Changing Passwords

1. Click "Change Password" on the user row
2. Enter the new password
3. Password must meet security requirements
4. Click "Update Password"

### Deactivating Users

1. Click "Deactivate" on the user row
2. Confirm the action
3. User can no longer log in

**Note:** You cannot deactivate your own account.

### Reactivating Users

1. Find the inactive user
2. Click "Activate"
3. User can log in again

---

## Calendar Sync

Navigate to: **Admin > Sync Calendar** (`admin/sync-calendar.php`)

### Overview

The calendar sync feature imports events from the club's Groups.io calendar into the website's event cache.

### How It Works

1. The system connects to Groups.io API
2. Fetches upcoming calendar events
3. Stores them in the `calendar_cache` table
4. Events display on the public "All Events" page

### Manual Sync

1. Go to Sync Calendar page
2. Click "Sync Now"
3. Wait for confirmation message
4. View sync results (success/failures)

### Automatic Sync

For automated sync, set up a cron job:

```bash
# Sync calendar every 6 hours
0 */6 * * * curl -s https://your-site.com/api/calendar-sync.php?key=API_KEY
```

### Troubleshooting

| Issue | Solution |
|-------|----------|
| API connection failed | Check GROUPS_IO_API_KEY in .htaccess |
| No events imported | Verify group calendar has future events |
| Stale data | Run manual sync or check cron job |

---

## Audit Logging

### What Gets Logged

Every significant admin action is recorded:

| Action | Logged Data |
|--------|-------------|
| login | Username, IP address, success/failure |
| logout | Username, IP address |
| create_* | New record data |
| update_* | Old and new values |
| delete_* | Deleted record data |
| toggle_* | Before/after state |

### Viewing the Audit Log

The dashboard shows the 10 most recent actions. Full audit history is stored in the database `audit_log` table.

### Log Entry Fields

| Field | Description |
|-------|-------------|
| User | Who performed the action |
| Action | Type of action taken |
| Table | Database table affected |
| Record ID | Specific record modified |
| Old Value | Previous state (JSON) |
| New Value | New state (JSON) |
| IP Address | Client IP address |
| Timestamp | When action occurred |

### Security Value

The audit log helps:
- Track who made changes
- Identify unauthorized access attempts
- Roll back problematic changes
- Maintain accountability

---

## Best Practices

### General

1. **Log out when done** - Don't leave sessions open
2. **Use strong passwords** - Follow the requirements
3. **Update regularly** - Keep meeting and event info current
4. **Check the dashboard** - Monitor for unusual activity

### Content Management

1. **Proofread before saving** - Changes go live immediately
2. **Use the preview** - View public pages after updates
3. **Keep backups** - Especially for newsletters
4. **Document changes** - Note why changes were made

### Security

1. **Never share credentials** - Each user should have their own account
2. **Report suspicious activity** - Contact the webmaster
3. **Don't ignore lockouts** - May indicate attack attempts
4. **Change passwords periodically** - Every 6-12 months

---

## Related Documentation

- [Architecture](ARCHITECTURE.md) - System design and database schema
- [Design System](DESIGN-SYSTEM.md) - CSS and styling
- [PHP Reference](PHP-REFERENCE.md) - Backend code documentation
- [Contributing](CONTRIBUTING.md) - Development workflow
