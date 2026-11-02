<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Customfield;

\defined('_JEXEC') or die;

/**
 * Options: one "value=label" per line.
 */
class ListType extends AbstractType
{
    public function xml(object $field): string
    {
        $xml = '<field type="list" ' . $this->baseAttributes($field) . ' filter="string">';

        foreach (self::optionsArray($field->options ?? '') as $key => $label) {
            $xml .= '<option value="' . self::attr($key) . '">' . self::attr($label) . '</option>';
        }

        return $xml . '</field>';
    }

    public function render(object $field, mixed $value): string
    {
        $options = self::optionsArray($field->options ?? '');
        $labels  = [];

        foreach ((array) $value as $item) {
            $labels[] = isset($options[(string) $item]) ? self::translate($options[(string) $item]) : self::e($item);
        }

        return implode(', ', $labels);
    }
}
