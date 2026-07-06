# Launch Runbook — austinschuetz.com on Namecheap cPanel

The other sites on this account (crossfit-sdg.com, attune-collective.com,
groundcreationscolorado.com) and all email are untouched by every step
below — we only work inside austinschuetz.com's document root. No DNS
changes are made at any point.

## 0. Pre-flight (in cPanel — 10 minutes)
- [ ] **Domains**: note austinschuetz.com's document root (likely
      `/home/<user>/public_html`). Write it into `.cpanel.yml` and the two
      scripts in `scripts/` (search for `TODO`).
- [ ] **Does the cPanel have "Git Version Control" and "Terminal" icons?**
      Yes → primary deploy path. No → WinSCP fallback
      (`scripts/deploy.winscp.txt`); SSH access can also be requested via
      Namecheap live chat.
- [ ] **MultiPHP Manager**: set austinschuetz.com to the newest PHP 8.x.
      In MultiPHP INI editor / PHP Selector confirm extensions `gd` and
      `fileinfo` are enabled.
- [ ] **Email**: confirm `contact@austinschuetz.com` mailbox exists
      (create if not) and check **Email Deliverability** shows valid
      SPF/DKIM for the domain.
- [ ] While in there: the sphera./rivo./contelligent. demo subdomains are
      serving bare directory listings — likely the same broken WP install.
      Optional cleanup at step 2.

## 1. Archive the old WordPress site
- [ ] File Manager (or SSH): create `~/old-site/` **outside** the docroot,
      move ALL current docroot contents into it.
- [ ] phpMyAdmin: export the old WP database (gzip), download it AND the
      `old-site` folder to local storage. Keep the DB dormant — don't drop.

## 2. Deploy the code
**Primary (cPanel Git):** Git Version Control → Create → clone this repo
(push it to GitHub first, or use "upload"), deploy branch `main`. The
`.cpanel.yml` copies code only.
**Fallback (WinSCP):** `winscp.com /ini=nul /script=scripts\deploy.winscp.txt`
after filling in HOST/USER.

## 3. Seed server-owned content (FIRST DEPLOY ONLY)
Upload once via File Manager/WinSCP into the docroot:
- [ ] `content/` (whole folder from local)
- [ ] `media/uploads/` (whole folder)
- [ ] `storage/` skeleton: create `storage/auth`, `storage/versions`,
      `storage/ratelimit`, `storage/logs` + copy the deny `.htaccess`
      files from local `storage/.htaccess` etc.
- [ ] Create the admin credential on the server (Terminal or cPanel cron
      one-shot): `php scripts/make-admin.php <user> '<16+ char passphrase>'`
      — use a NEW passphrase, not the local dev one.

## 4. SSL — fix the expired cert
The cert lapsed because AutoSSL's validation file under `/.well-known/`
was hitting the dead WP's 500s. With the new docroot live over HTTP:
- [ ] cPanel → SSL/TLS Status → **Run AutoSSL** for austinschuetz.com
      (+ www). Should issue within minutes.
- [ ] THEN edit `.htaccess` in the docroot: uncomment the force-HTTPS +
      www→apex block.
- [ ] If AutoSSL still fails: check for stale CAA DNS records and any
      Namecheap-level forced redirects.

## 5. Smoke test
- [ ] `https://austinschuetz.com` green lock; `https://www.` 301s to apex.
- [ ] Every nav page + `/style-guide` + two case studies load.
- [ ] `/sitemap.xml` valid; a junk URL returns the 404 page.
- [ ] `/admin/` login works with the NEW credential; edit + save a draft;
      confirm a version snapshot appears in `storage/versions/`.
- [ ] Contact form: real submission from a phone (off wifi), confirm it
      lands in the `contact@` inbox (check spam folder from Gmail too).
- [ ] Upload an image via the admin media screen; confirm it serves.
- [ ] Request `/media/uploads/<that-image-name>.php` → must be 403/404
      (LiteSpeed no-PHP rule verification).

## 6. Ongoing
- [ ] cPanel cron, weekly:
      `tar -czf ~/backups/content-$(date +\%F).tar.gz -C ~/public_html content media/uploads storage/versions && ls -t ~/backups/content-*.tar.gz | tail -n +9 | xargs rm -f`
- [ ] Monthly: run `scripts/pull-content.ps1` locally for an off-server copy.
- [ ] Content edits happen in the live `/admin` — the server is the source
      of truth for `content/` + `media/uploads/`. Code changes deploy via
      step 2 and never touch those directories.
