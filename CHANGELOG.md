# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Security

* **CSRF protection is now enforced.** `VerifyCsrfToken` was commented out of
  the http kernel, which left every state changing request, including login,
  page, post, event and comment writes, open to being forged by any third party
  site a logged in user visited. The new
  `app/Http/Middleware/VerifyCsrfToken.php` verifies the session token on every
  non `GET` request and is registered globally again.
* The `XSRF-TOKEN` cookie is now excluded from cookie encryption by
  `app/Http/Middleware/EncryptCookies.php`, and
  `resources/assets/js/cms-csrf.js` echoes it back in the `X-CSRF-TOKEN`
  header on every same origin ajax request, so the comment endpoints keep
  working now that the token is required.
* **`CMS_EVAL` now defaults to `false`.** Page bodies were handed straight to
  `php`'s `eval` out of the box, which gave every account with edit permission
  arbitrary code execution on the web server. Page bodies are content again
  unless an operator opts in explicitly. Existing installs that rely on this
  need `CMS_EVAL=true` in their `.env` file.
* The seeded pages are now rendered once, at install time, so a fresh install
  no longer depends on that feature: the stubs in `app/Seeds` are templates,
  not content, and what lands in the database is finished html. The contact
  form is the one piece that has to be rendered per request for its csrf token
  to be valid, and it is pulled in through a `{contact}` placeholder that
  `PagePresenter::content()` expands.
* **The application refuses to serve requests with a missing or short
  `APP_KEY`**, which protects every cookie and encrypted value in the system.
  Console commands are exempt so `php artisan key:generate` always works.
* **Page slugs are unique.** A page can no longer be created or moved onto a
  slug another live page is using, which previously let a second page shadow
  the homepage and made show, edit and delete operate on an arbitrary row.
* **Comments are bound to their post.** `show`, `update` and `destroy` on
  `blog/posts/{post}/comments/{comment}` return a 404 when the comment belongs
  to a different post, instead of serving it to whoever guessed the url.
* Posting a comment to a post that does not exist returns a 404 rather than
  silently creating an orphaned comment.
* Responses now carry `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: SAMEORIGIN` and
  `Referrer-Policy: strict-origin-when-cross-origin`.
* Session cookies are marked secure whenever `APP_URL` is an https url, and
  `SESSION_SECURE` can now override that. It was previously hard coded.

### Added

* A GitHub Actions workflow that runs the coding standard, the test suite, a
  coverage run, `composer validate` and `composer audit` on every push and
  pull request.
* A `Dockerfile` and a `docker-compose.yml` that bring the application and a
  MySQL server up with one command, and run the test suite in the same image
  with no external services.
* Tests for the middleware, the application and logging service providers, the
  slug uniqueness rules, the comment to post binding, and a regression test
  proving that a page body is never executed.
* `SECURITY.md`, `UPGRADE.md` and this changelog, plus a `composer audit`
  script for local use.
* `APP_LOG_JSON=true` switches every log handler over to monolog's json
  formatter, so each log line is one structured object.

### Changed

* The Travis CI configuration has been removed. It referenced PHP 5.5.9 and
  hhvm, neither of which is installable anywhere today, and the service no
  longer runs for open source projects.
* `tests/Acceptance/PageTest.php` no longer carries ten permanently skipped
  tests. The flows they described are covered, and actually execute, in
  `tests/Http/Controllers/PageControllerTest.php`.

### Fixed

* `EventController::update` assigned the validator to itself twice
  (`$val = $val = ...`), which only worked by accident.

[Unreleased]: https://github.com/PreCogSecurity/CMS/compare/master...HEAD
