# Compojoom Hotspots (maintained by TLWebdesign)

Compojoom has stopped trading. This repo holds the last versions of their extensions as used on the client site, so we can maintain and upgrade them ourselves.

## Source

Pulled from the production site of a client on 2026-11-02. Hotspots was a paid extension that was never published on GitHub, so the live site is the newest copy available.

| Extension | Version | Date |
|---|---|---|
| com_hotspots (Hotspots Professional) + module + plugins | 6.0.13 | 2023-09-19 |
| CB plugin `plug_hotspots` (legacy, disabled; replaced by a custom CB plugin of the client, in a separate private repo) | 2.7.4 | 2023-09-19 |
| lib_compojoom | 5.3.6 | 2023-09-14 (same as github.com/compojoom/lib_compojoom) |

## Layout

`site/` mirrors the Joomla root, so files can be diffed against the live site and copied back one to one. Since 2026-11-02 it no longer holds the integrations that are switched off on the site (see Notes).

## Build and release

- `php build/build.php` builds `dist/pkg_hotspots-<version>.zip` (PHP 8.1+ with the zip extension). The version comes from `build/pkg_hotspots.xml`; the component, module and plugin manifests must have the same version.
- Release: set the new version in those files, commit, and push a tag `V<version>` (for example `V6.1.0`). GitHub Actions builds the package, attaches it to a GitHub release and adds it to `updates.xml`.
- `pkg_hotspots` installs over an existing Hotspots 6.0.x or 6.1.x and updates it in place. Requires Joomla 5.4+ and PHP 8.1+.
- The package refuses to install while a Community Builder plugin still loads Hotspots 6 files (Compojoom's "CB Hotspots" `plug_hotspots`, or custom CB plugins built on it): Community Builder loads its plugin files even when they are unpublished, so its pages would fail. The message names the plugins to delete or update first.
- Do not uninstall Hotspots on a site with data: Joomla deletes a component's categories when it is uninstalled, so the markers lose their category.

Left out: `media/com_hotspots/cache` (1.5 GB of generated files).

## Hotspots 7.0 (branch `7.0-dev`)

A rewrite of the 6.x code for Joomla 5 and 6, without the Backward Compatibility plugin and without lib_compojoom. Same database tables, same URLs, same map JavaScript (`media/com_hotspots/js/v4`). Conventions and the shared API are in `.claude/REFACTOR-7.md`.

- Namespaced component (`TLWebdesign\Component\Hotspots`), module (`TLWebdesign\Module\HotspotsList`) and plugins, with `services/provider.php`, MVC classes, event classes and the web asset manager.
- What lib_compojoom did (image uploads and gallery, custom fields, CB avatars and profile links, thumbnails) is now part of the component. The package no longer installs lib_compojoom; an update detaches it from the package so it can be uninstalled under Extensions: Manage (only if no other Compojoom extension needs it).
- No Compojoom branding left: TLWebdesign is the author; Compojoom is credited as the original ("Based on Hotspots by compojoom.com"), as the GPL requires keeping the original copyright notices. CSS classes are `hotspots-*` (was `compojoom-bootstrap`, gallery theme `compojoom`).
- Own tables `#__hotspots_customfields`, `#__hotspots_customfields_cats` and `#__hotspots_multimedia`. The update moves the Hotspots rows out of the shared `#__compojoom_*` tables and drops those only when no other extension has rows in them.
- All 6.1.0 security fixes are kept. Also fixed on the way: the list module showed unpublished and access-restricted markers; frontend users could set a `javascript:` "read more" link; `{hotspots}` in articles showed unpublished markers; the contact-author form now has the same mail limit as "send map".
- Dropped: the SobiPro and Phoca importers (the KML import stays), the marker API (`view=api`), the MooTools map files, avatar/profile providers other than Community Builder, CComment support, runtime minifying (`media/com_hotspots/cache`), Compojoom's dashboard feeds and update check.
- The client's CB plugin must be updated for 7.0: `HotspotsGeocoder`, `HotspotsHelperUtils` and `components/com_hotspots/includes/defines.php` are gone. Use `Administrator\Service\Geocoder`, `Administrator\Helper\MarkerHelper::prepare()` and the admin layout `hotspots.map.map`.

### Template overrides on the client site

`overrides/yootheme/html/` holds the site's template overrides for Hotspots 7, to copy to `templates/yootheme/html/` on the site. Overrides made for Hotspots 6 break the page in 7.0 (they call the removed `HotspotsHelper*` classes).

- `com_hotspots/hotspots/default_hotspot.php`: the marker in the map's list; the title links to the author's Community Builder profile. The only change compared to the Hotspots 7 template.

## Local changes on top of Compojoom's code (6.1.0)

Search for `TLWebdesign patch` to find them in 6.1.0 (tag `V6.1.0`). In 7.0 the code is rewritten; the fixes are kept as behaviour.

- **2026-11-02** `administrator/components/com_hotspots/libraries/geocoder/geocoder.php`: the Google geocoding call now uses the Joomla proxy (the server has no direct internet) and has a 3s connect / 5s total timeout. Without it, CB registration and profile saves hung for about 30 seconds and created no marker. The client's CB plugin depends on this geocoder (`HotspotsGeocoder`).
- **2026-11-02** Security fixes (not yet on the live site):
  - `libraries/compojoom/model/multimedia.php`: any visitor could delete any file on the server (`../` in `file`), and frontend users could move any file into the public images folder (`jform[picture]`).
  - `components/com_hotspots/api/marker.php`: image name and content from the request were written to disk as is (any file, including PHP).
  - `components/com_hotspots/models/json.php` (`c`) and `api/category.php` (`catid`, `order`): SQL injection by any visitor.
  - Admin: CSRF token and permission checks on marker delete/publish/geocode, KML delete (also SQL injection) and tile delete; no `../` in the KML import delete.
  - Second round (from a full review of the frontend code):
    - `view=userhotspots`: SQL injection through the category filter, and guests saw all markers (also unpublished ones). Users could list other users' markers.
    - Image delete: users without any delete permission (guests too) could delete any marker's images. Uploads are now limited to real images.
    - Marker API `save` (`view=api`): disabled. It had no CSRF check and stored raw data (XSS, setting owner, state and access).
    - `mail.send` ("send map by mail") was an open mail relay: it now needs the setting on, sends from the site address with the visitor as Reply-To, accepts only links to this site, and allows 5 mails per session per hour.
    - Contact author mail: settings and allowed groups are now checked on the server; it redirects to the marker instead of the HTTP referer.
    - Marker detail page and JSON: access level and published state are checked.
    - Frontend marker form: owner, dates, access and permissions can no longer be set from the form; new markers only in categories where the user may create; `form.save` (unused, unchecked) is blocked.
    - Escaped marker titles, addresses and image titles in the admin list, the detail page and the list module (stored XSS).
    - Admin KML import: CSRF tokens and no `../`; category sample icons: no `../`.
    - Form filters use `\Joomla\CMS\Component\ComponentHelper::filterText` (the `JComponentHelper` alias is gone without the compat plugin).
- **2026-11-02** PHP 8 / Joomla 5 fixes: `each()` and old `join()`/`implode()` argument order (fatal on PHP 8), implicitly nullable parameters (PHP 8.4), `$object->$fields[1]` in the custom field mappings (wrong value since PHP 7), and `JError`, `JException`, `JSubMenuHelper`, `JApplication::stringURLSafe()`, `isAdmin()` (removed in Joomla 4).

## Notes

- The update server (compojoom.com, ARS stream id 4) is still listed in Joomla under Update Sites. Installing `pkg_hotspots` 6.1.0 removes it.
- Integrations installed but switched off on the site: AlphaUserPoints, JomSocial, K2, FlexiContent, SobiPro, Matukio, search, email, hotspotsanywhere. Removed from this repo on 2026-11-02 together with the legacy CB plugin; uninstall them on the site too.
