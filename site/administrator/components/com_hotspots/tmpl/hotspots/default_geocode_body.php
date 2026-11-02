<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Hotspots\HtmlView $this */

$this->getDocument()->getWebAssetManager()
    ->registerAndUseScript('com_hotspots.admin-geocode', 'com_hotspots/admin/geocode.js', [], ['defer' => true], ['core']);
?>
<div class="p-3">
    <p class="hs-geocode-output"><?php echo Text::_('COM_HOTSPOTS_GEOCODING_NOTICE'); ?></p>
</div>
<div class="btn-toolbar p-3">
    <button type="button" class="btn btn-success ms-auto hs-geocode-start"><?php echo Text::_('COM_HOTSPOTS_GEOCODE'); ?></button>
</div>
