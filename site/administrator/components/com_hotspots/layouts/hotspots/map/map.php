<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

/**
 * A simple Google map with markers; a click on a marker opens its link. Used by the dashboard
 * (and by the client's CB plugin on the CB profile).
 *
 * Fire `window.dispatchEvent(new CustomEvent('onHotspotClick', {detail: {id: 12}}))` to zoom to a marker
 * and open its info window.
 *
 * @var  array  $displayData  hotspots: list of prepared markers (MarkerHelper::prepare) with latitude,
 *                            longitude, title, icon, link, description_small and hotspots_id or id;
 *                            params: Registry (optional) with map_height (px, default 300).
 */

$app = Factory::getApplication();
$app->getLanguage()->load('com_hotspots', JPATH_ADMINISTRATOR);
$app->getLanguage()->load('com_hotspots', JPATH_SITE);

$params = ($displayData['params'] ?? null) instanceof Registry ? $displayData['params'] : new Registry($displayData['params'] ?? []);
$height = (int) $params->get('map_height', 300) ?: 300;
$apiKey = (string) SettingsHelper::get('api_key', '');

if ($apiKey === '') {
    echo '<div class="alert alert-warning">' . Text::_('COM_HOTSPOTS_API_KEY_MISSING') . '</div>';

    return;
}

$markers = [];

foreach ($displayData['hotspots'] ?? [] as $hotspot) {
    $hotspot   = (object) $hotspot;
    $markers[] = [
        'id'          => (int) ($hotspot->hotspots_id ?? $hotspot->id ?? 0),
        'lat'         => (float) ($hotspot->latitude ?? $hotspot->gmlat ?? 0),
        'lng'         => (float) ($hotspot->longitude ?? $hotspot->gmlng ?? 0),
        'title'       => (string) ($hotspot->title ?? ''),
        'icon'        => (string) ($hotspot->icon ?? ''),
        'link'        => (string) ($hotspot->link ?? ''),
        'description' => (string) ($hotspot->description_small ?? ''),
    ];
}

Text::script('COM_HOTSPOTS_READ_MORE');

$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('com_hotspots.simple-map', 'com_hotspots/simple-map.js', [], ['defer' => true]);
$wa->registerAndUseScript('com_hotspots.googlemaps.simple-map', MapHelper::getGmapsUrl('HotspotsSimpleMap.init'), [], ['defer' => true], ['com_hotspots.simple-map']);
?>
<div class="hotspots-simple-map" style="height: <?php echo $height; ?>px;"
     data-markers="<?php echo htmlspecialchars(json_encode($markers), ENT_QUOTES, 'UTF-8'); ?>"></div>
