#!/bin/bash
set -e

PHP_ERROR_REPORTING=${PHP_ERROR_REPORTING:-"E_ALL & ~E_DEPRECATED"}

cat > /usr/local/etc/php/conf.d/app.ini <<INI
display_errors = On
error_reporting = ${PHP_ERROR_REPORTING}
log_errors = On
error_log = /dev/stderr
date.timezone = America/Sao_Paulo
INI

mkdir -p cache log
chmod -R 777 cache log

exec apache2-foreground
