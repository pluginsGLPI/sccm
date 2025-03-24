#!/bin/bash

sudo apt-get update -yq

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")

sudo apt-get install -yq unixodbc-dev

if [[ "$PHP_VERSION" == "7.4" ]]; then
sudo pecl install sqlsrv-5.10.1
elif [[ "$PHP_VERSION" == "8.0" ]]; then
sudo pecl install sqlsrv-5.11.0
else
sudo pecl install sqlsrv
fi

echo "extension=sqlsrv.so" | sudo tee -a /usr/local/etc/php/php.ini
