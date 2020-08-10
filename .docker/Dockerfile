FROM phpdaily/php:8.0.0-dev-fpm-alpine

RUN apk add --update bash sqlite
RUN wget https://getcomposer.org/composer.phar \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer
