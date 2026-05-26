include ../../PluginsMakefile.mk

install-ext: ## Install PHP extensions required by this plugin (sqlsrv). Re-run after any container rebuild.
	$(COMPOSE) exec --user root app bash -c "\
		apt-get update -yq \
		&& apt-get install -yq --no-install-recommends unixodbc-dev \
		&& rm -rf /var/lib/apt/lists/* \
		&& pecl install sqlsrv \
		&& docker-php-ext-enable sqlsrv"
