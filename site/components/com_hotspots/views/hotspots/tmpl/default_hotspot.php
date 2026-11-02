<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       29.09.2014
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');


?>

<script type="text/template" id="hotspot-list-item">
	<div class="hs-place-summary">
		{{#if multimedia}}
			<div class="hs-galleria"></div>
		{{/if}}

		<h3>{{ title }}</h3>

		<div class="hs-hotspots-overview-short">
			{{{cutDescription}}}
		</div>
		<div class="hs-hotspots-overview">
			<div class="hs-place-summary-details small muted">
				<?php if(HotspotsHelperSettings::get('show_date', 1)): ?>
					<span title="<?php echo JText::_('COM_HOTSPOTS_POSTED_ON'); ?>">
						<span class="fa fa-clock-o"></span>
						{{ date }}
					</span>
				<?php endif; ?>
				<?php if(HotspotsHelperSettings::get('show_author', 1)) : ?>
					{{#if avatar }}

						<span title="<?php echo JText::_('COM_HOTSPOTS_POSTED_BY'); ?>"
						      class="js-hs-place-user-with-avatar">
							<img src='{{avatar}}' class="hs-place-avatar-small"/>
						    {{ created_by }}
					    </span>
						<div class="js-hs-place-avatar-hidden hide">
							<img src='{{avatar}}' class="hs-place-avatar"/>
							{{#if profile}}
								<a href='{{profile}}'>{{created_by}}</a>
							{{else}}
								{{created_by}}
							{{/if}}
						</div>
					{{else}}
						<span title="<?php echo JText::_('COM_HOTSPOTS_POSTED_BY'); ?>">
							<span class="fa fa-user"></span>
							{{ created_by }}
						 </span>
					{{/if}}
				<?php endif; ?>
			</div>
			{{{description}}}

			{{#if customfields}}
				<div class="hotspots-customfields">
					<h6><?php echo JText::_('COM_HOTSPOTS_CUSTOM_FIELDS'); ?></h6>
					{{{customfields}}}
				</div>
			{{/if}}
		</div>
		<?php if (HotspotsHelperSettings::get('show_address', 1)): ?>
			<?php echo $this->loadTemplate('address'); ?>
		<?php endif; ?>
		<?php if (HotspotsHelperSettings::get('show_coordinates', 0)): ?>
			<div class="hs-coordinates small muted">
				<?php echo JText::_('COM_HOTSPOTS_LATITUDE'); ?>: {{lat}} | <?php echo JText::_('COM_HOTSPOTS_LONGITUDE'); ?>: {{lng}}
			</div>
		<?php endif; ?>
		<?php if (HotspotsHelperSettings::get('show_streetview', 1)): ?>
			<div class="js-hs-streetmap hs-streetmap" style="display: none;">
				<label><?php echo JText::_('COM_HOTSPOTS_STREET_VIEW'); ?></label>
			</div>
		<?php endif; ?>
		<?php if(HotspotsHelperSettings::get('show_marker_directions', 1) ||
			HotspotsHelperSettings::get('show_zoom_button', 1) ||
			HotspotsHelperSettings::get('hotspot_detailpage', 1)
		) : ?>
			<div class="hs-toolbar">
				<?php if(HotspotsHelperSettings::get('show_marker_directions', 1)): ?>
					<button class="btn btn-link btn-mini js-hs-show-directions">
						<?php echo JText::_('COM_HOTSPOTS_DIRECTIONS'); ?>
					</button>
				<?php endif; ?>
				<?php if(HotspotsHelperSettings::get('show_zoom_button', 1)) : ?>
					<span class="hs-toolbar-separator"></span>
					<button class="btn btn-link btn-mini js-hs-zoom-in">
						<?php echo JText::_('COM_HOTSPOTS_ZOOM'); ?>
					</button>
				<?php endif; ?>
					{{#if readmore }}
					<span class="hs-toolbar-separator"></span>
					<a href='{{ readmore }}' class="btn btn-link btn-mini">
						<?php echo JText::_('COM_HOTSPOTS_READ_MORE'); ?>
					</a>
					{{/if}}
			</div>
		<?php endif; ?>
		<div class="js-hs-get-directions hs-get-directions" style="display:none;">
			<div class="input-prepend input-append control-group">
				<span class="add-on hs-quick-dir" data-id="to"><?php echo JText::_('COM_HOTSPOTS_TO'); ?></span>
				<span class="add-on hs-quick-dir active" data-id="from"><?php echo JText::_('COM_HOTSPOTS_FROM'); ?></span>
				<input type="text" value="" class="input-small" placeholder="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>">
				<button class="btn js-hs-search"><span class="fa fa-search"></span></button>
				<button class="btn js-hs-close"><span class="fa fa-times"></span></button>
			</div>
		</div>
	</div>
</script>