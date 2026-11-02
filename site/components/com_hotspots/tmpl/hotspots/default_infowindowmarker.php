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

<script type="text/html" id="infowindow-marker">
	<i class="js-hs-close hs-close fa fa-times pull-right"></i>

	<h3>{{ title }}</h3>
		<div class="hs-hotspots-overview">
			<div class="hs-place-summary-details small muted">
				<?php if($this->settings->get('show_date', 1)): ?>
		<span title="<?php echo Text::_('COM_HOTSPOTS_POSTED_ON'); ?>">
							<span class="fa fa-clock-o"></span>
							{{ date }}
						</span>
				<?php endif; ?>
	<?php if($this->settings->get('show_author', 1)) : ?>
		<span title="<?php echo Text::_('COM_HOTSPOTS_POSTED_BY'); ?>">
						<span class="fa fa-user"></span>
						 {{ created_by }}
					 </span>
	<?php endif; ?>
	</div>
	{{{description}}}
	</div>
	<?php if ($this->settings->get('show_address', 1)): ?>
		<?php echo $this->loadTemplate('address'); ?>
	<?php endif; ?>
	<?php if(
		$this->settings->get('show_zoom_button', 1) ||
		$this->settings->get('hotspot_detailpage', 1)
	) : ?>
		<div class="hs-toolbar">
			<?php if($this->settings->get('show_zoom_button', 1)) : ?>
				<button class="btn btn-link btn-mini js-hs-zoom-in">
					<?php echo Text::_('COM_HOTSPOTS_ZOOM'); ?>
				</button>
			<?php endif; ?>
			<?php if($this->settings->get('hotspot_detailpage', 1)): ?>
				<span class="hs-toolbar-separator"></span>
				<a href='{{ readmore }}' class="btn btn-link btn-mini">
					<?php echo Text::_('COM_HOTSPOTS_READ_MORE'); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</script>