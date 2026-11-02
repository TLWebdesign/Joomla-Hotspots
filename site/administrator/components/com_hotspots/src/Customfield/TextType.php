<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Customfield;

\defined('_JEXEC') or die;

class TextType extends AbstractType
{
    public function xml(object $field): string
    {
        return '<field type="text" ' . $this->baseAttributes($field) . ' filter="string" class="form-control"/>';
    }

    public function render(object $field, mixed $value): string
    {
        return self::e(\is_array($value) ? implode(', ', $value) : $value);
    }
}
