# SCCM plugin — local test environment

The plugin talks to a **Microsoft SQL Server** database (the SCCM / MECM
source) through the `sqlsrv` PHP extension, then pushes FusionInventory-style
XML to GLPI's **native** inventory endpoint (`/front/inventory.php`).

A full SCCM/MECM install is not reproducible in a dev environment, so this
folder ships a **fixture MSSQL database** containing only the tables/views the
plugin actually queries, plus two sample machines.

## What's here

| File | Purpose |
|------|---------|
| `docker-compose.yml` | Purely additive: declares only the `mssql` service (never touches `app`) |
| `install-sqlsrv.sh` | Installs `msodbcsql18` + `sqlsrv` / `pdo_sqlsrv` into the **running** `app` container |
| `Dockerfile` | Optional standalone image (CI); **not** used by the default `make` workflow |
| `sccm-schema.sql` | Idempotent fixture schema + data (database `CM_TST`) |

> **Why no image rebuild?** When `app` is a VS Code Dev Container, running
> `docker compose … up --build` recreates it *without* the Dev Container
> overrides (`/vscode` mount, keep-alive command) and the running container
> breaks. So the MSSQL client stack is installed into the live container
> instead. It lives in the container's writable layer — **re-run
> `make sccm-env-up` (or `make install-ext`) after every container rebuild.**

## Requirements

- The GLPI core docker stack (`docker-compose.yaml` at the GLPI root).
- `x86_64` host. On Apple Silicon / ARM, swap the image in
  `docker-compose.yml` for `mcr.microsoft.com/azure-sql-edge`.
- The base image is assumed to be Debian 12. If GLPI core changes it, adjust
  the `packages.microsoft.com` line in `Dockerfile`.

## Usage

All commands are `make` targets defined in `plugins/sccm/Makefile`; run them
from the **plugin** directory (`plugins/sccm`).

The `app` (dev) container must already be running.

```bash
# 1. start the MSSQL service + install sqlsrv/ODBC 18 into the running app container
make sccm-env-up

# 2. wait ~20-30s for MSSQL to become healthy, then load the fixtures
make sccm-db-seed

# 3. sanity check: the extension is loaded
make sccm-verify-ext
```

Then install / enable the plugin as usual:

```bash
make install
make enable
```

Run `make config` to print the values to enter in step 2 below.

### 1. Enable GLPI's native inventory

*Setup → Inventory → General setup* → check **"Enable inventory"**. Disabled by
default on a fresh instance → the SCCM push would otherwise get a `403
Forbidden` from `/front/inventory.php` regardless of any plugin setting.

Note the **"Authorization header"** selector on that same page: as soon as
inventory is enabled it defaults to **Basic Authentication**, which the push
will need to satisfy (step 2 below) — otherwise you get a `401 Authorization
header required to send an inventory`. Set a login/password there, e.g.
`sccm-agent` / `Glpi_Sccm_2026!`.

### 2. Plugin configuration to enter in GLPI

*Setup → SCCM → add a configuration*

| Field | Value |
|-------|-------|
| Enable synchronization | ✅ |
| Server hostname (MSSQL) | `mssql` |
| Database name | `CM_TST` |
| Username | `sa` |
| Password | `Glpi_Sccm_2026!` |
| Collection name | *(empty = all machines)* — or `Workstations`, or `Site Bordeaux - O'Brien` to exercise the quote-escaping path (only matches `PC-DEV-01`) |
| **Verify SSL certificate** | ❌ **must be unchecked** — MSSQL uses a self-signed cert; ODBC Driver 18 forces encryption and would otherwise reject the connection |
| Inventory server base URL | `http://localhost` — **not** the value of `url_base` if it points at the host-side published port (e.g. `http://localhost:12080`): that port mapping isn't reachable *from inside* the `app` container, only from the host. `http://localhost` (no port = 80) reaches GLPI's own Apache, in-container. |
| Utiliser des informations d'authentification spécifique | ✅ — required as soon as GLPI's inventory "Authorization header" is set to Basic Auth (the default, see step 1) |
| Value for specific authentication | `sccm-agent:Glpi_Sccm_2026!` — **`login:password`**, matching step 1 exactly. This is the raw string passed to cURL's `CURLOPT_USERPWD`, not two separate fields. |

Use the **Test connection** button to confirm MSSQL connectivity (this only
tests the `sqlsrv` side, not the inventory push).

### 3. Running the sync

*Setup → Automatic actions → `SCCMCollect` → Execute*, then `SCCMPush`.
Generated XML lands in `files/_plugins/sccm/xml/<config_id>/`.
Expected result: computer `PC-DEV-01` (and `PC-DEV-02` if the collection scope
is left empty) imported as an asset. Check `files/_log/sccm.log` for
`Push OK` / `Push KO` lines — a `KO` line includes GLPI's own inventory error
response, which is usually the fastest way to diagnose a failure.

### Inspecting / resetting the fixture DB

```bash
make sccm-db-shell     # interactive sqlcmd on CM_TST
make sccm-db-seed      # re-run to reset fixture data (idempotent)
```

### Teardown

```bash
make sccm-env-down     # remove the mssql container, keep its volume
make sccm-env-destroy  # remove the mssql container AND its data volume
```

`app` never depends on `mssql`, so teardown only affects the MSSQL container.
The `sqlsrv` extension stays in the `app` container until its next rebuild.

## Notes

- `docker-compose.yml` is **layered** on core via `-f` (see the Makefile),
  so it never edits a GLPI core file and needs no `docker-compose.override.yaml`.
  It is purely additive (only the `mssql` service), so it never recreates `app`.
- The MSSQL port is published on `localhost:12433` for external GUI tools;
  override with `make sccm-env-up SCCM_PORT=<port>` if 12433 is already taken.
- `make install-ext` runs `install-sqlsrv.sh` on its own — use it to reinstall
  the client stack after a container rebuild without restarting MSSQL.
- The extension is picked up immediately by any new `php` CLI process (the test
  runner); the web (Apache) side is reloaded by the script.
