<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Customfield;

use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

/**
 * Options: value_checked=<text to show>, translate=1 to run it through the language strings.
 */
class CheckboxType extends AbstractType
{
    public function xml(object $field): string
    {
        return '<field type="checkbox" ' . $this->baseAttributes($field) . ' value="1" filter="string"/>';
    }

    public function render(object $field, mixed $value): string
    {
        $options = self::optionsArray($field->options ?? '');

        if (isset($options['value_checked'])) {
            return self::e(isset($options['translate']) ? Text::_($options['value_checked']) : $options['value_checked']);
        }

        return self::e(\is_array($value) ? implode(', ', $value) : $value);
    }
}
