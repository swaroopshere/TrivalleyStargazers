=====================================================
TVS BETA SITE - MYSQL CONVERSION COMPLETE
=====================================================

The website has been converted from SQLite to MySQL.
Follow these steps to deploy:

=====================================================
STEP 1: CREATE MYSQL DATABASE IN IPAGE
=====================================================
1. Log into iPage Control Panel
2. Go to MySQL Databases
3. Create a new database (e.g., "tvs_beta")
4. Create a database user with a strong password
5. Assign the user to the database with ALL PRIVILEGES
6. Note down:
   - Database Host (usually "localhost")
   - Database Name
   - Username
   - Password

=====================================================
STEP 2: UPDATE config.php WITH YOUR CREDENTIALS
=====================================================
Edit: C:\Users\swaro\git\tvs-deploy\includes\config.php

Change these lines (around line 35-39):
   define('DB_HOST', 'localhost');           // Your MySQL host
   define('DB_NAME', 'your_database_name');  // Your database name
   define('DB_USER', 'your_username');       // Your username
   define('DB_PASS', 'your_password');       // Your password

=====================================================
STEP 3: UPLOAD UPDATED FILES VIA FTP
=====================================================
Upload these files/folders from:
   C:\Users\swaro\git\tvs-deploy\

To the server at:
   /wb_trivalleystargazers.org/beta/

Files to upload (replace existing):
   - includes/config.php  (with your DB credentials)
   - includes/db.php
   - includes/auth.php
   - admin/*.php  (all admin PHP files)
   - api/calendar-sync.php
   - setup_mysql.php  (new file for database setup)

=====================================================
STEP 4: RUN DATABASE SETUP
=====================================================
1. Visit: https://beta.trivalleystargazers.org/setup_mysql.php
2. Wait for "DATABASE SETUP COMPLETE!" message
3. Note the admin credentials shown

=====================================================
STEP 5: DELETE SETUP FILE (IMPORTANT!)
=====================================================
Using FTP or File Manager, DELETE:
   /wb_trivalleystargazers.org/beta/setup_mysql.php

This file should NOT remain on the server!

=====================================================
STEP 6: TEST THE SITE
=====================================================
1. Visit: https://beta.trivalleystargazers.org/
2. Test admin login: https://beta.trivalleystargazers.org/admin/
   - Default: admin / admin123
   - CHANGE PASSWORD IMMEDIATELY!

=====================================================
STEP 7: CHANGE ADMIN PASSWORD
=====================================================
After logging in:
1. Go to Users management (if available)
2. Or create reset_password.php with new password
3. Delete reset_password.php after use

=====================================================
FILES MODIFIED FOR MYSQL
=====================================================
- includes/config.php - Added MySQL credentials
- includes/db.php - Changed from SQLite to MySQL PDO
- includes/auth.php - Changed datetime('now') to NOW()
- admin/presentation.php - Changed datetime('now') to NOW()
- admin/newsletter.php - Changed datetime('now') to NOW()
- admin/events.php - Changed datetime('now') to NOW()
- admin/meetings.php - Changed datetime('now') to NOW()
- admin/index.php - Changed date('now') to CURDATE()
- api/calendar-sync.php - Changed date functions to MySQL

=====================================================
TROUBLESHOOTING
=====================================================
If you get database errors:
1. Check credentials in config.php
2. Verify database exists and user has permissions
3. Check error logs in iPage control panel

If you get 500 errors:
1. Check PHP error logs
2. Verify all files were uploaded correctly
3. Check file permissions (755 for folders, 644 for files)

=====================================================
