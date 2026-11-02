<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2014 compojoom.com, (C) 2026 TLWebdesign
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

<div id="hs" class="compojoom-bootstrap <?php echo $this->escape((string) $this->settings->get('pageclass_sfx')); ?>">
	<?php if ($this->settings->get('show_page_title', 1)) : ?>
		<div class="page-header">
			<h1> <?php echo $this->escape((string) $this->settings->get('page_heading', 'Hotspots')); ?> </h1>
		</div>
	<?php endif; ?>

	<?php if (count($this->modules['hotspots_above_map'])) : ?>
		<div class="hs-modules-above-map">
			<?php echo MapHelper::renderModules($this->modules['hotspots_above_map']); ?>
		</div>
	<?php endif; ?>
	<div id="js-hs-main-app" class="hotspots hs-main-region"></div>
	<?php if(count($this->modules['hotspots_below_map'])): ?>
		<div class="hs-modules-below-map">
			<?php echo MapHelper::renderModules($this->modules['hotspots_below_map']); ?>
		</div>
	<?php endif; ?>
	</div>

<script type="text/template" id="js-hs-main-region">
	<div id="js-menu-region" class="hs-menu-region"></div>
	<div id="js-map-region" class="hs-map-region"></div>
	<div id="js-hs-infowindow-region" class="hs-infowindow-region"></div>
</script>

<script type="text/template" id="menu-layout-template">
	<span title="Toggle the menu" class="<?php echo $this->settings->get('start_closed_menu', 0) ? 'toggle-off' : 'toggle-on'; ?> toggle" id="toggle-menu"></span>
	<div id="js-tabs-region" class="hs-tab-region">	</div>
</script>

<?php echo $this->loadTemplate('tabs'); ?>

<script type="text/template" id="main-map-template">
	<div id="map-container"></div>
</script>

<?php if($this->settings->get('show_tabs_hotspots', 1)) : ?>
	<?php echo $this->loadTemplate('hotspots'); ?>
	<?php echo $this->loadTemplate('hotspot'); ?>
<?php endif; ?>

<?php
// Show the infowindow only when we don't have a menu
?>
<?php if(!$this->settings->get('show_tabs_directions', 1)
		&& !$this->settings->get('show_tabs_settings', 1)
		&& !$this->settings->get('show_tabs_hotspots', 1)) : ?>
	<?php echo $this->loadTemplate('infowindowmarker'); ?>
<?php endif; ?>

<?php if($this->settings->get('show_tabs_directions', 1)) : ?>
	<?php echo $this->loadTemplate('directions'); ?>
<?php endif; ?>

<?php if($this->settings->get('show_tabs_settings', 1)) : ?>
	<?php echo $this->loadTemplate('settings'); ?>
<?php endif; ?>


<script type="text/template" id="loading-view">
	<div>
		<span class="fa-li fa fa-spinner fa-spin"></span> <?php echo Text::_('COM_HOTSPOTS_LOADING_DATA'); ?>
	</div>
</script>

<script type="text/template" id="js-hs-loading-mouse-template">
	<div class="js-hs-loading-data"><span class="fa-li fa fa-spinner fa-spin"></span> <?php echo Text::_('COM_HOTSPOTS_LOADING_DATA'); ?></div>
</script>

<?php echo preg_replace("@[\\r|\\n|\\t]+@", '', $this->loadTemplate('mapbuttons')); ?>

<?php echo $this->loadTemplate('sendmap'); ?>
