# -------------------------------------------------------------------------
# SCCM plugin for GLPI - local development image  (OPTIONAL / NOT wired in)
# -------------------------------------------------------------------------
# GLPI development image + Microsoft SQL Server client stack:
#   - msodbcsql18 (Microsoft ODBC Driver 18 for SQL Server)
#   - sqlsrv / pdo_sqlsrv PHP extensions
#
# This mirrors GLPI core's .docker/app/Dockerfile and adds the pieces the
# SCCM plugin needs to talk to a real MSSQL instance.
#
# NOTE: the default workflow no longer builds a custom `app` image - that
# recreated the `app` container and broke VS Code Dev Containers. Instead,
# `make sccm-env-up` installs the same stack into the *running* container
# via plugins/sccm/.dev/install-sqlsrv.sh. This Dockerfile is kept only for
# building a standalone image outside a dev container (e.g. CI):
#   docker build -t glpi-sccm-app --build-arg PHP_VERSION=8.4 plugins/sccm/.dev
# -------------------------------------------------------------------------

ARG PHP_VERSION=8.4

FROM ghcr.io/glpi-project/glpi-development-env:${PHP_VERSION}

USER root

# --- verbatim from GLPI core .docker/app/Dockerfile -----------------------
ENV PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d/:/usr/local/etc/php/custom_conf.d/"
RUN mkdir -p /usr/local/etc/php/custom_conf.d/

ARG HOST_GROUP_ID=1000
ARG HOST_USER_ID=1000
RUN groupmod --gid ${HOST_GROUP_ID} www-data \
 && usermod --uid ${HOST_USER_ID} www-data \
 && usermod --shell /bin/bash www-data \
 && echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers \
 && mkdir --parents /home/www-data \
 && chown www-data:www-data /home/www-data \
 && usermod --home /home/www-data www-data

# --- Microsoft ODBC driver 18 + sqlsrv/pdo_sqlsrv PHP extensions ---------
# NOTE: the base image is Debian 12 (bookworm). If it ever switches distro
#       or release, adjust the packages.microsoft.com path below.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg apt-transport-https unixodbc-dev; \
    curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg; \
    echo "deb [signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
        > /etc/apt/sources.list.d/mssql-release.list; \
    apt-get update; \
    ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18; \
    pecl install sqlsrv pdo_sqlsrv; \
    docker-php-ext-enable sqlsrv pdo_sqlsrv; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

VOLUME /home/www-data

USER www-data
