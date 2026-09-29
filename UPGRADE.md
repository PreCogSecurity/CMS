# Upgrading

## Where We Are

| Package | Constraint | Notes |
| --- | --- | --- |
| `laravel/framework` | `~5.1.10` | the 5.1 line went end of life in 2019 and no longer receives security fixes |
| `phpunit/phpunit` | `^4.7.6` | last released in 2015, does not know about php 7.1+ syntax |
| `squizlabs/php_codesniffer` | `^2.9` | 3.x is the current major, and 2.x does not understand php 8 syntax |
| `mockery/mockery` | `^0.9.4` | superseded by 1.x |
| `cartalyst/sentry` | `v3.0.1` | abandoned upstream, we pin a community fork through the `repositories` entry |
| `graham-campbell/*` | various | maintained alongside this project |
| `gulp`, `laravel-elixir` | `~3.9`, `~3.1.1` | need a node 8 or 10 toolchain |

`composer.lock` and `package-lock.json` are both committed, so a plain
`composer install` and `npm ci` are reproducible. The lock file was generated
by composer 1, so composer 2 prints a staleness warning on install; that is
harmless, it installs the locked versions either way.

## Why Nothing Was Bumped In This Pass

Every one of these is a major version bump, and several of them are framework
internals: laravel 5.1 to 5.8 moves the cache, session, queue, filesystem and
logging configuration into dedicated config files, changes the `Kernel`
middleware contract, drops the `$this->beforeFilter` and
`$this->setPermissions` helpers this project uses, and requires rewriting the
user, group, throttle and revision migrations. Doing that in the same change as
a security fix would have made both impossible to review and impossible to
revert, so it gets its own branch, its own pull request, and a green test suite
at every step.

What we have done instead is make the state visible and the gate automatic:
`composer audit` runs on every build in CI (as an informational job, because
the 5.1 line has known advisories), and Dependabot opens weekly pull requests
for both ecosystems.

## Recommended Order

1. **Tooling first, while nothing else changes.** Bump
   `squizlabs/php_codesniffer` to `^3.7` and `mockery/mockery` to `^1.6`,
   then `phpunit/phpunit` to `^9.6`. The psr-2 standard was renamed to `PSR12`,
   so `phpcs.xml.dist` has to change from `<rule ref="PSR2"/>` to
   `<rule ref="PSR12"/>`, and the test suite needs the phpunit 9 data provider
   and `setUp(): void` signatures. Do this on its own branch.
2. **Laravel 5.1 to 5.4.** The smallest hop, and it deprecates rather than
   removes everything we use. Expect to add `config/cache.php`,
   `config/queue.php` and `config/filesystems.php` shims and to switch
   `GrahamCampbell\Credentials` to its 5.4 compatible release.
3. **Laravel 5.4 to 5.8.** This is where `VerifyCsrfToken` becomes
   `VerifyCsrfToken` with an `except` array, `SetErrorsFromSession` moves, and
   the `Session::flash` workaround in `app/Http/routes.php` and
   `PageController::index` can finally be deleted.
4. **The graham-campbell packages.** `credentials`, `core`, `contact`,
   `navigation`, `throttle` and `exceptions` are all pinned to releases that
   only support the 5.1 line. Check each one for a compatible tag before
   planning the move, and treat the `sentry` fork as the long pole.
5. **The front end.** Replace gulp and elixir with a current bundler. This is
   independent of the php work and can be done first if the asset build is
   causing you pain.

## Things To Do At The Same Time

* Re-check `config/session.php`: `secure` is derived from `APP_URL` here, and
  modern laravel wants `http_only` and `same_site` spelled out as well.
* Re-check the `XSRF-TOKEN` handling: `Illuminate\Cookie\Middleware\EncryptCookies`
  in modern laravel maintains the exception list itself, so our subclass in
  `app/Http/Middleware/EncryptCookies.php` can go away.
* Re-check `app/Providers/AppServiceProvider.php`: modern laravel ships a
  `KeyGenerateCommand` and no longer needs the weak key guard, and the
  structured logging provider can be replaced by a `config/logging.php` stack.
