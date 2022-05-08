FROM php:8.1-fpm-alpine as dev

RUN apk add --no-cache git
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/app

FROM dev as app

COPY . .
RUN composer install
ENTRYPOINT ["bin/console"]