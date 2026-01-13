# Newsletter Admin System

Automated newsletter publishing system for Tri-Valley Stargazers website.

## Features

- **Secure Login System**: Password-protected admin access
- **File Upload**: Easy upload interface for newsletter PDFs
- **Automatic Cover Generation**: Extracts first page of PDF as newscover.jpg
- **Dynamic Page Generation**: Automatically updates newsletter.shtml and newsletterlinks.shtml
- **Database Storage**: Tracks all newsletters with metadata

## Setup Instructions

### 1. Database Setup

The system uses SQLite by default (no server setup needed). The database will be created automatically in `data/newsletters.db` on first use.

If you prefer MySQL, edit `config.php`:
- Set `USE_SQLITE` to `false`
- Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` with your MySQL credentials

### 2. Configure Admin Credentials

Edit `config.php` and change:
```php
define('ADMIN_USERNAME', 'your_username');
define('ADMIN_PASSWORD_HASH', password_hash('your_secure_password', PASSWORD_DEFAULT));
```

**Important**: Change the default password immediately!

### 3. PDF Processing Setup

The system tries multiple methods to extract the first page of PDFs:

**Option A: ImageMagick (Recommended)**
```bash
# Ubuntu/Debian
sudo apt-get install imagemagick

# macOS
brew install imagemagick

# Windows
# Download from https://imagemagick.org/script/download.php
```

**Option B: Ghostscript**
```bash
# Ubuntu/Debian
sudo apt-get install ghostscript

# macOS
brew install ghostscript

# Windows
# Download from https://www.ghostscript.com/download/gsdnld.html
```

**Option C: PHP Imagick Extension**
```bash
# Ubuntu/Debian
sudo apt-get install php-imagick

# macOS
pecl install imagick

# Windows
# Enable imagick extension in php.ini
```

**Option D: Poppler (pdftoppm)**
```bash
# Ubuntu/Debian
sudo apt-get install poppler-utils

# macOS
brew install poppler
```

The system will automatically try all available methods.

### 4. File Permissions

Ensure the following directories are writable by the web server:
```bash
chmod 755 admin/
chmod 755 newsletters/
chmod 755 images/
mkdir -p data
chmod 755 data/
```

### 5. Migrate Existing Newsletters

Run the migration script to import existing newsletters into the database:
```bash
php admin/migrate_existing.php
```

Or access via web browser:
```
http://yoursite.com/admin/migrate_existing.php
```

### 6. Access the Admin Panel

Navigate to:
```
http://yoursite.com/admin/
```

Login with your configured credentials.

## Usage

### Uploading a Newsletter

1. Log in to the admin panel
2. Click "Upload New Newsletter"
3. Select the year and month
4. Choose the PDF file
5. Click "Upload Newsletter"

The system will automatically:
- Save the PDF to `newsletters/YYYY/tvsnewsMMYY.pdf`
- Extract the first page as `images/newscover.jpg`
- Update `newsletter.shtml` with the latest newsletter link
- Update `newsletterlinks.shtml` with the new entry
- Store metadata in the database

### File Structure

After upload, files are organized as:
```
newsletters/
  └── YYYY/
      └── tvsnewsMMYY.pdf

images/
  └── newscover.jpg (automatically generated)

admin/
  ├── config.php (configuration)
  ├── auth.php (authentication)
  ├── login.php (login page)
  ├── index.php (dashboard)
  ├── upload.php (upload interface)
  ├── pdf_processor.php (PDF processing)
  └── data/
      └── newsletters.db (SQLite database)
```

## Troubleshooting

### Cover Image Not Generated

1. Check that ImageMagick, Ghostscript, or Imagick is installed
2. Verify file permissions on `images/` directory
3. Check server error logs for details
4. Try manually running the conversion command:
   ```bash
   convert newsletters/2025/tvsnews1125.pdf[0] -quality 90 images/newscover.jpg
   ```

### Database Errors

1. Ensure `data/` directory exists and is writable
2. Check file permissions: `chmod 755 data/`
3. For MySQL, verify database credentials in `config.php`

### Upload Fails

1. Check PHP `upload_max_filesize` and `post_max_size` settings
2. Verify `newsletters/` directory is writable
3. Check server error logs

### Pages Not Updating

1. Ensure `newsletter.shtml` and `newsletterlinks.shtml` are writable
2. Check that `regenerateNewsletterPages()` is being called after upload
3. Verify database contains newsletter entries

## Security Notes

- Change default admin password immediately
- Consider adding `.htaccess` authentication for extra security
- Keep PHP and server software updated
- Regularly backup the database file (`data/newsletters.db`)

## Support

For issues or questions, contact the webmaster.

