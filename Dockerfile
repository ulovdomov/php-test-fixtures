FROM php:8.3-fpm

ARG COMPOSER_TOKEN

RUN apt-get update -y
RUN apt-get install nano vim git zip libicu-dev libpq-dev mariadb-client postgresql-client -y
RUN apt-get upgrade -y

RUN docker-php-ext-configure intl
RUN docker-php-ext-install -j "$(nproc)" mysqli pdo_mysql pgsql pdo_pgsql intl

RUN mkdir -p src temp log

RUN chown -R www-data:www-data /var/www
RUN chmod -R 0777 .

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

ADD . /var/www/html

RUN mkdir -p .composer

RUN echo "{\"github-oauth\": {\"github.com\": \"${COMPOSER_TOKEN}\"}}" > .composer/auth.json

USER www-data

CMD ["php-fpm"]