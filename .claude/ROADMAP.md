# Hotspots refactor roadmap

Goal: Hotspots runs natively on Joomla 5.4 and 6.x, on PHP 8.1 to 8.4, without the Backward Compatibility plugin and without lib_compojoom. Live site the client site runs Joomla 5.4.10 (2026-11-02).

Rules for every phase are in `CLAUDE.md` (target, reference code, PHP rules).

## Starting point (scan of 2026-11-02)

| Part | PHP lines | Notes |
|---|---|---|
| `com_hotspots` admin | 11,300 | 8 views, 11 controllers, 3 tables, own libraries: cache, geocoder, importers, maps, recaptcha, rss |
| `com_hotspots` site | 8,600 | views hotspots, hotspot, form, userhotspots, kmls, tile, json/jsonv4, api; HMVC helper; old router |
| `lib_compojoom` | 17,200 | Generic Compojoom framework, Joomla 3 style. Much of it is probably unused by Hotspots (cssmin, shrink, minifier, galleria, testing, ogp, ...) |
| Plugins | 2,600 | 17 plugins, most for integrations switched off on the site |
| `mod_hotspots_list` | 130 | |
| CB `plug_hotspots` | 560 | Legacy, disabled, replaced by `the client's CB plugin` (removed in 6.1.0) |

- Database: `#__hotspots_marker`, `#__hotspots_kmls`, `#__hotspots_mappings`, plus custom fields tables. Categories are Joomla categories (`extension = com_hotspots`). The schema is installed through Compojoom's XML installer (`sql/xml/mysql.xml`), not plain SQL files.
- About 70 different legacy `J*` classes in use (`JFactory`, `JText`, `JControllerLegacy`, `JTable`, ...). They only work because the Behaviour - Backward Compatibility plugin (J5) / compat6 plugin (J6) maps them.
- Classes removed in Joomla 4 that fail as soon as the code runs: `JError` (admin entry point `hotspots.php:17`, `lib_compojoom/database/installer.php`), `JException` (`tables/kmls.php:86,117`), `JSubMenuHelper` (`helpers/hotspots.php:44`).
- Old JavaScript: a MooTools map layer in `media/com_hotspots/js/moo/` (Joomla 4+ no longer ships MooTools) next to a newer `js/v4/app.js`. Find out which one the live map uses.
- Google Maps JS API and Geocoding API (the geocoder has our proxy and timeout patch).

### PHP compatibility scan (PHPCompatibility, testVersion 8.1-8.4)

Errors (fail or give wrong results on PHP 8):

- `admin/libraries/rss/feedcreator.php:1485`: `each()` was removed in PHP 8. Fatal error.
- `admin/libraries/rss/feedcreator.php:1738` and `libraries/compojoom/layout/joomla/base.php:145`: `join()` / `implode()` with the arguments in the old order. Fatal error (TypeError) on PHP 8.
- `components/com_hotspots/helpers/mappings.php:94` and CB `plug_hotspots/hotspots.php:373`: `$object->$fields[1]`. Since PHP 7 this reads `($object->$fields)[1]`, so the custom field mapping has silently returned the wrong value. Fix: `$object->{$fields[1]}`.

Warnings (deprecations on PHP 8.4):

- Implicitly nullable parameters: `tables/marker.php:334` and 7 places in `libraries/compojoom/cssmin/cssmin.php`.
- Optional parameter before required: `feedcreator.php:266`.
- `gethotspotsTab::__constructor` in the legacy CB plugin (double underscore name, never called as a constructor).

The scan cannot see runtime-only issues, mainly dynamic properties (deprecated since PHP 8.2). Those are covered by the rewrite (declared properties) and by testing with `E_ALL` on PHP 8.4.

To rerun: the checker is installed in the session scratchpad; reinstall with `composer require --dev squizlabs/php_codesniffer:^4.0 phpcompatibility/php-compatibility:dev-develop` and run `vendor/bin/phpcs --standard=PHPCompatibility --runtime-set testVersion 8.1-8.4 --extensions=php site`.

## Phase 0: Decide and set up

- [ ] Answer the open questions below.
- [ ] Local test site: Joomla 5.4.10 on PHP 8.4 with a copy of the live database and `images/`, error reporting on Maximum, compat plugin still on.
- [ ] List what the client site uses (menu items, modules, plugins, admin screens, CB integration via `the client's CB plugin`) and write a manual test checklist from it. This list decides what we port and what we drop.
- [ ] Baseline screenshots / HTML of the map pages to compare against later (the Joomla-Playwright-TestSuite repo could hold this).

