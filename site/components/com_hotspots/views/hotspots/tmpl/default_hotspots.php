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

$catObject = JCategories::getInstance('Hotspots');
$categories = $catObject->get()->getChildren();
$categories = $catObject->excludeCategories($categories);

CompojoomHtml::addScriptsToQueue('hotspots', array
	(
		'media/com_hotspots/js/v4/apps/hotspots/hotspots_app.js',
		'media/com_hotspots/js/v4/apps/hotspots/list/list_controller.js',
		'media/com_hotspots/js/v4/apps/hotspots/list/list_view.js',
		'media/com_hotspots/js/v4/apps/hotspots/tab/tab_controller.js',
	)
);

$vars = HotspotsHelperUtils::getJSVariables(true);
?>

<script type="text/template" id="hs-tab-list-layout-template">
	<div id="js-hs-tab-filter-region"></div>
	<div id="hs-tab-list-hotspots-region"></div>
	<div id="js-hs-tab-hotspots-tile-region"></div>
</script>

<script type="text/template" id="js-hs-filter-template">
	<div class="hs-filters">
		<div class="input-append row-fluid text-center">
			<input type="text" placeholder="<?php echo JText::_('COM_HOTSPOTS_SEARCH_HOTSPOTS'); ?>" name="search"/>
			<button class="btn js-hs-submit-search"><span class="fa fa-search"></span></button>
		</div>
		<div class="row-fluid hs-filters-actions muted small">
			<?php if(HotspotsHelperSettings::get('marker_count', 1)) : ?>
				{{#if total}}
				<span class="pull-right js-hs-filter-counter fa fa-map-marker">
						{{total}} <?php echo JText::_('COM_HOTSPOTS_HOTSPOTS'); ?>
					</span>
				{{/if}}
			<?php endif; ?>
			<span class="fa fa-angle-double-down pull-left js-hs-show-filter hs-toggle-show-filter">
				<?php echo JText::_('COM_HOTSPOTS_FILTERS'); ?>
			</span>
		</div>
		<div class="js-hs-active-filters hs-active-filters"></div>
		<div class="js-hs-filter-options hs-filter-options hide">
			<?php if(count($this->modules['hotspots_above_filter'])): ?>
				<div class="hs-modules-above-filter">
					<?php echo HotspotsHelperModules::renderModules($this->modules['hotspots_above_filter']); ?>
				</div>
			<?php endif; ?>
			<?php echo HotspotsHelperUtils::renderNestedCategories($categories); ?>
			<?php if(count($this->modules['hotspots_below_filter'])): ?>
				<div class="hs-modules-below-filter">
					<?php echo HotspotsHelperModules::renderModules($this->modules['hotspots_below_filter']); ?>
				</div>
			<?php endif; ?>
			<button class="btn js-hs-submit-search">
				<?php echo JText::_('COM_HOTSPOTS_SHOW_HOTSPOTS'); ?>
			</button>
		</div>
	</div>
</script>

<script type="text/template" id="js-hs-empty-list-template">
	<?php echo JText::_('COM_HOTSPOTS_NO_HOTSPOTS_IN_THE_CURRENT_VIEW_WITH_FILTERS'); ?>
</script>

<script type="text/template" id="pagination-controls">
	{{#ifCond totalPages '>' 1 }}
	<ul>
		{{#ifCond currentPage '>' 1 }}
		<li><a href="#" class="navigatable" data-page="1">&laquo;</a></li>
		<li><a href="#" class="navigatable" data-page="{{previous}}">&lsaquo;</a></li>
		{{else}}
		<li class="disabled"><a href="#" class="disabled">&laquo;</a></li>
		<li class="disabled"><a href="#" class="disabled">&lsaquo;</a></li>
		{{/ifCond}}

		{{#ifCond pageSet.[0] '>' 1 }}
		<li class="disabled"><a href="#">...</a></li>
		{{/ifCond}}

		{{#each pageSet}}
		{{#ifCond this '===' ../currentPage}}
		<li class="active disabled"><a href="#">{{this}}</a></li>
		{{else}}
		<li><a href="#" class="navigatable" data-page="{{this}}">{{this}}</a></li>
		{{/ifCond}}
		{{/each}}

		{{#each pageSet}}
		{{#if @last}}
		{{#ifCond this '!==' ../../totalPages}}
		<li class="disabled"><a href="#">...</a></li>
		<li><a href="#" class="navigatable" data-page="{{../../../totalPages}}">{{../../../totalPages}}</a></li>
		{{/ifCond}}
		{{/if}}
		{{/each}}

		{{#ifCond currentPage '!==' totalPages}}
		<li><a href="#" class="navigatable" data-page="{{next}}">&rsaquo;</a></li>
		<li><a href="#" class="navigatable" data-page="{{totalPages}}">&raquo;</a></li>
		{{else}}
		<li class="disabled"><a href="#">&rsaquo;</a></li>
		<li class="disabled"><a href="#">&raquo;</a></li>
		{{/ifCond}}

	</ul>
	{{/ifCond}}
</script>

<script type="text/template" id="paginated-view">
	<?php if(count($this->modules['hotspots_above_list'])): ?>
		<div class="hs-modules-above-list">
			<?php echo HotspotsHelperModules::renderModules($this->modules['hotspots_above_list']); ?>
		</div>
	<?php endif; ?>
	<div id="js-pagination-controls-top" class="js-pagination-controls text-center"></div>
	<div class="js-pagination-main"></div>
	<div id="js-pagination-controls-bottom" class="js-pagination-controls text-center"></div>
	<?php if(count($this->modules['hotspots_below_list'])): ?>
		<div class="hs-modules-below-list">
			<?php echo HotspotsHelperModules::renderModules($this->modules['hotspots_below_list']); ?>
		</div>
	<?php endif; ?>
</script>