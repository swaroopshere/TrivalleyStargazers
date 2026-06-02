# TVS Newsletter Migration Tools

This directory contains utility scripts for managing the TVS website data.

## migrate_newsletters.php

Migrates existing newsletter PDFs from the directory structure into the database.

### Overview

The TVS website stores newsletter metadata in the database while keeping the actual PDF files in the filesystem. This script scans the newsletter directory structure and imports all existing PDFs into the database with proper metadata.

### Directory Structure

The script expects newsletters to be organized as:
```
[base-dir]/newsletters/<year>/tvsnews<month><year>.pdf
```

Examples:
- `newsletters/2023/tvsnews0923.pdf` → September 2023 newsletter
- `newsletters/2024/tvsnews0124.pdf` → January 2024 newsletter

### Usage

```bash
# Dry run (see what would be done without making changes)
php tools/migrate_newsletters.php --dry-run

# Verbose dry run (detailed output)
php tools/migrate_newsletters.php --dry-run --verbose

# Execute the migration
php tools/migrate_newsletters.php

# Verbose execution
php tools/migrate_newsletters.php --verbose

# Specify custom base directory
php tools/migrate_newsletters.php --base-dir /path/to/newsletters

# Short form for base directory
php tools/migrate_newsletters.php -b /path/to/newsletters

# Full example with custom directory and verbose output
php tools/migrate_newsletters.php --base-dir /home/user/archive --dry-run --verbose
```

### Command Line Options

- `--dry-run` - Preview changes without making any database modifications
- `--verbose` - Show detailed processing information for each file
- `--base-dir <path>` or `-b <path>` - Specify custom base directory (default: current directory)
- `--force` - Override certain validation checks (not recommended)

### What the Script Does

1. **Scans Directory Structure**: Finds all year directories under `[base-dir]/newsletters/`
2. **Validates Files**: Checks each PDF file for:
   - File existence and readability
   - Valid PDF MIME type
   - Non-zero file size
   - Valid year (1990-2100) and month (1-12) ranges
3. **Parses Filenames**: Extracts month and year from filename pattern `tvsnewsMMYY.pdf`
4. **Checks Database**: Verifies if newsletter already exists to avoid duplicates
5. **Inserts Records**: Creates database entries with:
   - Year and month
   - Filename and file path
   - File size and type
   - Upload metadata
   - Sets `is_current = 0` initially
6. **Sets Current**: After migration, sets the most recent newsletter as current

### Safety Features

- **Dry Run Mode**: Always test with `--dry-run` first
- **Duplicate Detection**: Won't overwrite existing entries with same year/month
- **File Validation**: Only processes valid PDF files
- **Audit Logging**: Records all migration actions in audit_log table
- **Transaction Safety**: Uses database transactions where appropriate

### Expected Output

```
=== TVS Newsletter Migration Script ===
Starting newsletter migration...

Found 3 year directories

Processing year: 2026
  Found 1 newsletter files
  Processing: tvsnews0126.pdf
    Successfully inserted: 2026-01 (ID: 42)

Processing year: 2025
  Found 11 newsletter files
  Processing: tvsnews0125.pdf
    Successfully inserted: 2025-01 (ID: 43)
  Processing: tvsnews0225.pdf
    Successfully inserted: 2025-02 (ID: 44)
  ...

Processing year: 2024
  Found 4 newsletter files
  Processing: tvsnews0924.pdf
    Successfully inserted: 2024-09 (ID: 54)
  ...

Migration Summary:
  Total files scanned: 16
  Successfully processed: 16
  Already existed: 0
  Failed to process: 0

Setting most recent newsletter as current...
  Set newsletter ID 42 (2026-1) as current

MIGRATION COMPLETE

Final Database Statistics:
  Total newsletters in database: 16
  Current newsletters: 1
  Years covered: 2026, 2025, 2024
```

### Error Handling

The script handles various error conditions:

- **Invalid Directory**: Skips non-numeric year directories
- **Missing Files**: Reports files that can't be accessed
- **Invalid PDFs**: Rejects non-PDF files or empty files
- **Invalid Dates**: Rejects years outside 1990-2100 or invalid months
- **Database Errors**: Logs and continues on database issues
- **Duplicates**: Warns about existing entries with different files

### Prerequisites

- PHP 7.4+ with PDO MySQL extension
- Database connection configured in `.htaccess`
- Read access to newsletter directories
- Write access to database

### Testing

Use the provided test script to create a safe test environment:

```bash
# Create test environment
php tools/test_migration.php

# Test the migration script (output will show test directory path)
php tools/migrate_newsletters.php --base-dir /tmp/tvs_migration_test --dry-run --verbose

# Clean up test files
rm -rf /tmp/tvs_migration_test
```

### Troubleshooting

**"Newsletter directory not found"**
- Ensure you're running from the correct directory
- Check that `newsletters/` exists relative to the script or specified base directory
- Use `--base-dir` to specify the correct path

**"File not accessible"**
- Check file permissions on PDF files
- Ensure the web server can read the files
- Verify the base directory path is correct

**"Database error"**
- Verify database connection settings in `.htaccess`
- Check that the newsletters table exists
- Ensure the database user has INSERT permissions

**"Invalid filename format"**
- Ensure files follow the `tvsnewsMMYY.pdf` pattern
- Check for typos in filenames
- Use `--verbose` to see which files are being processed

### Integration with Existing System

This script integrates seamlessly with the existing newsletter system:

- Uses the same database schema as the admin panel
- Follows the same file path conventions
- Maintains the same audit logging standards
- Sets the current newsletter using the same logic as the admin panel

### Security Considerations

- Script validates all file inputs to prevent directory traversal
- Uses prepared statements for all database operations
- Validates file types and sizes
- Logs all actions for audit trail
- Runs with minimal required privileges

### Performance

The script is optimized for typical newsletter archives:

- Processes files sequentially to avoid memory issues
- Uses efficient database queries
- Minimal file system operations
- Suitable for hundreds of newsletter files

For very large archives (>1000 files), consider running during low-traffic periods.

### Examples

**Migrate from current directory:**
```bash
php tools/migrate_newsletters.php --dry-run
```

**Migrate from remote server archive:**
```bash
php tools/migrate_newsletters.php --base-dir /home/user/archive --verbose
```

**Migrate from mounted network drive:**
```bash
php tools/migrate_newsletters.php -b /mnt/backup/newsletters --dry-run
```

**Migrate with full logging:**
```bash
php tools/migrate_newsletters.php --base-dir /var/www/old-site --verbose --dry-run
```

### Rollback

If you need to undo the migration:

1. The script only adds new records, it doesn't modify existing ones
2. To remove migrated records, delete from the newsletters table where `uploaded_by = 1` and `action = 'migrate_newsletter'` in audit_log
3. Or manually delete specific records by ID

### Notes

- The script sets `uploaded_by = 1` for all migrated records (system user)
- All migrated newsletters start with `is_current = 0`
- The most recent newsletter is automatically set as current after migration
- Existing newsletters in the database are not modified
- The script is idempotent - running it multiple times won't create duplicates