# TVS Project Instructions

## Directory Structure

- **`TrivalleyStargazers/`** = Root repository (production files on `main` branch)
- **`TrivalleyStargazers/tvs-deploy/`** = Beta site files (on `beta-ui-refresh` branch)

## Git Branches

- **`main`** = Production website (DO NOT MODIFY directly)
- **`beta-ui-refresh`** = Beta site with new UI (make changes here)

## Deployment

- FTP uploads go from `tvs-deploy/` directory to iPage hosting
- Beta site URL: https://beta.trivalleystargazers.org/

## Server Environment (iPage)

- **Database: MySQL** (NOT SQLite - iPage does not support SQLite)
- Database credentials are set via environment variables in .htaccess
- PHP version: Check iPage control panel for current version

## Important Notes

- All beta development should be done in the `tvs-deploy/` subdirectory
- Work on the `beta-ui-refresh` branch for beta changes
- Never modify production files on `main` branch without approval
