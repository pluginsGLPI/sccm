#!/bin/bash

#
# -------------------------------------------------------------------------
# SCCM plugin for GLPI
# -------------------------------------------------------------------------
#
# LICENSE
#
# This file is part of SCCM.
#
# SCCM is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# SCCM is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with SCCM. If not, see <http://www.gnu.org/licenses/>.
# -------------------------------------------------------------------------
# @author    François Legastelois
# @copyright Copyright (C) 2014-2023 by SCCM plugin team.
# @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
# @link      https://github.com/pluginsGLPI/sccm
# -------------------------------------------------------------------------
#

sudo apt-get update -yq

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")

sudo apt-get install -yq unixodbc-dev

if [[ "$PHP_VERSION" == "7.4" ]]; then
sudo pecl install sqlsrv-5.10.1
elif [[ "$PHP_VERSION" == "8.0" ]]; then
sudo pecl install sqlsrv-5.11.0
elif [[ "$PHP_VERSION" == "8.1" || "$PHP_VERSION" == "8.2" ]]; then
sudo pecl install sqlsrv-5.12.0
else
sudo pecl install sqlsrv
fi

echo "extension=sqlsrv.so" | sudo tee -a /usr/local/etc/php/php.ini
