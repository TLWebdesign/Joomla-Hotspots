<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       25.08.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>

<script type="text/template" id="hs-fullscreen-template">
	<div class="hs-buttons-map">
		<?php if(HotspotsHelperSettings::get('find_position', 1)): ?>
			<div class="js-hs-geocode-center" title="<?php echo JText::_('COM_HOTSPOTS_CENTER_ON_MY_LOCATION'); ?>">
				<span class="fa fa-crosshairs"></span>
			</div>
		<?php endif; ?>

		<?php if(HotspotsHelperSettings::get('resize_map', 1)): ?>
			<div class="js-hs-fullscreen-toggle" title="<?php echo JText::_('COM_HOTSPOTS_CENTER_TOGGLE_FULLSCREEN'); ?>">
				<span class="fa fa-expand"></span>
			</div>
		<?php endif; ?>
	</div>
</script>

<script type="text/template" id="hs-action-buttons-map-template">
	<div class="hs-buttons-map">
		<?php if(HotspotsHelperSettings::get('mail_map', 0)): ?>
			<div>
				<span class="fa fa-envelope js-hs-hotspots-share-mail"></span>
			</div>
		<?php endif; ?>

		<?php if(HotspotsHelperSettings::get('print_map', 0)): ?>
			<div><span class="fa fa-print"></span></div>
		<?php endif; ?>

		<?php if(HotspotsHelperSettings::get('rss_enable', 0)): ?>
		<div>
		<a class="fa fa-rss" href="<?php echo JRoute::_('index.php?option=com_hotspots&view=hotspots&task=hotspots.rss'); ?>"
			target="_blank"
			title="<?php echo JTEXT::_('COM_HOTSPOTS_FEED'); ?>"></a>
	</div>
		<?php endif; ?>
	</div>
</script>
