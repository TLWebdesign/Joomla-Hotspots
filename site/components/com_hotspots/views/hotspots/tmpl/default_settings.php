<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       20.10.2014
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/com_hotspots/js/v4/apps/settings/settings_app.js',
		'media/com_hotspots/js/v4/apps/settings/show/show_controller.js',
		'media/com_hotspots/js/v4/apps/settings/show/show_view.js',
	)
);
?>

<script type="text/template" id="hs-settings-template">
	<div class="input-prepend control-group">
		<span class="btn add-on js-hs-mylocation"><span class="fa fa-crosshairs" title="<?php echo JText::_('COM_HOTSPOTS_FIND_MY_LOCATION'); ?>"></span></span>
		<input type="text" name="mylocation" placeholder="<?php echo JText::_('COM_HOTSPOTS_MY_LOCATION'); ?>"/>
	</div>
	<?php if(HOTSPOTS_PRO): ?>
		<label class="checkbox">
			<input type="checkbox" class="js-hs-stickit" name="weather"
				   value="1" <?php echo HotspotsHelperSettings::get('weather_layer', 0) ? 'checked' : '' ?> />
			<?php echo JText::_('COM_HOTSPOTS_WEATHER'); ?>
		</label>
	<?php endif; ?>


	<label class="checkbox">
		<input type="checkbox" class="js-hs-stickit" name="traffic"
		       value="true" <?php echo HotspotsHelperSettings::get('traffic_layer', 0) ? 'checked' : '' ?>>
		<?php echo JText::_('COM_HOTSPOTS_TRAFFIC'); ?>
	</label>

	<label class="checkbox">
		<input type="checkbox" name="transit" class="js-hs-stickit"
		       value="1" <?php echo HotspotsHelperSettings::get('transit_layer', 0) ? 'checked' : '' ?> />
		<?php echo JText::_('COM_HOTSPOTS_TRANSIT'); ?>
	</label>

	<label class="checkbox">
		<input type="checkbox" name="bicycle" class="js-hs-stickit"
		       value="1" <?php echo HotspotsHelperSettings::get('bicycling_layer', 0) ? 'checked' : '' ?> />
		<?php echo JText::_('COM_HOTSPOTS_BICYCLING'); ?>
		<span class="hs-bike small muted">
			<span class="hs-bike-trail"></span> <?php echo JText::_('COM_HOTSPOTS_TRAILS'); ?>
		</span>
		<span class="hs-bike small muted">
			<span class="hs-bike-path"></span> <?php echo JText::_('COM_HOTSPOTS_DEDICATED_LANES'); ?>
		</span>
		<span class="hs-bike small muted">
			<span class="hs-bike-friendly-road"></span> <?php echo JText::_('COM_HOTSPOTS_BICYCLE_FRIENDLY_ROADS'); ?>
		</span>
	</label>

	<?php if(count($this->modules['hotspots_below_settings'])): ?>
		<div class="hs-modules-below-settings">
			<?php echo HotspotsHelperModules::renderModules($this->modules['hotspots_below_settings']); ?>
		</div>
	<?php endif; ?>
</script>