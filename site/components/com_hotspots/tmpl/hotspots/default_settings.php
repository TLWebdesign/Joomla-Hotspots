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

<script type="text/template" id="hs-settings-template">
	<div class="input-prepend control-group">
		<span class="btn add-on js-hs-mylocation"><span class="fa fa-crosshairs" title="<?php echo Text::_('COM_HOTSPOTS_FIND_MY_LOCATION'); ?>"></span></span>
		<input type="text" name="mylocation" placeholder="<?php echo Text::_('COM_HOTSPOTS_MY_LOCATION'); ?>"/>
	</div>
	<?php if (true) : ?>
		<label class="checkbox">
			<input type="checkbox" class="js-hs-stickit" name="weather"
				   value="1" <?php echo $this->settings->get('weather_layer', 0) ? 'checked' : '' ?> />
			<?php echo Text::_('COM_HOTSPOTS_WEATHER'); ?>
		</label>
	<?php endif; ?>


	<label class="checkbox">
		<input type="checkbox" class="js-hs-stickit" name="traffic"
		       value="true" <?php echo $this->settings->get('traffic_layer', 0) ? 'checked' : '' ?>>
		<?php echo Text::_('COM_HOTSPOTS_TRAFFIC'); ?>
	</label>

	<label class="checkbox">
		<input type="checkbox" name="transit" class="js-hs-stickit"
		       value="1" <?php echo $this->settings->get('transit_layer', 0) ? 'checked' : '' ?> />
		<?php echo Text::_('COM_HOTSPOTS_TRANSIT'); ?>
	</label>

	<label class="checkbox">
		<input type="checkbox" name="bicycle" class="js-hs-stickit"
		       value="1" <?php echo $this->settings->get('bicycling_layer', 0) ? 'checked' : '' ?> />
		<?php echo Text::_('COM_HOTSPOTS_BICYCLING'); ?>
		<span class="hs-bike small muted">
			<span class="hs-bike-trail"></span> <?php echo Text::_('COM_HOTSPOTS_TRAILS'); ?>
		</span>
		<span class="hs-bike small muted">
			<span class="hs-bike-path"></span> <?php echo Text::_('COM_HOTSPOTS_DEDICATED_LANES'); ?>
		</span>
		<span class="hs-bike small muted">
			<span class="hs-bike-friendly-road"></span> <?php echo Text::_('COM_HOTSPOTS_BICYCLE_FRIENDLY_ROADS'); ?>
		</span>
	</label>

	<?php if(count($this->modules['hotspots_below_settings'])): ?>
		<div class="hs-modules-below-settings">
			<?php echo MapHelper::renderModules($this->modules['hotspots_below_settings']); ?>
		</div>
	<?php endif; ?>
</script>