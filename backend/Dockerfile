FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

FROM php:8.3-cli AS app
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql bcmath \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY --from=vendor /app /app
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
ENTRYPOINT ["entrypoint.sh"]
