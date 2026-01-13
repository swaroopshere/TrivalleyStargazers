# Quick Start Guide

## Initial Setup (One-Time)

### 1. Run Setup Script
```bash
cd admin
php setup.php
```

Or via web browser:
```
http://yoursite.com/admin/setup.php
```

### 2. Change Default Password
**IMPORTANT**: Change the default password immediately!

Via web browser:
```
http://yoursite.com/admin/change_password.php
```

Or edit `config.php` manually and update `ADMIN_PASSWORD_HASH`.

### 3. Install PDF Processing Tool (Choose One)

**ImageMagick** (Recommended):
```bash
# Ubuntu/Debian
sudo apt-get install imagemagick

# macOS  
brew install imagemagick

# Windows: Download from https://imagemagick.org
```

**OR Ghostscript**:
```bash
# Ubuntu/Debian
sudo apt-get install ghostscript

# macOS
brew install ghostscript
```

**OR PHP Imagick Extension**:
```bash
sudo apt-get install php-imagick
# Then restart your web server
```

### 4. Migrate Existing Newsletters
Import your existing newsletters into the database:
```bash
php migrate_existing.php
```

Or via web browser:
```
http://yoursite.com/admin/migrate_existing.php
```

## Using the System

### Upload a Newsletter

1. Go to: `http://yoursite.com/admin/`
2. Login with your credentials
3. Click "Upload New Newsletter"
4. Select year and month
5. Choose PDF file
6. Click "Upload Newsletter"

That's it! The system automatically:
- ✅ Saves PDF to correct folder
- ✅ Generates newscover.jpg from first page
- ✅ Updates newsletter.shtml
- ✅ Updates newsletterlinks.shtml
- ✅ Stores in database

## File Structure

```
admin/
├── config.php          # Configuration (edit to change password)
├── login.php           # Login page
├── index.php           # Admin dashboard
├── upload.php          # Upload interface
├── pdf_processor.php   # PDF processing functions
├── migrate_existing.php # Import existing newsletters
├── setup.php           # Initial setup script
├── change_password.php # Change password utility
└── data/              # Database (auto-created)
    └── newsletters.db

newsletters/
└── YYYY/
    └── tvsnewsMMYY.pdf

images/
└── newscover.jpg      # Auto-generated cover image
```

## Troubleshooting

### Can't Login
- Default username: `admin`
- Default password: `changeme123`
- Change it immediately using `change_password.php`

### Cover Image Not Generated
- Install ImageMagick or Ghostscript (see step 3 above)
- Check file permissions on `images/` directory
- Check server error logs

### Upload Fails
- Check PHP `upload_max_filesize` setting
- Verify `newsletters/` directory is writable
- Check server error logs

### Pages Not Updating
- Ensure `newsletter.shtml` and `newsletterlinks.shtml` are writable
- Check database contains newsletter entries
- Verify `regenerateNewsletterPages()` is being called

## Security

1. ✅ Change default password immediately
2. ✅ Delete `change_password.php` after first use (optional)
3. ✅ Keep PHP and server software updated
4. ✅ Regularly backup `data/newsletters.db`

## Support

For detailed documentation, see `README.md`