## Phase 1: PHP 8.4 hotfix on the current code (ship to live)

Small, safe fixes in the existing code, marked `TLWebdesign patch`, so the live site is clean while the rewrite happens.

- [x] Fix the 5 errors and 10 warnings above (except the legacy CB plugin, dropped in phase 2).
- [x] Replace `JError::raiseWarning` in the admin entry point, `JException` in `tables/kmls.php` and `JSubMenuHelper` with what Joomla 5 offers. Also `JApplication::stringURLSafe()` and `isAdmin()`.
- [x] Security fixes found on the way (see README, "Local changes"): file delete/move/write through `../`, SQL injection in the map JSON and category API, missing CSRF tokens in admin tasks.
- [x] Full security review of the frontend code; fixed all 11 findings (see README).
- [ ] Run the site with `E_ALL` on PHP 8.4, click through the checklist, fix what shows up in the log (needs the test site from phase 0).
- [ ] Copy to the live site, check. **The security fixes should go live as soon as possible.**
- Note: the live map uses `media/com_hotspots/js/v4` (tiles), not the MooTools files in `js/moo`.
- Note: the marker API (`view=api`) has no CSRF token and no callers in this code (it served Compojoom's mobile app). Remove it in phase 6 unless something uses it.

## Phase 2: Trim

Smaller code base before the rewrite.

- [x] Drop integrations that are switched off: AlphaUserPoints, JomSocial (also `hotspotslinks/community`, `jomsocial_rule.xml`), K2, FlexiContent, SobiPro, Matukio, hotspotsanywhere, email, with their language files.
- [x] Drop the search plugin: com_search no longer exists since Joomla 4. Replace with a Smart Search plugin later only if needed.
- [x] Drop the legacy CB `plug_hotspots`.
- [x] Drop the Compojoom-only parts: JED controller, stats model (sent data to stats.compojoom.com), update check and "reload update" tasks. The dashboard no longer calls compojoom.com (update check, JED prompt, news feed) and no longer re-adds the compojoom.com update site on every visit (`refreshUpdateSite()`).
- [x] Map which parts of `lib_compojoom` Hotspots really calls (below). Deleted the dead ones: `testing`, `mvc`, `geolocation`, `layouts/jed`, `layouts/update`.
- [ ] Still in the code, to decide: the SobiPro and Phoca importers (Components → Hotspots → Import), the AUP and CB parts of `script.php` / `libraries/compojoom/installer` (go with the new install script in phase 3), the avatar/profile providers for JomSocial, K2, Kunena and EasySocial (keep CB).
- [ ] On the live site: uninstall the removed plugins and the legacy CB plugin (Extensions → Manage), and delete the compojoom.com update site.

### What Hotspots uses of lib_compojoom (2026-11-02)

Found by following class names and file paths from the Hotspots code, plus what is loaded by name (form fields through `addfieldpath`, layouts by id, tables through `JTable::getInstance(..., 'CompojoomTable')`, avatar/profile providers by setting).

| Part | Used for | Replace with |
|---|---|---|
| `autoloader`, `include.php` | Loading `Compojoom*` classes | Component namespace |
| `controller` (controller, customfield, multimedia) | Base controllers, file upload endpoint | Joomla `BaseController` / `FormController` in the component |
| `model` (customfield(s), multimedia, update) | Custom fields, file uploads, Compojoom update model | Component models; `update` goes with the Joomla update server |
| `table` (customfield, multimedia) | `#__compojoom_customfields`, `#__compojoom_multimedia` | Component tables (keep the database tables) |
| `database` + `sql/xml` | Schema install/update from XML | Plain SQL install/update files (phase 3) |
| `installer` (installer, cb, aup) | Install script helpers | Joomla `InstallerScript` (phase 3) |
| `form/fields` (avatars, cmultimedia, customfields, explanation, loadcompojoom, profiles) | Config and edit forms | Component form fields |
| `layout`, `layouts` (fileupload, galleria, social/shariff share) | Layout file loader, upload widget, gallery, share buttons | Joomla `FileLayout` + component layouts |
| `html` (behavior, feed, ctemplate, string, html) | Admin page header/footer, CSS/JS helpers | WebAssetManager, Atum markup |
| `image`, `galleria` | Thumbnails, image gallery | Joomla `Image` class, a small gallery script |
| `avatars`, `profiles` | User avatar and profile link (CB on this site) | One CB provider in the component |
| `helper`, `utils`, `query`, `language`, `template`, `changelog`, `ogp`, `component` | Small helpers | Joomla APIs or a few component helpers |
| `cssmin`, `minifier`, `shrink` | Minifying CSS/JS at runtime | Ship minified files in the build instead |

## Phase 3: Package and release setup

- [x] Installable package built from the `site/` mirror: `php build/build.php` makes `dist/pkg_hotspots-<version>.zip` with lib_compojoom, the component, the module, the 4 kept plugins and the French language files. The build reads each manifest, copies the files from where Joomla installs them and fails when a listed file is missing.
- [x] Our own update server: `updates.xml` in the repo root, `<updateservers>` in `build/pkg_hotspots.xml`. The package script removes the compojoom.com update sites of the Hotspots extensions on install and update.
- [x] Version **6.1.0** for the release with the phase 1 and 2 fixes (lib_compojoom 5.4.0). 7.0.0 stays for the refactored release.
- [x] `sql/install.mysql.utf8.sql` and `sql/updates/mysql/6.1.0.sql` replace the XML schema installer (removed, with `checkAndFixDatabase()` on the dashboard and the Hotspots 4 category migration). Same tables and columns, so no data migration. No uninstall SQL: tables are only dropped when "complete uninstall" is set (as before), by the install script.
- [x] New install script (`InstallerScript`): Joomla 5.4+ and PHP 8.1+, removes the files dropped since 6.0.13, fills in missing option defaults from `config.xml`. It no longer reinstalls the legacy CB plugin. The package script enables the category and content link plugins on a fresh install, and on update uninstalls the dropped Compojoom plugins if they are disabled.
- [x] GitHub Actions: `ci.yml` (lint on PHP 8.1 and 8.4 including deprecations, PHPCompatibility 8.1-8.4, test build), `release.yml` (tag `V6.1.0` → build, GitHub release, entry in `updates.xml`). PHPStan comes with phase 4; on the Joomla 3 style code it would only report the legacy classes.
- [x] Tested on a throwaway Joomla 5.4.10 on PHP 8.4 (MySQL 9.5, error reporting maximum): fresh install, update without a schema version (the live situation), saving a marker in the admin, and 19 admin and site pages and JSON endpoints load without any PHP notice. Found and fixed on the way: saving a marker failed on a fresh InnoDB table (`params` NOT NULL without default) and four PHP 8 notices.
- [ ] **The repo is private**, so Joomla cannot fetch `updates.xml` or the release zip. Either make the repo public (Hotspots is GPL) or host `updates.xml` and the zips somewhere public; until then, install updates by uploading the zip.
- [ ] First release: tag `V6.1.0`, then install `pkg_hotspots-6.1.0.zip` on the live site (it updates the existing extensions in place).

Found while testing: with the Backward Compatibility plugin switched off, every page of the site fails (`Class "JPlugin" not found`), because the Hotspots plugins load on every request. When we get to the refactor, do the plugins first (phase 7 before 4-6), so a Hotspots problem can no longer take down the whole site.

## Phase 4: Admin component on Joomla 5/6 MVC

- [ ] `services/provider.php`, Extension class (`MVCComponent` with `BootableExtensionInterface`, `RouterServiceInterface`, `CategoryServiceInterface`), namespaced `src/`.
- [ ] Controllers: `DisplayController`, `FormController` / `AdminController` for markers, KMLs, custom fields; import controller.
- [ ] Models: `ListModel` / `AdminModel` with `forms/*.xml` and `filter_*.xml`.
- [ ] Tables: `Table` classes with typed properties, asset handling for markers.
- [ ] Views: `HtmlView` with the toolbar from `Toolbar` / `ToolbarFactory`, tmpl in Joomla 5 admin markup (Atum, Bootstrap 5), no Bootstrap 2 / jQuery UI leftovers.
- [ ] Submenu through the manifest; `access.xml`, `config.xml` checked against what is used.
- [ ] Custom form fields (`elements/`) as namespaced `Field` classes.
- [ ] Custom fields: decide between porting the Compojoom custom fields and moving to Joomla custom fields (`com_fields`, context `com_hotspots.marker`).

## Phase 5: Geocoder and shared services

- [ ] Geocoder as a service in the component (`HttpFactory`, proxy and timeouts from the global configuration, as in the current patch).
- [ ] No legacy shims for `the client's CB plugin`: it is rewritten against the 7.0 component, released together with Hotspots 7.0. Today (6.x) it uses:
  - `require_once administrator/components/com_hotspots/libraries/geocoder/geocoder.php` and `new HotspotsGeocoder()->geocode($address)` (CB registration and profile save).
  - `file_exists(components/com_hotspots/includes/defines.php)` as the "is Hotspots installed" check, and `HOTSPOTS_PICTURE_CATEGORIES_PATH`.
  - `JLoader::discover('HotspotsHelper', components/com_hotspots/helpers)` and `HotspotsHelperUtils::prepareHotspot()` for its CB profile tab.
  - The admin layout `hotspots.map.map` (`administrator/components/com_hotspots/layouts`) to draw the map in the profile tab.
  - Direct SQL on `#__hotspots_marker` and `#__hotspots_mappings` (these tables stay as they are).
  The new component offers the replacements: a geocoder service, a marker preparation helper and a map layout, all namespaced; `ComponentHelper::isEnabled('com_hotspots')` replaces the file check.
- [ ] reCAPTCHA library replaced by Joomla's captcha plugins.
- [ ] RSS through Joomla's feed document (`format=feed`) instead of `feedcreator.php`.
- [ ] Image handling, avatars and cache from `lib_compojoom` moved into the component or replaced by Joomla APIs.

## Phase 6: Site component

- [ ] Router with `RouterView` and `MenuRules` / `StandardRules` / `NomenuRules`; keep the current URLs working (or redirect).
- [ ] Views hotspots, hotspot, form, userhotspots, kmls; JSON endpoints (`format=json`) and tiles (`format=png`) as view or controller formats. Remove the HMVC helper.
- [ ] Assets through the WebAssetManager (`media/com_hotspots/joomla.asset.json`). No MooTools: keep the `v4/app.js` map if it is the current one, otherwise port the MooTools map to plain JS.
- [ ] Frontend marker submission (form view, mail) with Joomla form validation and captcha.
- [ ] Template overrides on the live site: list them and port them.

## Phase 7: Module and plugins

- [ ] `mod_hotspots_list` with `services/provider.php`, a Dispatcher and a Helper.
- [x] Kept plugins as namespaced plugins with `SubscriberInterface` and event classes (branch `7.0-dev`). With the Backward Compatibility plugin off, the site no longer fails on every page; only pages that still run old Hotspots code do.
- [ ] `onCreateLink` (hotspotslinks group) still comes as a generic event with positional arguments from the old helper; the plugins already read named arguments `linkId` and `type`. Give it an event class when the site component is rewritten.
- [x] Content plugin (map in articles) on `ContentPrepareEvent`: replaces every `{hotspots hotspot=N}`, only published markers the visitor may see.
- [ ] Content plugin: `renderMap()` still uses the old site helpers and `CompojoomLayoutFile`; switch to the new site component in phase 6.

## Phase 8: Test and release on Joomla 5

- [ ] Fresh install and update-over-6.0.13 on Joomla 5.4 + PHP 8.1 and PHP 8.4.
- [ ] Turn the Backward Compatibility plugin off on the test site; nothing should break.
- [ ] Checklist from phase 0, CB registration and profile save with `the client's CB plugin`, geocoding.
- [ ] Remove `lib_compojoom` once nothing loads it (uninstall it from the live site after the update).
- [ ] Release 7.0.0, update the live site.

## Phase 9: Joomla 6

- [ ] Install on Joomla 6.1 with compat6 off; check deprecations against `git -C ../joomla-cms diff 5.4.10 6.1.4`.
- [ ] Fix, release 7.x, then upgrade the live site to Joomla 6.

## Open questions

1. Namespace / vendor: `TLWebdesign\Component\Hotspots`? And the element name stays `com_hotspots` (it has to, to keep the data and update over 6.0.13)?
2. Which features does the client site use: KML layers, import, frontend submission, user hotspots, tiles, RSS, the list module, the content plugin?
3. Custom fields: keep Compojoom's own or move to Joomla custom fields?
4. Do other sites run Hotspots, or only the client site? That decides how much we keep for others.
5. Google Maps: keep it (API key, cost), or move to Leaflet + OpenStreetMap while the map JS is being rewritten anyway?
