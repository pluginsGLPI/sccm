include ../../PluginsMakefile.mk

install-ext: ## Install the MSSQL client stack (ODBC 18 + sqlsrv/pdo_sqlsrv) into the running `app` container. Re-run after any container rebuild.
	$(COMPOSE) exec --user root app bash /var/www/glpi/plugins/$(PLUGIN_DIR)/.dev/install-sqlsrv.sh

##—— SCCM local test environment (MSSQL) ———————————————————————————————————————
# See plugins/sccm/.dev/README.md. These targets drive docker compose from the
# GLPI root, layering plugins/sccm/.dev/docker-compose.yml on top of core.

SCCM_COMPOSE_FILES = -f docker-compose.yaml -f plugins/sccm/.dev/docker-compose.yml
SCCM_SA_PASSWORD  ?= Glpi_Sccm_2026!
SCCM_PORT         ?= 12433
SCCM_MSSQL_VOLUME  = $(notdir $(realpath $(GLPI_DIR)))_sccm_mssql
export SCCM_PORT

sccm-env-up: ## Start the MSSQL service and install the client stack into the running `app` container (no image rebuild)
	@$(COMPOSE) ps --status running --services 2>/dev/null | grep -qx app \
		|| { echo "The 'app' container is not running - start the dev container first (VS Code: 'Dev Containers: Rebuild Container'), then re-run."; exit 1; }
	cd $(GLPI_DIR) && $(COMPOSE) $(SCCM_COMPOSE_FILES) up -d --no-recreate mssql
	@$(MAKE) --no-print-directory install-ext

sccm-env-down: ## Stop and remove the MSSQL service (keeps its data volume)
	cd $(GLPI_DIR) && $(COMPOSE) $(SCCM_COMPOSE_FILES) rm -sf mssql

sccm-env-destroy: ## Stop the MSSQL service and delete its data volume
	cd $(GLPI_DIR) && $(COMPOSE) $(SCCM_COMPOSE_FILES) rm -sf mssql
	docker volume rm $(SCCM_MSSQL_VOLUME) 2>/dev/null || true

sccm-db-seed: ## (Re)load the fixture schema plugins/sccm/.dev/sccm-schema.sql into MSSQL
	cd $(GLPI_DIR) && $(COMPOSE) $(SCCM_COMPOSE_FILES) exec -T mssql bash -lc '\
		SQLCMD=$$(command -v sqlcmd || echo /opt/mssql-tools18/bin/sqlcmd); \
		[ -x "$$SQLCMD" ] || SQLCMD=/opt/mssql-tools/bin/sqlcmd; \
		"$$SQLCMD" -C -S localhost -U sa -P "$(SCCM_SA_PASSWORD)" -i /sql/sccm-schema.sql'

sccm-db-shell: ## Open an interactive sqlcmd shell on the MSSQL container (database CM_TST)
	cd $(GLPI_DIR) && $(COMPOSE) $(SCCM_COMPOSE_FILES) exec mssql bash -lc '\
		SQLCMD=$$(command -v sqlcmd || echo /opt/mssql-tools18/bin/sqlcmd); \
		[ -x "$$SQLCMD" ] || SQLCMD=/opt/mssql-tools/bin/sqlcmd; \
		"$$SQLCMD" -C -S localhost -U sa -P "$(SCCM_SA_PASSWORD)" -d CM_TST'

sccm-verify-ext: ## Check that the sqlsrv extension is loaded in the app container
	cd $(GLPI_DIR) && $(COMPOSE) $(SCCM_COMPOSE_FILES) exec -T app php -m | grep -E '^(pdo_)?sqlsrv$$' || (echo "sqlsrv NOT loaded" && exit 1)

config: ## Print the values to enter in GLPI's Setup > SCCM > add a configuration page
	@echo "Server hostname (MSSQL): mssql"
	@echo "Database name: CM_TST"
	@echo "Username: sa"
	@echo "Password: $(SCCM_SA_PASSWORD)"
	@echo "Verify SSL certificate: no"
	@echo "Inventory server base URL: http://localhost"
	@echo "Utiliser des informations d'authentification spécifique: yes"
	@echo "Value for specific authentication: sccm-agent:$(SCCM_SA_PASSWORD)"

.PHONY: sccm-env-up sccm-env-down sccm-env-destroy sccm-db-seed sccm-db-shell sccm-verify-ext config
