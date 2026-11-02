<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Form\Field\ColorField;

\defined('_JEXEC') or die;

/**
 * Colour picker for the tile marker colour. The colour is stored as "r,g,b" (the content plugin
 * hotspotscategory converts it on save), the picker needs "#rrggbb".
 */
class HotspotscolorField extends ColorField
{
    protected $type = 'Hotspotscolor';

    protected function getInput()
    {
        $value = (string) $this->value;

        if (preg_match('/^\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*$/', $value, $rgb)) {
            $this->value = \sprintf('#%02x%02x%02x', min(255, (int) $rgb[1]), min(255, (int) $rgb[2]), min(255, (int) $rgb[3]));
        }

        return parent::getInput();
    }
}
