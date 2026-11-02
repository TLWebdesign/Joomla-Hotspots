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

compojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/com_hotspots/js/v4/apps/infowindow/infowindow_app.js',
		'media/com_hotspots/js/v4/apps/infowindow/show/show_controller.js',
		'media/com_hotspots/js/v4/apps/infowindow/show/show_view.js',
	)
);

?>

<script type="text/html" id="infowindow-marker">
	<i class="js-hs-close hs-close fa fa-times pull-right"></i>

	<h3>{{ title }}</h3>
		<div class="hs-hotspots-overview">
			<div class="hs-place-summary-details small muted">
				<?php if(HotspotsHelperSettings::get('show_date', 1)): ?>
		<span title="<?php echo JText::_('COM_HOTSPOTS_POSTED_ON'); ?>">
							<span class="fa fa-clock-o"></span>
							{{ date }}
						</span>
				<?php endif; ?>
	<?php if(HotspotsHelperSettings::get('show_author', 1)) : ?>
		<span title="<?php echo JText::_('COM_HOTSPOTS_POSTED_BY'); ?>">
						<span class="fa fa-user"></span>
						 {{ created_by }}
					 </span>
	<?php endif; ?>
	</div>
	{{{description}}}
	</div>
	<?php if (HotspotsHelperSettings::get('show_address', 1)): ?>
		<?php echo $this->loadTemplate('address'); ?>
	<?php endif; ?>
	<?php if(
		HotspotsHelperSettings::get('show_zoom_button', 1) ||
		HotspotsHelperSettings::get('hotspot_detailpage', 1)
	) : ?>
		<div class="hs-toolbar">
			<?php if(HotspotsHelperSettings::get('show_zoom_button', 1)) : ?>
				<button class="btn btn-link btn-mini js-hs-zoom-in">
					<?php echo JText::_('COM_HOTSPOTS_ZOOM'); ?>
				</button>
			<?php endif; ?>
			<?php if(HotspotsHelperSettings::get('hotspot_detailpage', 1)): ?>
				<span class="hs-toolbar-separator"></span>
				<a href='{{ readmore }}' class="btn btn-link btn-mini">
					<?php echo JText::_('COM_HOTSPOTS_READ_MORE'); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</script>