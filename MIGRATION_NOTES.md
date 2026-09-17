# Notes de migration GLPI 12 — plugin SCCM

Suivi des tests réalisés sur `feature/glpi-12.0`, dans le cadre de la
procédure de test des plugins GLPI 12. Complète [.dev/README.md](.dev/README.md)
(mise en place de l'environnement) avec les **résultats** des tests et les
constats à connaître avant de rejouer cette migration ailleurs.

Environnement : GLPI core `main` (12.0.0-dev), plugin `feature/glpi-12.0`
(2.6.1), stack Docker locale (voir `.dev/`).

## Constats importants pour toute migration de plugin vers GLPI 12

### 1. Suspension automatique de l'exécution des plugins après une mise à jour majeure

Dès qu'une mise à jour majeure du core est détectée (`Update::isUpdateMandatory()`),
GLPI 12 **suspend automatiquement l'exécution de tous les plugins**
(`Config` `core.plugins_execution_mode = suspended_by_update`,
voir `src/Plugin.php::checkStates()`). Ce verrou :

* bloque le chargement du plugin **côté web uniquement** — toute URL
  `/plugins/<key>/front/*.php` renvoie **404 "Item not found"**
  (`PluginsRouterListener` → `Plugin::isPluginLoaded()` = false) ;
* **n'est pas levé automatiquement** après un `bin/console database:update`
  réussi, dès lors que la mise à jour change de version majeure/intermédiaire
  (`src/Update.php:415` — la reprise auto ne s'applique qu'entre versions de
  même ligne, ex. 12.0.0 → 12.0.1) ;
* n'affecte pas le CLI : `bin/console plugin:list/install/enable` continuent
  d'afficher le plugin comme « Enabled » (l'état `glpi_plugins.state` en base
  n'est pas modifié — seul un flag de config global bloque le chargement).

**Sans le savoir, on peut donc croire une migration "cassée" côté web alors
que tout est correct côté CLI/DB.** À lever explicitement après toute
migration majeure :

```bash
docker compose exec app php bin/console plugin:resume_execution
```

*(commande liée à `front/plugin.php`, qui exige elle-même une réauthentification
— redirection `/ReAuth/Prompt` observée lors des tests — cohérent avec le
caractère sensible de l'action.)*

### 2. CSRF : mécanisme entièrement remplacé (confirme la consigne de migration)

* **GLPI 11** : champ caché `_glpi_csrf_token`, valeur vérifiée via
  `Session::checkCSRF()` sur chaque formulaire (confirmé en observant le
  code source HTML du formulaire de connexion GLPI 11 — token présent).
* **GLPI 12** : plus aucun champ caché. Validation faite par un listener
  Symfony central (`Glpi\Kernel\Listener\ControllerListener\CheckCsrfListener`)
  qui compare les en-têtes `Sec-Fetch-Site` / `Origin` au `Host` de la requête,
  sur **toute requête non-GET**, automatiquement, sans rien à faire côté plugin.
* Le plugin SCCM n'ajoute **aucun champ `CSRF_token` manuel** dans son
  template (`templates/config.html.twig` s'appuie entièrement sur
  `generic_show_form.html.twig` du core) → **conforme par construction**,
  aucun correctif nécessaire sur ce point.
* Testé et confirmé fonctionnel : une requête POST cross-site forgée
  (`Origin` différent du `Host`) sur `config.form.php` (action purge) est
  bloquée avec 403 ; la requête légitime same-origin aboutit normalement.

## Phase 1 — Environnement de test

Voir [.dev/README.md](.dev/README.md) : image `app` + extension `sqlsrv`/ODBC 18
installée à chaud (non baked dans l'image, pour ne pas casser le Dev
Container), service `mssql` additif, base fixture `CM_TST` (2 machines,
`PC-DEV-01`/`PC-DEV-02`).

Prérequis GLPI découverts en cours de route, absents de la doc initiale et
maintenant documentés dans `.dev/README.md` :

* l'inventaire natif GLPI doit être activé explicitement
  (*Configuration → Inventaire → Configuration générale*), sinon 403 sur
  `/front/inventory.php` quelle que soit la config du plugin ;
* dès l'inventaire activé, l'authentification basique est active par défaut
  → nécessite de renseigner *Utiliser des informations d'authentification
  spécifique* côté plugin (`login:mot_de_passe`), le plugin gère déjà ce cas
  via `CURLOPT_USERPWD`.

## Phase 2 — Migration GLPI 11 → 12

Rejouée dans un environnement dédié (`git worktree` séparé, stack Docker sur
ports `11xxx`, indépendante de la stack de dev principale) :

1. Install + config du plugin `main` (2.6.1) sur GLPI 11.0.9-dev fraîchement
   installé, config `MigrationTest` avec mot de passe MSSQL et `auth_info`
   réels (chiffrés).
2. Bascule du code : core `11.0/bugfixes` → `main` (12.0.0-dev), plugin
   `main` → `feature/glpi-12.0`.
3. `bin/console database:update --no-interaction` : OK, sans erreur.
4. `bin/console plugin:resume_execution` (cf. constat n°1 ci-dessus).

**Résultat** : table, cron tasks et **secrets chiffrés préservés et
déchiffrables** après migration — `Test connection` post-migration retourne
`Login successful` / `Connection successful!` (connexion réelle à MSSQL avec
le mot de passe créé avant migration). Aucune erreur dans les logs après
reprise de l'exécution des plugins.

## Phase 3 — Tests fonctionnels (sur la stack de dev GLPI 12 principale)

| Test | Résultat |
|---|---|
| Installation / activation / désactivation / désinstallation | ✅ |
| Configuration (CRUD, multi-config, Test connection) | ✅ |
| Synchro complète (SCCMCollect → XML → SCCMPush → inventaire natif) | ✅ multi-machines |
| Idempotence (re-run sans doublon) | ✅ |
| Option `use_lasthwscan` | ✅ — `last_inventory_update` remplacé par le `LastHWScan` SCCM, pas l'heure du push |
| Scope de collection avec apostrophe (`Site Bordeaux - O'Brien`) | ✅ pas d'injection, échappement correct |
| Droits/profils (profil Technician sans droit `config`) | ✅ 403 sur GET/POST, menu masqué, rien créé en base |
| CSRF (cf. constat n°2) | ✅ |
| Purge (avec test CSRF forgé au préalable) | ✅ |
| Désinstallation complète | ✅ table, cron, display prefs, dossier `files/_plugins/sccm/` tous nettoyés ; ordinateurs importés conservés (assets natifs, non liés au plugin) |
| Logs (`php-errors.log`, `sql-errors.log`, mode debug) | propres à chaque étape |

## Bugs / points à corriger avant la PR

### 🐛 Exception non catchée dans `executePush()` (à corriger)

[inc/sccm.class.php:584](inc/sccm.class.php#L584) :

```php
$REP_XML = realpath(GLPI_PLUGIN_DOC_DIR . '/sccm/xml/' . $config_id . '/' . $tab['CSD-MachineID'] . '.ocs');
if ($REP_XML === '0') {   // mort : Safe\realpath() ne renvoie jamais '0', il lève une exception
```

`Safe\realpath()` **lève une exception** (`Safe\Exceptions\FilesystemException`)
quand le chemin n'existe pas, au lieu de renvoyer `false` comme la fonction
native. Le test `=== '0'` est donc du code mort. Reproduit en conditions
réelles : `SCCMPush` lancé avant que `SCCMCollect` n'ait produit le `.ocs`
correspondant → exception non catchée (`executePush()` n'a **aucun**
`try/catch`, contrairement à `executeCollect()` qui catch `Throwable` par
config) → toute la tâche plante pour toute la config, rien loggé dans
`sccm.log`. Un `.ocs` manquant/purgé en prod (device supprimé de SCCM entre
temps, nettoyage manuel du dossier) provoquerait le même crash.

**Correctif suggéré** : `file_exists()` avant `realpath()`, ou `try/catch`
autour de l'appel, avec log + `continue` comme le reste de la boucle.

### ❓ À vérifier : unité de `Capacity0` (mémoire)

[inc/sccmxml.class.php `setMemories()`](inc/sccmxml.class.php) ne fait
**aucune conversion d'unité** sur `Capacity0` avant de l'injecter dans
`<CAPACITY>`, contrairement à `setStorages()` qui fait `* 1024` sur
`gld-TotalSize`/`gld-FreeSpace`. GLPI/FusionInventory attend `CAPACITY` en
**Mo**. Si `v_GS_PHYSICAL_MEMORY.Capacity0` de SCCM est en Ko (à confirmer
sur une vraie base SCCM — non vérifiable avec le fixture, qui a été codé en
Mo pour ce test), les tailles mémoire remontées en prod seraient ~1024×
trop grandes. Impact : affichage erroné, pas de crash (la colonne `size` en
DB est un `int`, largement suffisant pour ce facteur d'erreur). À trancher
avec un accès à une vraie base SCCM ou la doc Microsoft (`Win32_PhysicalMemory`).

## Phase 5 — Lint / analyse statique

Tous outils exécutés depuis le conteneur `app`, binaires partagés du core
(`../../vendor/bin/*`, pas de `vendor/` local au plugin pour ces outils sauf
PHPUnit qui a besoin de son propre `composer install` pour générer
`vendor/autoload.php`, cf. `tests/bootstrap.php`).

| Outil | Résultat |
|---|---|
| Parallel-lint | ✅ aucune erreur |
| PHP-CS-Fixer | ✅ 0 finding |
| Rector | 🔧 1 finding corrigé — voir ci-dessous |
| PHPStan (level 5) | ✅ aucune erreur |
| Psalm (taint analysis) | ✅ aucune erreur |
| PHPUnit | 🔧 62/62 tests passaient déjà ; 41 warnings PHPUnit 12 corrigés — voir ci-dessous. `OK (62 tests, 273 assertions)` |
| Twig CS | ✅ aucune violation |
| License headers (`tools:licence_headers_check`) | ✅ valides |
| ESLint | N/A — aucun JS dans ce plugin (pas de `package.json`) |

Correctifs appliqués :

* **`setup.php`** — Rector (`ReplaceHardcodedRightnameByCommonDBTMRightnamePropertyRector`) :
  remplace le littéral `"config"` par `Config::$rightname` dans le
  `Session::haveRight()` de `plugin_init_sccm()`.
* **`tests/PluginSccmSccmxmlTest.php`** — ajout de l'attribut
  `#[AllowMockObjectsWithoutExpectations]` au niveau classe. PHPUnit 12
  signale par défaut tout mock (`getMockBuilder(...)->getMock()`) jamais
  vérifié via `->expects()`, ce qui est le cas ici par construction : les
  mocks de `PluginSccmSccmxml`/`PluginSccmSccm`/`PluginSccmSccmdb` ne
  servent qu'à fournir des données de test (usage "stub"), pas à vérifier
  des appels. Motif déjà utilisé dans les tests du core GLPI
  (`tests/functional/ConfigTest.php` et autres) pour ce cas exact.

L'installation d'un environnement de test dédié a été nécessaire au préalable
(non documentée ailleurs, à refaire si besoin) :

```bash
php bin/console database:install -r -f --db-host=db --db-port=3306 \
  --db-name=glpi_test --db-user=root --db-password=glpi \
  --no-interaction --no-telemetry --env=testing
php bin/console plugin:install --env=testing sccm -u glpi --force
php bin/console plugin:enable --env=testing sccm
cd plugins/sccm && composer install   # génère vendor/autoload.php, requis par tests/bootstrap.php
```

## Reste à faire

* [ ] Corriger le bug `realpath()` (voir ci-dessus)
* [ ] Trancher le point `Capacity0`
* [ ] Mettre à jour `sccm.xml` (`<compatibility>12.0.0</compatibility>`) et le CHANGELOG avant la PR
* [ ] Commit des correctifs (`setup.php`, `tests/PluginSccmSccmxmlTest.php`) et de ce fichier
