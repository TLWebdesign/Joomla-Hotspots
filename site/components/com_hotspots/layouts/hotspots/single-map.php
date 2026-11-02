<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       06.05.2015
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();

HotspotsHelperUtils::getJsLocalization();
CompojoomHtmlBehavior::jquery();

if (HotspotsHelperSettings::get('emulate_bootstrap', 1))
{
	JHTML::_('stylesheet', 'media/lib_compojoom/css/bootstrap-232.css');
}

JHTML::_('stylesheet', 'media/com_hotspots/css/hotspot.css');
JHTML::_('script', HotspotsHelperUtils::getGmapsUrl());

JHTML::stylesheet('media/lib_compojoom/third/font-awesome/css/font-awesome.min.css');

$hotspot = $displayData['hotspot'];
$params       = $displayData['params'];
$width        = $params->get('map_width', '') ? $params->get('map_width') . 'px' : '100%';

$marker = array(
	'id'          => $hotspot['id'],
	'lat'         => (float) $hotspot['latitude'],
	'lng'         => (float) $hotspot['longitude'],
	'title'       => $hotspot['title'],
	'icon'        => $hotspot['icon']
);

CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		HotspotsHelperUtils::createJsonMarker($marker),
		HotspotsHelperUtils::createJSConfig(array('mapHeightSingle' => $params->get('map_height', 200), 'mapStartZoomSingle' => $params->get('zoom', 12)))
	)
);
CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/com_hotspots/js/v4/vendor/handlebars-v2.0.0.js',
		'media/lib_compojoom/js/jquery.ui.custom.js',
		'media/com_hotspots/js/v4/vendor/jquery.cookie.js',
		'media/com_hotspots/js/v4/vendor/underscore.js',
		'media/com_hotspots/js/v4/vendor/backbone.js',
		'media/com_hotspots/js/v4/vendor/backbone.marionette.js',
		'media/com_hotspots/js/v4/vendor/backbone.picky.js',
		'media/com_hotspots/js/v4/vendor/backbone.stickit.js',
		'media/com_hotspots/js/v4/vendor/backbone.validator.js',
		'media/com_hotspots/js/v4/vendor/backbone.googlemaps.js',
		'media/com_hotspots/js/v4/vendor/backbone.paginator.js',
		'media/com_hotspots/js/v4/overrides/backbone.marionette.js',
		'media/com_hotspots/js/v4/app.js',

		'media/com_hotspots/js/v4/entities/map.js',
		'media/com_hotspots/js/v4/entities/direction.js',
		'media/com_hotspots/js/v4/entities/marker.js',

		'media/com_hotspots/js/v4/common/views.js',
		'media/com_hotspots/js/v4/common/map/show/show_view.js',
	)
);

CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/com_hotspots/js/v4/subapps/hotspot/map/map_app.js',
		'media/com_hotspots/js/v4/subapps/hotspot/map/show/show_controller.js',

		'media/com_hotspots/js/v4/subapps/hotspot/map/show/show_view.js',
		'media/com_hotspots/js/v4/subapps/hotspot/map/marker/marker_controller.js',
		'media/com_hotspots/js/v4/subapps/hotspot/map/marker/marker_view.js'
	)
);

if (HotspotsHelperSettings::get('show_marker_directions', 1))
{
	CompojoomHtml::addScriptsToQueue(
		'hotspots', array(
			'media/com_hotspots/js/v4/subapps/hotspot/directions/directions_app.js',
			'media/com_hotspots/js/v4/subapps/hotspot/directions/show/show_controller.js',
			'media/com_hotspots/js/v4/subapps/hotspot/directions/show/show_view.js'
		)
	);
}

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
				      data-id="from"><?php echo JText::_('COM_HOTSPOTS_FROM'); ?></span>
				<input type="text" value="" class="input-min js-hs-input-dir-phone"
				       placeholder="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"
				       style="width:125px"
				       title="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>">
				<button class="btn js-hs-search"><span class="fa fa-search"></span></button>
			</div>
			<div class="input-prepend input-append control-group hidden-phone">
				<span class="add-on hs-quick-dir" data-id="to"><?php echo JText::_('COM_HOTSPOTS_TO'); ?></span>
				<span class="add-on hs-quick-dir active"
				      data-id="from"><?php echo JText::_('COM_HOTSPOTS_FROM'); ?></span>
				<input type="text" value="" class="input-small js-hs-input-dir-desktop" id="test"
				       placeholder="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"
				       title="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>">
				<button class="btn js-hs-search"><span class="fa fa-search"></span></button>
				<button class="btn js-hs-close"><span class="fa fa-times"></span></button>
			</div>

		</div>
		<div class="hs-directions-results"></div>
	</script>

	<script type="text/template" id="hs-fullscreen-template">
		<div class="hs-buttons-map">
			<?php if (HotspotsHelperSettings::get('resize_map', 1)): ?>
				<div class="js-hs-fullscreen-toggle"
				     title="<?php echo JText::_('COM_HOTSPOTS_CENTER_TOGGLE_FULLSCREEN'); ?>">
					<span class="fa fa-expand"></span>
				</div>
			<?php endif; ?>
		</div>
	</script>

	<div id="hotspots-id-<?php echo $hotspot['id']; ?>" class="hotspots compojoom-bootstrap">
		<div class="row-fluid">

			<div class="row-fluid">
				<div class="span12">
					<div id="" class="hs-map-card-container single-view box-shadow"
					>
						<div id="js-hs-main-app" class="hotspots hs-main-region"></div>
						<?php if (count($displayData['addressArray'])) : ?>
							<?php echo implode(', ', $displayData['addressArray']); ?>
						<?php endif; ?>
						<div id="js-hs-item-direction-region" class="text-right"></div>
					</div>
				</div>
			</div>
		</div>

	</div>

<?php
// Now add all js files to the head
CompojoomHtml::script(
	CompojoomHtml::getScriptQueue('hotspots'),
	'media/com_hotspots/cache',
	HotspotsHelperSettings::get('minify', true)
);
