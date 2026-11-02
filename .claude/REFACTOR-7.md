# Hotspots 7.0 refactor: conventions and work split

Branch `7.0-dev`. Goal: the component runs on Joomla 5.4 and 6.x without the Backward Compatibility plugin and without lib_compojoom. See `CLAUDE.md` for the PHP rules and the Joomla reference code.

## Conventions

- Namespace `TLWebdesign\Component\Hotspots\Administrator\...` (folder `administrator/components/com_hotspots/src`) and `...\Site\...` (`components/com_hotspots/src`). Both are autoloaded on both clients.
- Joomla 5 layout: `src/{Controller,Model,View/<Name>/HtmlView.php,Table,Field,Helper,Service}`, templates in `tmpl/<view>/default.php`, forms in `forms/*.xml` (loaded with `addfieldprefix="TLWebdesign\Component\Hotspots\Administrator\Field"`), menu item XML in `tmpl/<view>/default.xml`.
- Copy patterns from core `com_contact` / `com_content` at tag 5.4.10 (`git -C ../joomla-cms show 5.4.10:<path>`).
- No `J`-classes, no `jimport`, no `JLoader::discover`, no lib_compojoom (`Compojoom*`), no `HOTSPOTS_*` constants (Pro is always on), no MooTools. Declared and typed properties, `?Type $x = null`.
- Database: query builder with `bind()` / `whereIn()`; never put request values in SQL strings.
- Every state-changing task checks the CSRF token (`$this->checkToken()`) and a permission.
- Output: escape with `$this->escape()` / `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. `MarkerHelper::formattedAddress()` already escapes its parts.
- Assets: `media/com_hotspots/joomla.asset.json` (names `com_hotspots.*`). jQuery through `$wa->useScript('jquery')`, Font Awesome through `$wa->useStyle('fontawesome')` (the J5 `fa` classes work). Files that lived in `media/lib_compojoom` move to `media/com_hotspots/vendor/<name>/`. No runtime minifying or concatenating (CompojoomHtml::script and the `media/com_hotspots/cache` files are gone); the JS config goes in with `$document->addScriptOptions('com_hotspots.config', ...)` plus a small inline `var HotspotsConfig = Joomla.getOptions(...)` where the old JS expects a global.
- Branding: TLWebdesign is the author. Keep the GPL credit `(C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) <years>` in headers of files derived from Compojoom code; no other Compojoom names (classes, CSS, tables, texts).
- Language strings stay in `language/en-GB/en-GB.com_hotspots.ini` (site) and `administrator/language/en-GB/en-GB.com_hotspots(.sys).ini`.
- Old files are deleted in the same change that replaces them (git rm). Fixes marked `TLWebdesign patch` must survive the port (the behaviour, not the comment).
- The client's CB plugin gets rewritten against 7.0: no shims for it, but keep the public helpers below stable.

## Shared API (keep these signatures)

Administrator\Helper:
- `SettingsHelper::get(string $key, mixed $default = null)`, `::getAll(): Registry` (component options merged with the active Hotspots menu item).
- `MarkerHelper::prepare(object $marker): object` (params as Registry, postdate, link, short description), `::link()`, `::markerUrl()`, `::formattedAddress(object, bool $itemprop = false)`, `::localDate(?string)`, `::iconBaseUrl()`.
- `SecurityHelper::authorise(string $action, ?object $marker = null, ?User $user = null)`, `::groupHasAccess(array, array)`.
- `GalleryHelper::getData(int|array $itemIds, bool $json = false, string $small = 'small', string $normal = 'large'): array|string|false`, `::loadAssets()`.
- `CustomfieldsHelper::getFields(int|array $catids = []): array` (slug => field), `::formXml(array $fields): string`, `::addToForm(Form $form, int|array $catids, string|array|null $values = null)`, `::render(object $field, mixed $value): string` (escaped).
- `UserHelper::getUsers(array $ids): array` (id => {id, name, username, avatar, profile}; CB or nothing).
- `Model\MultimediaModel`: uploadTmp, uploadPermanent, updateExistingFiles, permanentlyMoveFiles, getFilesFromDb, saveInDb, delete, deleteAll, getFiles, getFilePath, getWebFilePath. `Model\HotspotModel::saveMultimedia(int $id, array $files, array $meta)`.
- Layouts (administrator): `hotspots.map.map` (map of many markers, fires `onHotspotClick`), `multimedia.fileupload`, `gallery.galleria`.
- `Service\Geocoder`: `geocode(string $address): array|false`, `reverseGeocode(float $lat, float $lng): array|false` (same array shapes as the old `HotspotsGeocoder`).

Site\Helper:
- `RouteHelper::getHotspotRoute($id, $catid = 0)`, `::findItem(int $id = 0, int $catid = 0): ?int`.
- `MapHelper`: `getConfig(array $overrides)`, `addConfig(WebAssetManager, array $overrides)` (script option `com_hotspots.config`, `js/config.js` makes it `HotspotsConfig`), `registerGoogleMaps(WebAssetManager, string $callback = '')`, `getGmapsUrl()`, `loadSingleMap(array $marker, array $overrides)`, `loadJsStrings()`, `getCategories()`, `getCategoriesInfo()`, `prepareCategory()`, `renderNestedCategories()`.
- `JsonHelper::marker(object $prepared, array $users = [], array $customFields = [])` (the marker JSON the v4 app reads), `::customFields(array $catids)`.
- `TileHelper::cacheFolder()` (site cache folder `com_hotspots.tiles`).
- Layouts (site): `hotspots.single-map` (one marker; used by the marker page and the content plugin), `customfields.default`, `mail.contact`.

Events: `onCreateLink` (group hotspotslinks) is dispatched by `MarkerHelper::link()` as a `Joomla\Event\Event` with arguments `linkId`, `markerId`, `type`, `result`.

## Work packages

| Package | Scope |
|---|---|
| L: lib replacements | Multimedia (table `#__hotspots_multimedia`, was `#__compojoom_multimedia`, model, admin and site `MultimediaController`, upload form field, upload layout and JS), custom fields (table `#__compojoom_customfields`, admin views customfields/customfield, form XML generation and rendering, site raw view that loads the fields for a category), gallery data and assets, user names/avatars/profile links (CB and none), image thumbnails |
| A: admin markers | Views hotspots/hotspot, controllers, models, `MarkerTable`, `forms/marker.xml` + filter form, form fields (category marker icon, category, colour, language, link, marker image, modal marker select), category form `forms/category.xml`, geocode toolbar and modal, batch, admin tiles task, `Service\Geocoder` |
| B: admin rest | DisplayController (default view dashboard), dashboard, KMLs (views, controllers, model, `KmlTable`), import (KML import only; SobiPro and Phoca importers dropped), `config.xml` and `access.xml` cleanup, admin map layout `hotspots.map.map`, `script.php` for 7.0 (removed files and folders, lib_compojoom no longer needed) |
| C: site map | Site DisplayController (default view hotspots), router (`Site\Service\Router`, keep current URLs), map view hotspots and its templates, JSON endpoints (markers, search, KMLs), tiles, RSS, `MapHelper` (JS options, Google Maps URL, JS strings, category info, user location), the single marker map layout and the content plugin's `renderMap()`, `joomla.asset.json` |
| D: site marker pages | Views hotspot (detail, contact the author), form (frontend submission, Joomla captcha), userhotspots, `HotspotController` / form save, mail controller (send map, contact), drop the `view=api` endpoint, `mod_hotspots_list` (Dispatcher + Helper) |
