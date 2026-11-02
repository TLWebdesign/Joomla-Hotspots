<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Customfield;

\defined('_JEXEC') or die;

/**
 * A custom field type: the Joomla form field XML for the marker form and the HTML for the site.
 * $field is a row of #__hotspots_customfields (title, slug, type, options, default, allow_empty).
 */
interface CustomfieldTypeInterface
{
    public function xml(object $field): string;

    /**
     * Escaped HTML for the value(s) stored on a marker.
     */
    public function render(object $field, mixed $value): string;
}
