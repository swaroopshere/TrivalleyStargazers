# TVS PHP Site — Next Session TODO

## Rollout blockers

- [ ] **Membership form backend** — `membership.php` form has `action=""` and no `$_POST` handler.
      Decision: either add PHP backend + `members` DB table, or replace the submit buttons
      with a "Download PDF form" notice and disable digital submission.

- [ ] **Verify PayPal JS wired up** — `pay.php` calls `callPayPal()`, `membership.php` calls
      `setupForm()`, `donation.php` calls `doPayPalDonation()`. All three live in `tvs.js`.
      Confirm `tvs.js` is included in `includes/templates/header.php` and the functions work
      on the beta site before going live.

- [ ] **Deploy `images/` directory** — `tvs-deploy/` references `images/banners/`, `images/fire/`,
      etc., but those directories live in the repo root, not in `tvs-deploy/`. Copy/upload the
      full `images/` tree to iPage alongside the PHP files.

- [ ] **Deploy `newsletters/` PDFs** — same issue; the 20 PDF files live in the repo root
      `newsletters/` directory and must be uploaded to iPage before the newsletter DB records
      resolve to real files.

- [ ] **Add `GROUPS_IO_API_KEY` to `.htaccess`** — calendar sync uses `getenv('GROUPS_IO_API_KEY')`.
      The key exists in the root `.env` (for the old Node.js script) but is not in
      `tvs-deploy/.htaccess`. Add: `SetEnv GROUPS_IO_API_KEY "..."` before deploying.

- [ ] **Initialize production database** — on iPage phpMyAdmin, run in order:
      1. `data/schema_mysql.sql`
      2. `data/seed.sql`
      Or use the admin migrations panel (migration 003) after schema.sql is imported.

- [ ] **Set real admin password** — run `admin/setup.php` once after first deploy; delete or
      restrict access to it afterwards.

## Code fixes

- [ ] **`getCurrentPublicMeeting()` shows stale meetings** — `db.php:124`.
      Add `AND meeting_date >= CURDATE()` to the query so a forgotten past meeting
      doesn't stay on the homepage forever.

- [ ] **`contacts.php` is hardcoded** — officer names/emails are static HTML; `admin/contacts.php`
      exists but doesn't drive the public page. Either wire the public page to the DB or
      document that contact updates require a direct file edit.

- [ ] **Production `error_reporting`** — `config.php:16` has `error_reporting(E_ALL)`.
      Change to `E_ALL & ~E_NOTICE & ~E_DEPRECATED` (or lower) for production.

- [ ] **Delete `data/tvs.db`** — leftover SQLite file; iPage doesn't support SQLite, and
      `.htaccess` blocks direct web access, but it shouldn't be in the deployment package.

- [ ] **Add custom 404 page** — `.htaccess` has no `ErrorDocument 404` directive.
      Add a simple `404.php` and wire it up.

## Post-rollout / nice to have

- [ ] **Pre-2024 newsletter archive** — only 20 PDFs (Sep 2024–May 2026) are in the repo.
      Locate older newsletter PDFs and add them via the admin upload panel.

- [ ] **H2O and Potluck events** — both were past-dated in the static site and were not seeded.
      Add the next scheduled H2O Open House and Winter Potluck via admin → Events once dates
      are known.

- [ ] **CAPTCHA on membership form** — once backend processing is added, add reCAPTCHA or
      honeypot to prevent spam submissions.
