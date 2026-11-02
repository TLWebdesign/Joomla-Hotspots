<?php
/**
 * @author     Daniel Dimitrov
 * @date: 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$address = HotspotsHelperHotspot::formattedAddress($this->hotspot, true);
?>
<?php if (HotspotsHelperSettings::get('show_address', 1) && $address) : ?>
	<div class="one-line-address text-right">
		<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
			<?php echo $address; ?>
		</div>
		<?php if (HotspotsHelperSettings::get('show_coordinates', 0)): ?>
			<div class="hs-coordinates small muted" itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates">
				<?php echo JText::_('COM_HOTSPOTS_LATITUDE'); ?>:
				<span  itemprop="latitude">
					<?php echo $this->hotspot->gmlat; ?>
				</span>
				|
				<?php echo JText::_('COM_HOTSPOTS_LONGITUDE'); ?>:
				<span  itemprop="latitude">
					<?php echo $this->hotspot->gmlng; ?>
				</span>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
