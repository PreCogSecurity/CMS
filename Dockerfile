# Bootstrap CMS
#
# The image is deliberately php only: the gulp and elixir asset pipeline needs
# a 2014 era node runtime, and nothing in the test suite depends on it. See the
# "Building the assets" section of the readme for that half.
FROM php:7.4-cli

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libonig-dev \
        libsqlite3-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" mbstring dom pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY . .

# --no-scripts skips the artisan post install hooks, which expect an installed
# application with a configured environment
RUN composer install --no-interaction --prefer-dist --no-scripts \
    && composer dump-autoload --optimize

# overridden by the app service in docker-compose.yml
CMD ["vendor/bin/phpunit"]
