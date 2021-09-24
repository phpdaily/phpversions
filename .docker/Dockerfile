FROM phpdaily/php:8.1-fpm-alpine3.14

# System dependencies
RUN apk add --update bash sqlite supervisor

# Install Composer
RUN wget https://getcomposer.org/composer.phar \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

# Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony/bin/symfony /usr/local/bin/symfony

RUN echo '0 0 * * * cd /var/www/phpversions && bin/console synchronize >> /var/www/phpversions/var/log/cron.log' >> /etc/crontabs/root

COPY conf/supervisor.conf /etc/supervisor/phpversions.conf
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/phpversions.conf"]
