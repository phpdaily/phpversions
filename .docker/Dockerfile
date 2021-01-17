FROM phpdaily/php:8.1.0-dev-fpm-alpine3.12

# System dependencies
RUN apk add --update bash sqlite

# Install Composer
RUN wget https://getcomposer.org/composer.phar \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

# Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony/bin/symfony /usr/local/bin/symfony
