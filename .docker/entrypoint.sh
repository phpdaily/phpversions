#/bin/sh

set -e

echo "* * * * * cd /var/www/phpversions && bin/console synchronize >> var/log/cron.log" >> /etc/crontabs/root

/usr/sbin/crond

/usr/local/bin/docker-php-entrypoint "$@"
