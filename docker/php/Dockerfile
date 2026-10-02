FROM php:8.2-fpm-bookworm
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ARG APP_UID=1000
ARG APP_GID=1000
RUN groupadd -g ${APP_GID} app && useradd -m -u ${APP_UID} -g app app
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-project.ini
WORKDIR /var/www/html
USER app
CMD ["php-fpm"]
