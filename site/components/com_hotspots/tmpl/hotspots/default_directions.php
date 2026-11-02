<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2014
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Hotspots\HtmlView $this */
?>

<script type="text/template" id="hs-directions-template">
	<div class="search-location">
		<div class="row-fluid">
			<form action="" id="js-hs-search-address-form">


				<div class="input-prepend input-append control-group">
					<span class="js-hs-clear-address-search btn btn-mini btn-link pull-right"
					      style="display:none;"><?php echo Text::_('COM_HOTSPOTS_CLEAR_SEARCH'); ?></span>
					<label for="hs-search-origin">
						<?php echo Text::_('COM_HOTSPOTS_SEARCH'); ?>
					</label>
					<span class="add-on"><span class="fa hs-drag fa-map-marker"></span></span>
					<input type="text" id="hs-search-destination" class="destination input-medium" value="{{ destination }}"
					       placeholder="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"/>
					<button class="btn js-hs-directions-search" title="Search"><span class="fa fa-search"></span></button>
					<span class="btn js-hs-show-directions" title="Get Directions"><span class="hs-icon-direction"></span></span>
				</div>
			</form>
		</div>

	</div>
	<div class="hs-search-directions hs-hide js-hs-search-directions">
		<form action="" id="js-hs-search-directions-form">
			<span
				class="fa fa-caret-left muted small pull-right js-hs-back-address hs-back-address"> <?php echo Text::_('COM_HOTSPOTS_GO_BACK'); ?></span>

			<div class="hs-routing-mode">
				<div class="hs-icon-car active" data-type="DRIVING"></div>
				<div class="hs-icon-train" data-type="TRANSIT"></div>
				<div class="hs-icon-bicycle" data-type="BICYCLING"></div>
				<div class="hs-icon-pedestrian" data-type="WALKING"></div>
			</div>
			<div class="js-hs-drag-info hs-drag-info muted small" style="display:none;">
				<span class="fa fa-reply fa-rotate-270"></span> <?php echo Text::_('COM_HOTSPOTS_DRAG_ICON_TO_REORDER_STOPS'); ?>
			</div>
			<div class="sortable">
				<div class="row-fluid">
					<div class="input-prepend input-append control-group">
						<span class="add-on"><span class="fa hs-drag fa-circle-o"></span></span>
						<input type="text" placeholder="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"/>
						<span class="add-on"><span class="fa fa-times hs-close"></span></span>
					</div>
				</div>
				<div class="row-fluid">
					<div class="input-prepend input-append control-group">
						<span class="add-on"><span class="fa hs-drag fa-map-marker"></span></span>
						<input type="text" placeholder="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"/>
						<span class="add-on"><span class="fa fa-times hs-close"></span></span>
					</div>
				</div>
			</div>

			<div class="row-fluid hs-directions-actions muted small">
				<span class="fa fa-plus js-hs-add-stop"> <?php echo Text::_('COM_HOTSPOTS_ADD_STOP'); ?></span>
				<span class="fa fa-times-circle js-hs-clear-search" style="display:none;"> <?php echo Text::_('COM_HOTSPOTS_CLEAR_SEARCH'); ?></span>
				<span class="fa fa-angle-double-down pull-right js-hs-direction-options"> <?php echo Text::_('COM_HOTSPOTS_OPTIONS'); ?></span>
			</div>

			<div class="hs-directions-options-container">
				<strong><?php echo Text::_('COM_HOTSPOTS_USE'); ?>:</strong>

				<div class="hs-units form-inline">
					<div>
						<input type="radio" id="hs-km" name="units" value="METRIC" checked/>
						<label for="hs-km"><?php echo Text::_('COM_HOTSPOTS_KILOMETERS'); ?></label>
					</div>
					<div>
						<input type="radio" id="hs-miles" name="units" value="IMPERIAL"/>
						<label for="hs-miles"><?php echo Text::_('COM_HOTSPOTS_MILES'); ?></label>
					</div>
				</div>
				<strong>
					<?php echo Text::_('COM_HOTSPOTS_AVOID_FOLLOWING'); ?>:
				</strong>

				<div class="hs-avoid">
					<label class="checkbox">
						<input type="checkbox" id="js-hs-avoid-highways" value="1"/> <?php echo Text::_('COM_HOTSPOTS_AVOID_HIGHWAY'); ?>
					</label>

					<label class="checkbox">
						<input type="checkbox" id="js-hs-avoid-tolls" value="1"/> <?php echo Text::_('COM_HOTSPOTS_AVOID_TOLLS'); ?>
					</label>
				</div>
			</div>

			<button class="btn js-hs-get-directions"><?php echo Text::_('COM_HOTSPOTS_GET_DIRECTIONS'); ?></button>
		</form>
	</div>

	<div class="clear-both"></div>
	<div class="hs-search-results"></div>
	<div class="hs-directions-results"></div>
	<?php if (count($this->modules['hotspots_below_directions'])): ?>
		<div class="hs-modules-below-directions">
			<?php echo MapHelper::renderModules($this->modules['hotspots_below_directions']); ?>
		</div>
	<?php endif; ?>
</script>

<script type="text/template" id="hs-directions-single-row-template">
	<div class="row-fluid">
		<div class="input-prepend input-append control-group">
			<span class="add-on"><span class="fa hs-drag fa-map-marker"></span></span>
			<input type="text" placeholder="<?php echo Text::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"/>
			<span class="add-on"><span class="fa fa-times hs-close"></span></span>
		</div>
	</div>
</script>

<script type="text/template" id="hs-address-result-template">
	<div class="row-fluid hs-address-result">
		<h3>{{ address }}</h3>
	</div>
</script>

