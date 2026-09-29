Bootstrap CMS
=============

Bootstrap CMS was created by, and is maintained by [Graham Campbell](https://github.com/GrahamCampbell), and is a PHP CMS powered by [Laravel 5.1](http://laravel.com) and [Sentry](https://cartalyst.com/manual/sentry). It utilises many of my packages including [Laravel Core](https://github.com/GrahamCampbell/Laravel-Core) and [Laravel Credentials](https://github.com/BootstrapCMS/Credentials). Feel free to check out the [releases](https://github.com/BootstrapCMS/CMS/releases), [license](LICENSE), [screenshots](SCREENSHOTS.md), [contribution guidelines](CONTRIBUTING.md), [changelog](CHANGELOG.md), and [security policy](SECURITY.md).

![Bootstrap CMS](https://cloud.githubusercontent.com/assets/2829600/4432327/c1ae6436-468c-11e4-84eb-4e5e546da3ff.PNG)

<p align="center">
<a href="https://github.com/PreCogSecurity/CMS/actions"><img src="https://github.com/PreCogSecurity/CMS/actions/workflows/ci.yml/badge.svg" alt="Build Status" style="flat-square"></a>
<a href="LICENSE"><img src="https://img.shields.io/badge/license-AGPL%203.0-brightgreen.svg?style=flat-square" alt="Software License"></a>
<a href="https://github.com/PreCogSecurity/CMS/releases"><img src="https://img.shields.io/github/release/PreCogSecurity/CMS.svg?style=flat-square" alt="Latest Version"></a>
</p>


## Requirements

* PHP 5.5.9 or newer, with the `mbstring`, `openssl` and `pdo_mysql` extensions
* [Composer](https://getcomposer.org) 2
* MySQL 5.7 or newer, or any database Laravel 5.1 supports
* Node.js and npm, only if you intend to rebuild the front end assets


## Installation

1. There are 3 ways of grabbing the code:
  * Use GitHub: simply download the zip on the right of the readme
  * Use Git: `git clone git@github.com:PreCogSecurity/CMS.git`
  * Use Composer: `composer create-project graham-campbell/bootstrap-cms --prefer-dist -s dev`
2. From a command line open in the folder and run `composer install` (add `--no-dev -o` for a production checkout, and `npm ci` if you also need the asset toolchain).
3. Copy `.env.example` to `.env` and fill in your database details, then generate your application key with `php artisan key:generate`. The application refuses to serve requests with a missing or short key, because that key is what protects your session cookies.
4. Run `php artisan app:install` to create the schema and seed the first user and page.
5. Run `gulp --production` to build the css and js (skip this if you deployed prebuilt assets).
6. You will need to enter your mail server details into `config/mail.php`.
  * You can disable verification emails in `config/credentials.php`
  * Mail is still required for other functions like password resets and the contact form
  * You must set the contact email in `config/contact.php`
  * I'd recommend [queuing](#setting-up-queing) email sending for greater performance (see below)
7. Finally, setup an [Apache VirtualHost](http://httpd.apache.org/docs/current/vhosts/examples.html) to point to the "public" folder.
  * For development, you can simply run `php artisan serve`


## Running With Docker

If you would rather not set up PHP, Composer and MySQL by hand, the repository
ships a compose file that brings the whole application up for you:

```
docker compose up --build
```

That starts MySQL, installs the application into it, and serves the site on
[http://localhost:8000](http://localhost:8000). The api keys baked into
`docker-compose.yml` exist for that sandbox only, so replace `APP_KEY` before
you do anything else with the image.

To run the test suite in the same image, with no database server and no other
setup at all:

```
docker compose run --rm tests
```


## Testing

The project ships with a PHPUnit test suite covering the middleware, controllers, repositories, facades, and commands. The suite runs against an in-memory SQLite database, so no database server is required to run the tests.

1. Install the dev dependencies: `composer install`
2. Run the test suite: `vendor/bin/phpunit` (or `composer test`)
3. Check the PSR-2 coding standard: `vendor/bin/phpcs` (or `composer lint`)

Both commands run on every push and pull request in [GitHub Actions](.github/workflows/ci.yml), along with a coverage run and a `composer audit`, so a pull request that breaks the tests or the coding standard fails the build.


## Building The Assets

The front end is built with gulp and laravel-elixir, which pin a 2014 era
Node.js toolchain. Use Node.js 8 or 10, then:

```
npm ci
gulp --production
```

The compiled css and js are written to `public/assets`, and are not committed.


## Security

Please read [SECURITY.md](SECURITY.md) before exposing an installation to the
internet. The defaults shipped here are the safe ones: CSRF protection is
enforced on every state changing request, page bodies are never handed to
`php`'s `eval`, slugs are unique per page, and responses carry the usual
hardening headers. Turning any of them off is your call, and the ones that
matter are described in that document.


## Setting Up Queuing

Bootstrap CMS uses Laravel's queue system to offload jobs such as sending emails so your users don't have to wait for these activities to complete before their pages load. By default, we're using the "sync" queue driver.

1. Check out Laravel's [documentation](http://laravel.com/docs/master/queues#configuration).
2. Enter your queue server details into `config/queue.php`.


## Setting Up Caching

Bootstrap CMS provides caching functionality, and when enabled, requires a caching server.
Note that caching will not work with Laravel's `file` or `database` cache drivers.

1. Choose your poison - I'd recommend [Redis](http://redis.io).
2. Enter your cache server details into `config/cache.php`.
3. Setting the driver to array will effectively disable caching if you don't want the overhead.


## Setting Up Themes

Bootstrap CMS also ships with 18 themes, 16 from [Bootswatch](http://bootswatch.com).

1. You can set your theme in `config/theme.php`.
2. You can also set your navbar style in `config/theme.php`.
3. After making theme changes, you will have to run `php artisan app:update`.


## Setting Up Google Analytics

Bootstrap CMS natively supports [Google Analytics](http://www.google.com/analytics).

1. Setup a web property on [Google Analytics](http://www.google.com/analytics).
2. Enter your tracking id into `config/analytics.php`.
3. Enable Google Analytics in `config/analytics.php`.


## Setting Up CloudFlare Analytics

Bootstrap CMS can read [CloudFlare](https://www.cloudflare.com/) analytic data through a package.

1. Follow the install instructions for my [Laravel CloudFlare](https://github.com/BootstrapCMS/CloudFlare) package.
2. Bootstrap CMS will auto-detect the package, only allow admin access, and add links to the navigation bar.


## Upgrading

This project runs on a legacy framework line. [UPGRADE.md](UPGRADE.md)
describes the state of the dependencies, the published advisories that come
with them, and the order we recommend tackling them in.


## License

GNU AFFERO GENERAL PUBLIC LICENSE

Bootstrap CMS Is A PHP CMS Powered By Laravel 5 And Sentry

Copyright (C) 2013-2015 Graham Campbell

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
