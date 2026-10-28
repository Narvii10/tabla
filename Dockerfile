FROM dunglas/frankenphp:php8.3
RUN install-php-extensions pdo_mysql intl zip opcache bcmath
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache
ENV SERVER_NAME=:80
