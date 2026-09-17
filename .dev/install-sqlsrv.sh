#!/usr/bin/env bash
# -------------------------------------------------------------------------
# SCCM plugin for GLPI - MSSQL client stack installer
# -------------------------------------------------------------------------
# Installs, into a *running* GLPI dev container (no image rebuild):
#   - Microsoft ODBC Driver 18 for SQL Server (msodbcsql18)
#   - sqlsrv / pdo_sqlsrv PHP extensions
#
# Run as root inside the `app` container. Idempotent - safe to re-run.
# Must be re-run after every container rebuild: the changes live in the
# container's writable layer, not in an image.
#
# Driven by `make sccm-env-up` / `make install-ext` (plugins/sccm/Makefile).
#
# NOTE: the base image is Debian 12 (bookworm). If GLPI core switches distro
#       or release, adjust the packages.microsoft.com path below.
# -------------------------------------------------------------------------
set -euo pipefail

if php -m | grep -qx 'sqlsrv' && php -m | grep -qx 'pdo_sqlsrv'; then
    echo "sqlsrv + pdo_sqlsrv already loaded - nothing to do."
    exit 0
fi

export DEBIAN_FRONTEND=noninteractive

apt-get update
apt-get install -y --no-install-recommends \
    ca-certificates curl gnupg apt-transport-https unixodbc-dev

if [ ! -f /usr/share/keyrings/microsoft-prod.gpg ]; then
    curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg
fi
echo "deb [signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
    > /etc/apt/sources.list.d/mssql-release.list

apt-get update
ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18

pecl list 2>/dev/null | grep -qi '^sqlsrv'     || pecl install sqlsrv
pecl list 2>/dev/null | grep -qi '^pdo_sqlsrv' || pecl install pdo_sqlsrv
docker-php-ext-enable sqlsrv pdo_sqlsrv

apt-get clean
rm -rf /var/lib/apt/lists/*

# Best-effort reload so a running Apache picks up the extension.
# (A fresh `php` CLI - i.e. the test runner - sees it with no reload.)
if command -v apache2ctl >/dev/null 2>&1; then
    apache2ctl -k restart 2>/dev/null && echo "Apache reloaded." || true
fi

echo
if php -m | grep -qE '^(pdo_)?sqlsrv$'; then
    echo "sqlsrv stack installed OK:"
    php -m | grep -E '^(pdo_)?sqlsrv$'
else
    echo "ERROR: sqlsrv extension still not loaded." >&2
    exit 1
fi
