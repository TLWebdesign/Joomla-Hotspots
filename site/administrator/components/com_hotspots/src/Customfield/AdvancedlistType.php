<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Customfield;

\defined('_JEXEC') or die;

/**
 * Options as JSON: {"multiple": "true", "options": [{"value": "a", "label": "A"}]}. All keys except
 * "options" become attributes of the list field.
 */
class AdvancedlistType extends AbstractType
{
    private const ALLOWED_ATTRIBUTES = ['multiple', 'size', 'class', 'layout', 'hint', 'onchange'];

    public function xml(object $field): string
    {
        $json       = self::optionsJson($field->options ?? '');
        $attributes = '';

        foreach (get_object_vars($json) as $name => $value) {
            if (\in_array($name, self::ALLOWED_ATTRIBUTES, true) && is_scalar($value)) {
                $attributes .= ' ' . $name . '="' . self::attr($value) . '"';
            }
        }

        $multiple = isset($json->multiple) && !\in_array(strtolower((string) $json->multiple), ['', '0', 'false'], true);
        $xml      = '<field type="list" ' . $this->baseAttributes($field) . $attributes
            . ' filter="' . ($multiple ? 'array' : 'string') . '">';

        foreach ((array) ($json->options ?? []) as $option) {
            $xml .= '<option value="' . self::attr($option->value ?? '') . '">' . self::attr($option->label ?? '') . '</option>';
        }

        return $xml . '</field>';
    }

    public function render(object $field, mixed $value): string
    {
        $labels = [];

        foreach ((array) (self::optionsJson($field->options ?? '')->options ?? []) as $option) {
            $labels[(string) ($option->value ?? '')] = (string) ($option->label ?? $option->value ?? '');
        }

        $out = [];

        foreach ((array) $value as $item) {
            $out[] = isset($labels[(string) $item]) ? self::translate($labels[(string) $item]) : self::e($item);
        }

        return implode(', ', $out);
    }
}
