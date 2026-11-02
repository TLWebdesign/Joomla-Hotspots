# Hotspots refactor roadmap

Goal: Hotspots runs natively on Joomla 5.4 and 6.x, on PHP 8.1 to 8.4, without the Backward Compatibility plugin and without lib_compojoom. The client site runs Joomla 5.4.10 (2026-11-02).

Rules for every phase are in `CLAUDE.md` (target, reference code, PHP rules).

## Starting point (scan of 2026-11-02)

| Part | PHP lines | Notes |
|---|---|---|
| `com_hotspots` admin | 11,300 | 8 views, 11 controllers, 3 tables, own libraries: cache, geocoder, importers, maps, recaptcha, rss |
| `com_hotspots` site | 8,600 | views hotspots, hotspot, form, userhotspots, kmls, tile, json/jsonv4, api; HMVC helper; old router |
| `lib_compojoom` | 17,200 | Generic Compojoom framework, Joomla 3 style. Much of it is probably unused by Hotspots (cssmin, shrink, minifier, galleria, testing, ogp, ...) |
| Plugins | 2,600 | 17 plugins, most for integrations switched off on the site |
| `mod_hotspots_list` | 130 | |
| CB `plug_hotspots` | 560 | Legacy, disabled, replaced by the client's CB plugin (removed in 6.1.0) |

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
- [ ] List what the client site uses (menu items, modules, plugins, admin screens, CB integration via the client's CB plugin) and write a manual test checklist from it. This list decides what we port and what we drop.
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
- [x] 7.0.1 (2026-11-03): the package refuses to install while a CB plugin still loads Hotspots 6 files; first 7.x release through GitHub, so sites get updates in Joomla.
- [x] Repo made public on 2026-11-03 (TLWebdesign/Joomla-Hotspots); Joomla can fetch `updates.xml` and the release zips. The client site runs 7.0.0 since 2026-11-02.
- [ ] First release: tag `V6.1.0`, then install `pkg_hotspots-6.1.0.zip` on the live site (it updates the existing extensions in place).

Found while testing: with the Backward Compatibility plugin switched off, every page of the site fails (`Class "JPlugin" not found`), because the Hotspots plugins load on every request. When we get to the refactor, do the plugins first (phase 7 before 4-6), so a Hotspots problem can no longer take down the whole site.

## Phases 4-7: Rewrite (branch `7.0-dev`, done 2026-11-02)

Done in five parallel work packages (see `.claude/REFACTOR-7.md` for the split, conventions and shared API).

- [x] Component: `services/provider.php`, `HotspotsComponent` (MVC, categories, router), namespaced `src/` for administrator and site, `tmpl/`, `forms/`, `joomla.asset.json`.
- [x] Admin: dashboard, markers (list, edit, batch, geocode, modal select), KMLs, KML import, custom fields, tiles, options and permissions. Toolbar API, Atum markup, CSRF and permission checks on every task.
- [x] Shared services: `Service\Geocoder` (HttpFactory, global proxy, 3 s / 5 s timeouts), `MarkerHelper`, `SettingsHelper`, `SecurityHelper`, `MultimediaModel`, `CustomfieldsHelper`, `GalleryHelper`, `UserHelper` (CB), thumbnails with Joomla's Image class.
- [x] Custom fields: kept Compojoom's own tables and field types (no data migration). Moving to Joomla custom fields stays possible later.
- [x] Site: router (old SEF URLs keep working; also fixes `/edit/<id>` and `/userhotspots`), map view on the existing `js/v4` app, marker JSON / search / KML JSON, tiles, RSS/Atom through Joomla's feed document, marker page, frontend form with Joomla captcha plugins, own markers list, send-map and contact-author mail.
- [x] Dropped: SobiPro and Phoca importers, marker API (`view=api`), MooTools files, runtime minifying and `media/com_hotspots/cache`, reCAPTCHA library, `feedcreator.php`, avatar/profile providers other than CB, CComment.
- [x] `mod_hotspots_list`: services, Dispatcher, Helper; now checks access, state and publish dates.
- [x] Plugins: content/hotspots on the new single-map layout; `onCreateLink` is dispatched with named arguments (`linkId`, `markerId`, `type`, `result`) by `MarkerHelper::link()`.
- [x] lib_compojoom is no longer in the package; the package update detaches it so it can be uninstalled by hand.
- [x] French site strings for everything new.
- [x] Compojoom traces removed (2026-11-02): branding, CSS classes, gallery theme, option texts of removed features; own `#__hotspots_*` tables for custom fields and images (moved on update, tested with and without rows of other Compojoom extensions). Only the GPL credit lines and the cleanup code for the old Compojoom pieces mention Compojoom.
- [x] GitHub repo renamed to TLWebdesign/Joomla-Hotspots (2026-11-02); update server and download URLs changed with it.
- [x] The client's CB plugin 2.0.0 for Hotspots 7 (separate private repo, branch `hotspots-7`): uses `Service\Geocoder`, `MarkerHelper::prepare()` / `::iconBaseUrl()` and the layout `hotspots.map.map`. Tested in a harness on the test site (stub CB base class, fake geocoder): registration, address change, rule change, missing old user, failed geocoding, profile tab. Not tested in a real Community Builder yet. On update install the client's CB plugin 2.0 first, then Hotspots 7: 1.3 breaks CB pages once Hotspots 6 files are gone, while 2.0 does nothing until Hotspots 7 is there.
- [x] Template overrides on the live site: only `templates/yootheme/html/com_hotspots/hotspots/default_hotspot.php` (title links to the CB profile). It broke the map after the 7.0 update (`HotspotsHelperSettings` not found); ported to 7.0 in `overrides/yootheme/` (2026-11-02).
- [x] The map script failed when the menu item's start category no longer existed; start categories are now limited to existing, visible ones.
- [ ] Not tested yet: captcha with a captcha plugin enabled, geocoding against Google with a real API key, a backend user who is not Super User, the map with a real API key in a browser.

## Phase 8: Test and release on Joomla 5

- [x] Update 6.1.0 → 7.0.0 and fresh install on the throwaway Joomla 5.4.10 / PHP 8.4, with the Backward Compatibility plugins (compat and compat6) off: all admin and site pages and JSON endpoints load without PHP notices or untranslated strings, marker and category save work, the map JS starts without errors, SQL injection probes do nothing. PHPCompatibility 8.1-8.4 clean.
- [ ] Same on PHP 8.1.
- [ ] Copy of the live database and `images/` on the test site (phase 0), then the checklist, CB registration and profile save with the client's rewritten CB plugin.
- [ ] Before updating live: `SELECT COUNT(*) FROM #__compojoom_multimedia WHERE origin LIKE 'com_hotspots.migrate%'` must be 0 (the 7.0 update moves the rows to `#__hotspots_multimedia`) (Hotspots 4 images that were never migrated).
- [ ] Release 7.0.0 together with the client's new CB plugin, update the live site, then uninstall lib_compojoom there if nothing else uses it.

## Phase 9: Joomla 6

- [ ] Install on Joomla 6.1 with compat6 off; check deprecations against `git -C ../joomla-cms diff 5.4.10 6.1.4`.
- [ ] Fix, release 7.x, then upgrade the live site to Joomla 6.

## Open questions

1. Namespace / vendor: `TLWebdesign\Component\Hotspots`? And the element name stays `com_hotspots` (it has to, to keep the data and update over 6.0.13)?
2. Which features does the client site use: KML layers, import, frontend submission, user hotspots, tiles, RSS, the list module, the content plugin?
3. Custom fields: keep Compojoom's own or move to Joomla custom fields?
4. Do other sites run Hotspots, or only the client site? That decides how much we keep for others.
5. Google Maps: keep it (API key, cost), or move to Leaflet + OpenStreetMap while the map JS is being rewritten anyway?
