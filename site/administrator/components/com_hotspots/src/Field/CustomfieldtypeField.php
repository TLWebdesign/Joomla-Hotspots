<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;

\defined('_JEXEC') or die;

/**
 * The type select of a custom field (text, checkbox, list, advancedlist, url).
 */
class CustomfieldtypeField extends ListField
{
    protected $type = 'Customfieldtype';

    protected function getOptions()
    {
        $options = parent::getOptions();

        foreach (CustomfieldsHelper::getTypes() as $type) {
            $options[] = HTMLHelper::_('select.option', $type, $type);
        }

        return $options;
    }
}
