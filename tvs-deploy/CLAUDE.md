# TVS Project Instructions

## Directory Structure

- **`C:\Users\swaro\git\TrivalleyStargazers`** = PRODUCTION site (DO NOT MODIFY)
- **`C:\Users\swaro\git\tvs-deploy`** = BETA/development site (make all changes here)

## Deployment

- FTP uploads go from `tvs-deploy` to iPage hosting
- Beta site URL: https://beta.trivalleystargazers.org/

## Important Notes

- All new development and changes should be made in `tvs-deploy`
- Never modify files in the TrivalleyStargazers directory

## Server Environment (iPage)

- **Database: MySQL** (NOT SQLite - iPage does not support SQLite)
- Database credentials are set via environment variables in .htaccess
- PHP version: Check iPage control panel for current version
