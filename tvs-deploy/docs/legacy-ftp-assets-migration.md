# Live FTP archive migration — November 15, 2026

The FTP-hosted files are authoritative. The manifest was generated from the live
filesystem, with every included file SHA-256 verified in bounded authenticated
requests. No file contents are embedded in Git or the database.

## Included content

- 6,693 file records: 70 documents, 5,192 pictures, and 1,431 newsletter assets.
- 381 canonical newsletter issues, including 13 previously uncataloged issues:
  July–August 1994 and February–December 1995.
- Newsletter HTML pages, scans, supporting images, and placeholder PDFs are
  cataloged individually; only canonical issue filenames create newsletter rows.
- Document formats include PDF, Word, spreadsheets, and PowerPoint. Picture
  records include originals, thumbnails, and existing site graphics. Importing a
  file does not turn it into a public gallery item.

Scope: root-level document/picture files and the existing public archive folders
`newsletters`, `pdfs`, `images`, `talks`, `ron`, `don_dossa`, `ken`, `barcroft`, and
`gert`. The live `ZoomVideos` folder was checked and contains audio/video only,
which is outside this migration. Hosting settings, mail, hidden folders, duplicate
site backups, and test directories are excluded. `delvallenotify.pdf` is explicitly
excluded at discovery, manifest generation, and validation.

## Database behavior

`legacy_assets` stores original paths, categories, byte counts, source modification
times, SHA-256 digests, and the migration ID. Its primary key is the SHA-256 of the
case-sensitive original path; this handles spaces and historical filenames without
renaming assets. `is_listed` defaults to zero and existing editorial visibility is
preserved on replay. The table supplies archive metadata for future administration;
this change does not add a public document or picture listing.

The importer adds absent newsletter months and refuses conflicting issue paths.
It preserves existing newsletter rows and the current issue flag. Thus October
2026 remains current. Historical documents are kept in `legacy_assets`; the seven
curated `official_documents` entries are preserved. Membership, payment, contacts,
accounts, and calendar records are outside this migration.

The 3.1 GB of existing files stay at their current FTP paths. The current public
website remains live. This migration is metadata import, not a website cutover or
an independent binary archive backup. The new beta archive resolver can access
the preserved primary newsletter tree; older asset URLs continue to use the
existing live host. A future cutover must retain those directories.

## Reproducible artifacts

- `data/legacy-ftp-assets-20261115.json`: immutable verified file and issue manifest.
- `tools/build_legacy_asset_manifest.py`: turns an externally collected live FTP
  inventory into that manifest; it refuses duplicate issues and unhashed files.
- `includes/legacy_asset_migration.php`: reusable validator, snapshotter, and
  transaction-bound importer.
- `tools/migrate_legacy_assets.php`: CLI-only plan/apply entry point. It uses the
  configured database credentials and never accepts passwords as command arguments.

Run the CLI from a private shell, configured separately for each target database:

```sh
php tools/migrate_legacy_assets.php \
  --manifest=data/legacy-ftp-assets-20261115.json \
  --root=/absolute/path/to/the/existing/live/site

php tools/migrate_legacy_assets.php \
  --manifest=data/legacy-ftp-assets-20261115.json \
  --root=/absolute/path/to/the/existing/live/site \
  --apply --expected=FINGERPRINT_FROM_PLAN \
  --backup=/private/non-web-directory/archive-before.json
```

Plan performs no database writes. Apply requires a new backup file and matching
snapshot fingerprint. MySQL schema creation occurs before the data transaction
because MySQL DDL implicitly commits. The data import rolls back on conflicts;
if a first run fails, an empty catalog table may remain. Inspect the state and
produce a fresh plan before retrying.

For this shared host, bounded SHA verification was followed immediately by a
size/mtime recheck at apply, using an exact manifest hash. Temporary transport
endpoints required POST, a random secret header, the beta hostname, and an expiry;
they were removed after use. Credentials stayed in memory or the local private
configuration, and backups were written outside Git and the public document root.
`/data` and `/includes` HTTP requests remain denied.

## Verification and recovery

After each import, replay within a rolled-back transaction must report zero
inserts. Compare complete `legacy_assets` and `newsletters` snapshots between the
two databases, including every metadata value. Run the regression test with:

```sh
php tests/membership/legacy-assets.test.php
```

Before restoring a backup, compare the current snapshot to the saved post-import
fingerprint. Stop if later edits exist. The importer only inserts, so restoration
removes the exact rows added by this migration and retains all original rows.
If the catalog did not exist before import, it can be removed after confirming no
subsequent records or visibility edits exist. Restore only the affected metadata;
do not replace the entire database or remove the hosted files.
