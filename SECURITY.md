# Security Policy

## Reporting A Vulnerability

Please report security issues privately to the maintainers rather than opening
a public issue. Include the affected version or commit, the steps needed to
reproduce, and the impact you believe it has. You should get an
acknowledgement within a few working days.

## What Is Covered

This project is a CMS: it renders user supplied content, it manages
accounts, and it runs on a framework line that is no longer maintained upstream
(see [UPGRADE.md](UPGRADE.md)). Any of the following is in scope:

* authentication, authorisation and account takeover
* cross site request forgery and cross site scripting
* the page `eval` feature described below
* dependency vulnerabilities reachable from a request
* the deployment defaults shipped in `.env.example`, `config/` and `docker-compose.yml`

## The Defaults We Ship

These are on unless you turn them off, and turning them off moves the risk
onto you:

* **CSRF protection is enforced.** `app/Http/Middleware/VerifyCsrfToken.php`
  rejects any non `GET` request that does not carry the session token, either
  as a `_token` field or as an `X-CSRF-TOKEN` header. The `XSRF-TOKEN` cookie is
  deliberately left unencrypted and readable by javascript so that the ajax
  endpoints can echo it back; if you write new ajax calls, `resources/assets/js/cms-csrf.js`
  attaches the header for you through a global `$.ajaxSetup`.
* **Page bodies are never executed.** `CMS_EVAL` defaults to `false`, so a page
  body is treated as content, not as PHP. The only thing expanded in a body is
  the `{contact}` placeholder, which renders the contact form at request time
  so that its csrf token stays valid. Setting `CMS_EVAL=true` means any
  account with edit permission has arbitrary code execution on your server,
  which includes reading your `.env` and writing files. Do not enable it on a
  multi tenant install.
* **Slugs are unique.** Creating a page with a slug that a live page already
  uses is rejected, so a page cannot shadow another one, and cannot take over
  the homepage. Note that soft deleted pages release their slug.
* **Security headers are set** on every response by
  `app/Http/Middleware/SecurityHeaders.php`: `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: SAMEORIGIN`, and `Referrer-Policy:
  strict-origin-when-cross-origin`. There is no content security policy,
  because pages are allowed to carry their own css and js. If you do not need
  that, add a `Content-Security-Policy` header and drop the per page css and js
  fields.
* **Session cookies are marked secure** whenever `APP_URL` is an `https` url.
  Override with `SESSION_SECURE` if you terminate tls somewhere unusual, and
  set it to `false` only for local http development.
* **The application refuses to serve traffic with a weak `APP_KEY`.** Run
  `php artisan key:generate` and keep the result out of version control. The
  key protects every cookie and every encrypted value in the application.
* **Commenting is rate limited** per the throttle settings in
  `config/throttle.php`.

## Deployment Checklist

1. `APP_DEBUG=false` and `APP_ENV=production`.
2. A generated `APP_KEY`, not the placeholder.
3. `CMS_EVAL=false` unless you fully trust every edit capable account.
4. `APP_LOG_JSON=true`, so logs are one structured object per line, and ship
   them somewhere you will actually read them.
5. `SESSION_SECURE=true` behind https.
6. Serve `public/` only. The application root contains `server.php`,
   `composer.json` and, on a real install, your `.env` file.
7. Give the database user only the privileges the application needs, and keep
   it on a separate host or network segment where you can.
8. Change the seeded administrator password, and give out the `edit`
   permission only to people who need it: it allows arbitrary html, css and js
   to be published to your visitors.
