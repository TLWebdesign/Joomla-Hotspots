<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Form\Field\CategoryField;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;

\defined('_JEXEC') or die;

/**
 * Hotspots categories; with the option "enable_category" (menu item of the submission form) only the
 * categories selected in "catid".
 */
class HotspotscategoryField extends CategoryField
{
    public $type = 'Hotspotscategory';

    protected function getOptions()
    {
        $this->element['extension'] = 'com_hotspots';

        $options = parent::getOptions();

        if (!SettingsHelper::get('enable_category', 0)) {
            return $options;
        }

        $allowed = array_map('intval', (array) SettingsHelper::get('catid', []));

        return array_values(array_filter(
            $options,
            static fn ($option): bool => (string) $option->value === '' || \in_array((int) $option->value, $allowed, true)
        ));
    }
}
