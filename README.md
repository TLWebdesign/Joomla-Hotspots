# Compojoom Hotspots (maintained by TLWebdesign)

Compojoom has stopped trading. This repo holds the last versions of their extensions as used on the client site, so we can maintain and upgrade them ourselves.

## Source

Pulled from the live site (`the web root` on the client's server) on 2026-11-02. Hotspots was a paid extension that was never published on GitHub, so the live site is the newest copy available.

| Extension | Version | Date |
|---|---|---|
| com_hotspots (Hotspots Professional) + module + plugins | 6.0.13 | 2023-09-19 |
| CB plugin `plug_hotspots` (legacy, disabled; replaced by our own `the client's CB plugin`, repo a separate private repo) | 2.7.4 | 2023-09-19 |
| lib_compojoom | 5.3.6 | 2023-09-14 (same as github.com/compojoom/lib_compojoom) |

## Layout

`site/` mirrors the Joomla root, so files can be diffed against the live site and copied back one to one. Since 2026-11-02 it no longer holds the integrations that are switched off on the site (see Notes).

Left out: `media/com_hotspots/cache` (1.5 GB of generated files).

## Local changes on top of Compojoom's code

Search for `TLWebdesign patch` to find them.

- **2026-11-02** `administrator/components/com_hotspots/libraries/geocoder/geocoder.php`: the Google geocoding call now uses the Joomla proxy (the server has no direct internet) and has a 3s connect / 5s total timeout. Without it, CB registration and profile saves hung for about 30 seconds and created no marker. `the client's CB plugin` depends on this geocoder (`HotspotsGeocoder`).
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

- The update server (compojoom.com, ARS stream id 4) is still listed in Joomla under Update Sites. Disable it once we publish our own builds.
- Integrations installed but switched off on the site: AlphaUserPoints, JomSocial, K2, FlexiContent, SobiPro, Matukio, search, email, hotspotsanywhere. Removed from this repo on 2026-11-02 together with the legacy CB plugin; uninstall them on the site too.
