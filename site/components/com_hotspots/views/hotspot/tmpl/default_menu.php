<?php
/**
 * @author Daniel Dimitrov
 * @date: 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>

<div id="slide_menu" style="<?php echo HotspotsHelperSettings::get('hs_show_controllmenu', 1) ? '' : 'display:none;'; ?>">
	<span id="toggle-menu" class="toggle-off" title="<?php echo JText::_('COM_HOTSPOTS_TOGGLE'); ?>">
	</span>
	<div class="hotspots-actions" id="hotspots-menu-actions" style="display:none">
		<label><input type="checkbox" id="all-hotspots" /><?php echo JText::_('COM_HOTSPOTS_SHOW_ALL_HOTSPOTS'); ?></label>
	</div>
	<div id="tab-container">

		<div id="hotspots-slide-tabs-back"><!--slide back button--></div>
			<div id="tab-container-inner">
				<ul class="hotspots-tabs" id="hotspots-tabs"><li class="hotspots-tab" id="tab-search" data-id="search"><span><img src="<?php echo JURI::root(); ?>/media/com_hotspots/images/utils/search.png" alt="saerch" title="search" /></span></li></ul>
			</div>
		<div id="hotspots-slide-tabs-forward"><!--slide forward button--></div>
	</div>
	<div class="clear-both"></div>

	<div class="hotspots-tab-content" id="search-tab-container" data-id="search">
		<div class="search-actions">
			<span class="active" data-id="search-directions"><?php echo JText::_('COM_HOTSPOTS_SEARCH_DIRECTIONS'); ?></span><span data-id="search-address"><?php echo JText::_('COM_HOTSPOTS_SEARCH_ADDRESS'); ?></span>
		</div>
		<form id="search-directions" action="" class="form active menu">
			<div class="" style="width:100%">
				<label for="directions-departure">
					<?php echo JText::_('COM_HOTSPOTS_START_ADDRESS'); ?>
				</label>
				<input type="text" id="directions-departure" title="<?php echo JText::_('COM_HOTSPOTS_YOUR_START_ADDRESS'); ?>" class="required" />
				<label for="directions-arrival">
					<?php echo JText::_('COM_HOTSPOTS_END_ADDRESS'); ?>
				</label>
				<input type="text" id="directions-arrival" title="<?php echo JText::_('COM_HOTSPOTS_YOUR_END_ADDRESS'); ?>" class="required"/>

				<button class="sexybutton right" type="submit">
					<span>
						<span><?php echo JText::_('COM_HOTSPOTS_GET_DIRECTIONS'); ?></span>
					</span>
				</button>
			</div>
			<div id="directions-display"></div>
		</form>
		<form id="search-address" class="form menu" action="">
			<input type="text" id="search-address-input" title="<?php echo JText::_('COM_HOTSPOTS_ADDRESS'); ?>" class="required"/>
			<button class="sexybutton right" type="submit">
				<span>
					<span><?php echo JText::_('COM_HOTSPOTS_SUBMIT'); ?></span>
				</span>
			</button>
			<div id="hotspots-address-result">

			</div>
		</form>
	</div>

</div>
