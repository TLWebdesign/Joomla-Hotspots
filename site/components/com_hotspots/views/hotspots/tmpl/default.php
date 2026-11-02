<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       26.02.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

if (HotspotsHelperSettings::get('emulate_bootstrap', 1))
{
	JHTML::_('stylesheet', 'media/lib_compojoom/css/bootstrap-232.css');
}

JHTML::_('stylesheet', 'media/com_hotspots/css/hotspots-v4.css');
JHTML::_('stylesheet', 'media/com_hotspots/css/tooltipster.css');

if (HotspotsHelperSettings::get('show_welcome_text', 1))
{
	JHTML::_('stylesheet', 'media/com_hotspots/css/vendor/hopscotch.css');
}
if (JVERSION < 3)
{
	/**
	 * Hotspots doesn't need mootools to work. Unfortunately we have to enable
	 * mootools here, otherwise if the library is included after the google maps
	 * files, it screws the controls of the map. Tracing this it comes down to
	 * the delete Function.prototype.bind;
	 * Funny enough - although joomla 3.x comes with exactly the same version of
	 * mootools this line is missing in the moootools-core file, so we don't need
	 * to include it on joomla 3.x
	 * If you don't have mootools on your site, just delete the call here & you
	 * should be fine.
	 */
	JHtml::_('behavior.framework');
	JHTML::_('stylesheet', 'media/com_hotspots/css/hotspots-j2.5-hacks.css');
	JHTML::_('stylesheet', 'media/lib_compojoom/css/bootstrap-232.css');
}

CompojoomHtmlBehavior::jquery();
JHTML::_('script', HotspotsHelperUtils::getGmapsUrl());
// Load necessary strings for JS translations
HotspotsHelperUtils::getJsLocalization();

JHtml::stylesheet('media/lib_compojoom/third/font-awesome/css/font-awesome.min.css');
JHtml::stylesheet('media/lib_compojoom/third/galleria/themes/compojoom/galleria.compojoom.css');
JHtml::stylesheet('media/com_hotspots/images/v4/hotspots/styles.css');

$tabs = HotspotsHelperSettings::get('show_tabs', array('directions', 'hotspots', 'settings'));

CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		HotspotsHelperUtils::createJSConfig()
	)
);
CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/lib_compojoom/js/jquery.onimpression.js',
		'media/lib_compojoom/third/galleria/galleria.js',
		'media/lib_compojoom/third/galleria/themes/compojoom/galleria.compojoom.js',
		'media/com_hotspots/js/v4/vendor/handlebars-v2.0.0.js',
		'media/com_hotspots/js/v4/vendor/hopscotch.js',
		'media/lib_compojoom/js/jquery.ui.custom.js',
		'media/com_hotspots/js/v4/vendor/jquery.tooltipster.js',
		'media/com_hotspots/js/v4/vendor/jquery.cookie.js',
		'media/com_hotspots/js/v4/vendor/jquery.smoothscroll.js',
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
		'media/com_hotspots/js/v4/entities/hotspot.js',
		'media/com_hotspots/js/v4/entities/tab.js',
		'media/com_hotspots/js/v4/entities/category.js',
		'media/com_hotspots/js/v4/entities/direction.js',
		'media/com_hotspots/js/v4/entities/filter.js',
		'media/com_hotspots/js/v4/entities/marker.js',
		'media/com_hotspots/js/v4/entities/mail.js',

		'media/com_hotspots/js/v4/common/views.js',
		'media/com_hotspots/js/v4/common/map/show/show_view.js',

		'media/com_hotspots/js/v4/apps/app/app_app.js',
		'media/com_hotspots/js/v4/apps/app/main/main_view.js'
	)
);

if (HOTSPOTS_PRO)
{
	CompojoomHtml::addScriptsToQueue(
		'hotspots', array(
			'media/com_hotspots/js/v4/entities/kml.js'
		)
	);
}


if (HOTSPOTS_PRO && HotspotsHelperSettings::get('custom_tiles', 0))
{
	CompojoomHtml::addScriptsToQueue(
		'hotspots', array(
			'media/com_hotspots/js/v4/entities/tile.js',
			'media/com_hotspots/js/v4/apps/map/tiles/tiles_controller.js',
			'media/com_hotspots/js/v4/apps/map/tiles/tiles_view.js'
		)
	);
}

CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/com_hotspots/js/v4/apps/map/map_app.js',
		'media/com_hotspots/js/v4/apps/map/show/show_controller.js',
		'media/com_hotspots/js/v4/apps/map/show/show_view.js',
		'media/com_hotspots/js/v4/apps/map/weather/weather_controller.js',
		'media/com_hotspots/js/v4/apps/map/markers/markers_controller.js',
		'media/com_hotspots/js/v4/apps/map/markers/markers_view.js'
	)
);


CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
	'media/com_hotspots/js/v4/apps/share/share_app.js',
	'media/com_hotspots/js/v4/apps/share/mail/mail_controller.js',
	'media/com_hotspots/js/v4/apps/share/mail/mail_view.js',

	'media/com_hotspots/js/v4/apps/filter/show/show_view.js'
	)
);

