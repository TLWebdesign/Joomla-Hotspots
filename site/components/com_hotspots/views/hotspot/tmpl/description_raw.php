<?php
/**
 * @author     Daniel Dimitrov
 * @date       : 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>

<div id="map-overlay">
	<div id='marker_container'>
		<h2><?php echo $this->hotspot->title ?></h2>

		<div id='marker_adress'>
			<?php  require_once JPATH_COMPONENT . '/views/hotspot/tmpl/default_address.php'; ?>
		</div>
		<div id='marker_description'>
			<?php echo $this->hotspot->description_small; ?>
			<div class="clear-both"></div>
			<p>
				<?php if ($this->settings->get('show_author', 1)) : ?>
					<?php echo JTEXT::_('COM_HOTSPOTS_POSTED_BY'); ?>
					<strong>
						<?php if ($this->profile) : ?>
						<a href="<?php echo $this->profile; ?>">
						<?php endif; ?>

							 <?php echo $this->hotspot->created_by_alias ? $this->hotspot->created_by_alias : $this->hotspot->user_name; ?>

						<?php if ($this->profile) : ?>
						</a>
						<?php endif; ?>
					</strong>
				<?php endif; ?>

				<?php if ($this->settings->get('show_date')) : ?>
					 <?php echo JText::_('COM_HOTSPOTS_ON'); ?> <?php echo $this->hotspot->postdate; ?>
				<?php endif; ?>
			</p>

		</div>
	</div>
</div>