# Production Del Valle update

The approved public guide is served at `pdfs/delvalle.pdf` through the existing `delvalle.shtml` page. Its three internal section links and external links are preserved. The old public agreement PDF is replaced at its existing URL to remove outdated notification contacts. The two legacy Del Valle pages forward readers to the current page and no longer contain notification instructions.

The approved notification procedure is stored unchanged at `pdfs/delvallenotify.pdf`. Share `https://www.trivalleystargazers.org/pdfs/delvallenotify.pdf` only through permit-holder communications. Do not add this URL to public pages, navigation, sitemap, or the public guide. This is unlisted access as requested; it has no password protection.

## Deployment

1. Upload `pdfs/.htaccess` first, preserving any server-only settings if added since this PR was prepared. No existing `/pdfs/.htaccess` was found during preparation. It requires Apache `mod_headers` with the `FileInfo` override, plus permission to use `Options -Indexes`. Do not wrap the header in an optional module check: the notification PDF must not silently be served without its search exclusion.
2. Upload `pdfs/delvallenotify.pdf` and replace `pdfs/delvalle.pdf` with the approved public guide.
3. Upload `delvalle.old.shtml`, `delvalle_old.shtml`, and `delvalle.shtml`.
4. Verify `GET /pdfs/delvallenotify.pdf` succeeds with `Content-Type: application/pdf` and `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet` before sharing its URL. Check `/pdfs/` does not display a directory listing. Confirm the public guide still has no `noindex` header, and test its three section links in a PDF reader. Check both legacy pages lead to the new public page.
5. Confirm the old notification contacts are absent from the public page, public PDF, and legacy pages. Request a refresh/removal of stale search results from the site search provider as needed; an already cached copy does not disappear immediately.

Do not block the notification PDF in `robots.txt`: crawlers need access to read its `noindex` response header. The HTTP header is the search exclusion mechanism for this non-HTML resource.

For future updates, replace the notification PDF at the same filename without changing or removing `pdfs/.htaccess`. Recheck the response header after deployment.

## Validation performed before publication

- Both installed PDFs match the supplied approved documents by SHA-256.
- Visually inspected all five PDF pages.
- Confirmed all three named section destinations resolve to pages 1, 2, and 4, and external link annotations are preserved.
- Verified public page asset links resolve and neither the public guide nor the public/legacy HTML contains the notification URL or notification mailbox.
- `git diff --check` passed.

Apache response-header and directory-listing checks require the target server; they have not been verified by a local Apache runtime. Production has not been modified by this PR.
