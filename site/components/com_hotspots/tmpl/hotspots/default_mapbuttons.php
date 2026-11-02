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

<script type="text/template" id="hs-fullscreen-template">
	<div class="hs-buttons-map">
		<?php if($this->settings->get('find_position', 1)): ?>
			<div class="js-hs-geocode-center" title="<?php echo Text::_('COM_HOTSPOTS_CENTER_ON_MY_LOCATION'); ?>">
				<span class="fa fa-crosshairs"></span>
			</div>
		<?php endif; ?>

		<?php if($this->settings->get('resize_map', 1)): ?>
			<div class="js-hs-fullscreen-toggle" title="<?php echo Text::_('COM_HOTSPOTS_CENTER_TOGGLE_FULLSCREEN'); ?>">
				<span class="fa fa-expand"></span>
			</div>
		<?php endif; ?>
	</div>
</script>

<script type="text/template" id="hs-action-buttons-map-template">
	<div class="hs-buttons-map">
		<?php if($this->settings->get('mail_map', 0)): ?>
			<div>
				<span class="fa fa-envelope js-hs-hotspots-share-mail"></span>
			</div>
		<?php endif; ?>

		<?php if($this->settings->get('print_map', 0)): ?>
			<div><span class="fa fa-print"></span></div>
		<?php endif; ?>

		<?php if($this->settings->get('rss_enable', 0)): ?>
		<div>
		<a class="fa fa-rss" href="<?php echo Route::_('index.php?option=com_hotspots&view=hotspots&format=feed&type=rss'); ?>"
			target="_blank"
			title="<?php echo Text::_('COM_HOTSPOTS_FEED'); ?>"></a>
	</div>
		<?php endif; ?>
	</div>
</script>
