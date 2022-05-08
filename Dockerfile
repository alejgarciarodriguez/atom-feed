FROM php:8.1-fpm-alpine as base
RUN apk add --no-cache git
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/app

FROM base as dev
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
    	pecl install xdebug; \
    	docker-php-ext-enable xdebug; \
    	apk del .build-deps

FROM base as app
COPY . .
RUN composer install
ENTRYPOINT ["bin/console"]