// If any of the tabs is published, we need to load the menu and tabs files
if (HotspotsHelperSettings::get('show_tabs_directions', 1)
	|| HotspotsHelperSettings::get('show_tabs_settings', 1)
	|| HotspotsHelperSettings::get('show_tabs_hotspots', 1))
{
	CompojoomHtml::addScriptsToQueue(
		'hotspots', array(
			'media/com_hotspots/js/v4/apps/menu/menu_app.js',
			'media/com_hotspots/js/v4/apps/menu/show/show_controller.js',
			'media/com_hotspots/js/v4/apps/menu/show/show_view.js',
		)
	);
	CompojoomHtml::addScriptsToQueue(
		'hotspots', array(
			'media/com_hotspots/js/v4/apps/tabs/tabs_app.js',
			'media/com_hotspots/js/v4/apps/tabs/list/list_controller.js',
			'media/com_hotspots/js/v4/apps/tabs/list/list_view.js',
			'media/com_hotspots/js/v4/apps/tabs/tabitem/tabitem_view.js',
			'media/com_hotspots/js/v4/apps/tabs/tabcontentitem/tabcontentitem_view.js',
			'media/com_hotspots/js/v4/apps/tabs/save/save_controller.js',
			'media/com_hotspots/js/v4/apps/tabs/remove/remove_controller.js',
		)
	);
}

$this->modules = HotspotsHelperModules::getModules();
?>

<div id="hs" class="compojoom-bootstrap <?php echo $this->escape(HotspotsHelperSettings::get('pageclass_sfx')); ?>">
	<?php if (HotspotsHelperSettings::get('show_page_title', 1)) : ?>
		<div class="page-header">
			<h1> <?php echo $this->escape(HotspotsHelperSettings::get('page_heading', 'Hotspots')); ?> </h1>
		</div>
	<?php endif; ?>

	<?php if(count($this->modules['hotspots_above_map'])): ?>
		<div class="hs-modules-above-map">
			<?php echo HotspotsHelperModules::renderModules($this->modules['hotspots_above_map']); ?>
		</div>
	<?php endif; ?>
	<div id="js-hs-main-app" class="hotspots hs-main-region"></div>
	<?php if(count($this->modules['hotspots_below_map'])): ?>
		<div class="hs-modules-below-map">
			<?php echo HotspotsHelperModules::renderModules($this->modules['hotspots_below_map']); ?>
		</div>
	<?php endif; ?>
	<?php echo $this->loadTemplate('footer'); ?>
</div>

<script type="text/template" id="js-hs-main-region">
	<div id="js-menu-region" class="hs-menu-region"></div>
	<div id="js-map-region" class="hs-map-region"></div>
	<div id="js-hs-infowindow-region" class="hs-infowindow-region"></div>
</script>

<script type="text/template" id="menu-layout-template">
	<span title="Toggle the menu" class="<?php echo HotspotsHelperSettings::get('start_closed_menu', 0) ? 'toggle-off' : 'toggle-on'; ?> toggle" id="toggle-menu"></span>
	<div id="js-tabs-region" class="hs-tab-region">	</div>
</script>

<?php echo $this->loadTemplate('tabs'); ?>

<script type="text/template" id="main-map-template">
	<div id="map-container"></div>
</script>

<?php if(HotspotsHelperSettings::get('show_tabs_hotspots', 1)) : ?>
	<?php echo $this->loadTemplate('hotspots'); ?>
	<?php echo $this->loadTemplate('hotspot'); ?>
<?php endif; ?>

<?php
// Show the infowindow only when we don't have a menu
?>
<?php if(!HotspotsHelperSettings::get('show_tabs_directions', 1)
		&& !HotspotsHelperSettings::get('show_tabs_settings', 1)
		&& !HotspotsHelperSettings::get('show_tabs_hotspots', 1)) : ?>
	<?php echo $this->loadTemplate('infowindowmarker'); ?>
<?php endif; ?>

<?php if(HotspotsHelperSettings::get('show_tabs_directions', 1)) : ?>
	<?php echo $this->loadTemplate('directions'); ?>
<?php endif; ?>

<?php if(HotspotsHelperSettings::get('show_tabs_settings', 1)) : ?>
	<?php echo $this->loadTemplate('settings'); ?>
<?php endif; ?>


<script type="text/template" id="loading-view">
	<div>
		<span class="fa-li fa fa-spinner fa-spin"></span> <?php echo JText::_('COM_HOTSPOTS_LOADING_DATA'); ?>
	</div>
</script>

<script type="text/template" id="js-hs-loading-mouse-template">
	<div class="js-hs-loading-data"><span class="fa-li fa fa-spinner fa-spin"></span> <?php echo JText::_('COM_HOTSPOTS_LOADING_DATA'); ?></div>
</script>

<?php echo preg_replace("@[\\r|\\n|\\t]+@", '', $this->loadTemplate('mapbuttons')); ?>

<?php echo $this->loadTemplate('sendmap'); ?>


<?php
if (HotspotsHelperSettings::get('show_welcome_text', 1))
{
	CompojoomHtml::addScriptsToQueue(
		'hotspots', array(
			HotspotsHelperTour::addTour(),
			'media/com_hotspots/js/tour/tour.js',
		)
	);
}

// Now add all js files to the head
CompojoomHtml::script(
	CompojoomHtml::getScriptQueue('hotspots'),
	'media/com_hotspots/cache',
	HotspotsHelperSettings::get('minify', true)
);
