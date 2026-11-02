<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

/**
 * The map of one marker.
 *
 * $displayData:
 * - hotspot       array  id, latitude, longitude, title (plain text), icon (URL)
 * - addressArray  array  address parts (plain text), shown below the map
 * - params        Registry|array  map_height, zoom (optional)
 */
$hotspot = $displayData['hotspot'];
$params  = $displayData['params'] ?? [];
$params  = $params instanceof Registry ? $params : new Registry($params);

MapHelper::loadSingleMap(
    [
        'id'    => (int) $hotspot['id'],
        'lat'   => (float) $hotspot['latitude'],
        'lng'   => (float) $hotspot['longitude'],
        'title' => (string) $hotspot['title'],
        'icon'  => (string) ($hotspot['icon'] ?? ''),
    ],
    [
        'mapHeightSingle'    => (int) $params->get('map_height', SettingsHelper::get('map_height_single', 300)),
        'mapStartZoomSingle' => (int) $params->get('zoom', SettingsHelper::get('map_startzoom_single', 15)),
    ]
);
?>

	<script type="text/template" id="js-hs-main-region">
		<div id="js-map-region" class="hs-map-region"></div>
	</script>

	<script type="text/template" id="main-map-template">
		<div id="map-container"></div>
	</script>

	<script type="text/template" id="js-hs-item-direction">
		<div class="js-hs-get-directions hs-get-directions text-right">
			<div class="input-prepend  input-append control-group visible-phone">
				<span class="add-on hs-quick-dir active"
				      data-id="from"><?php echo Text::_('COM_HOTSPOTS_FROM'); ?></span>
				<input type="text" value="" class="input-min js-hs-input-dir-phone"
				       placeholder="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"
				       style="width:125px"
				       title="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>">
				<button class="btn js-hs-search"><span class="fa fa-search"></span></button>
			</div>
			<div class="input-prepend input-append control-group hidden-phone">
				<span class="add-on hs-quick-dir" data-id="to"><?php echo Text::_('COM_HOTSPOTS_TO'); ?></span>
				<span class="add-on hs-quick-dir active"
				      data-id="from"><?php echo Text::_('COM_HOTSPOTS_FROM'); ?></span>
				<input type="text" value="" class="input-small js-hs-input-dir-desktop" id="test"
				       placeholder="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"
				       title="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>">
				<button class="btn js-hs-search"><span class="fa fa-search"></span></button>
				<button class="btn js-hs-close"><span class="fa fa-times"></span></button>
			</div>

		</div>
		<div class="hs-directions-results"></div>
	</script>

	<script type="text/template" id="hs-fullscreen-template">
		<div class="hs-buttons-map">
			<?php if (SettingsHelper::get('resize_map', 1)): ?>
				<div class="js-hs-fullscreen-toggle"
				     title="<?php echo Text::_('COM_HOTSPOTS_CENTER_TOGGLE_FULLSCREEN'); ?>">
					<span class="fa fa-expand"></span>
				</div>
			<?php endif; ?>
		</div>
	</script>

	<div id="hotspots-id-<?php echo (int) $hotspot['id']; ?>" class="hotspots compojoom-bootstrap">
		<div class="row-fluid">

			<div class="row-fluid">
				<div class="span12">
					<div id="" class="hs-map-card-container single-view box-shadow"
					>
						<div id="js-hs-main-app" class="hotspots hs-main-region"></div>
						<?php if (!empty($displayData['addressArray'])) : ?>
							<?php echo implode(', ', array_map(static fn ($part) => htmlspecialchars((string) $part, ENT_QUOTES, 'UTF-8'), $displayData['addressArray'])); ?>
						<?php endif; ?>
						<div id="js-hs-item-direction-region" class="text-right"></div>
					</div>
				</div>
			</div>
		</div>

	</div>
