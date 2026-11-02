<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Language\Text;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;

\defined('_JEXEC') or die;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Hotspot\HtmlView $this */

// formattedAddress() escapes the parts itself.
$address = $this->params->get('show_address', 1) ? MarkerHelper::formattedAddress($this->item, true) : '';
?>
<?php if ($address !== '' || $this->params->get('show_coordinates', 0)) : ?>
    <div class="one-line-address text-end small">
        <?php if ($address !== '') : ?>
            <div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress"><?php echo $address; ?></div>
        <?php endif; ?>
        <?php if ($this->params->get('show_coordinates', 0)) : ?>
            <div class="hs-coordinates text-muted" itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates">
                <?php echo Text::_('COM_HOTSPOTS_LATITUDE'); ?>: <span itemprop="latitude"><?php echo (float) $this->item->gmlat; ?></span>
                |
                <?php echo Text::_('COM_HOTSPOTS_LONGITUDE'); ?>: <span itemprop="longitude"><?php echo (float) $this->item->gmlng; ?></span>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
