<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;

\defined('_JEXEC') or die;

/**
 * Own icon of a marker instead of the category icon. Stored relative to media/com_hotspots/images/categories,
 * for example "/sample/aa_club_c.png"; empty means the category icon.
 */
class MarkerimageField extends FormField
{
    use SampleIconsTrait;

    protected $type = 'Markerimage';

    protected function getInput()
    {
        $this->useIconPickerScript();

        $value = (string) $this->value;

        // Only paths inside the icon folder.
        if ($value !== '' && (str_contains($value, '..') || !preg_match('#^[/A-Za-z0-9_.\- ]+$#', $value))) {
            $value = '';
        }

        $custom = $value !== '';
        $html   = [];
        $html[] = '<div class="hs-icon-picker" data-mode="marker" data-input="' . $this->id . '">';
        $html[] = '<div class="hs-icon-preview mb-2" id="' . $this->id . '_preview">'
            . ($custom ? '<img src="' . self::escape(MarkerHelper::iconBaseUrl() . ltrim($value, '/')) . '" alt="" style="max-height:48px">' : '') . '</div>';
        $html[] = '<select class="form-select hs-icon-source" id="' . $this->id . '_source">';
        $html[] = '<option value="0"' . ($custom ? '' : ' selected') . '>' . Text::_('COM_HOTSPOTS_SAME_MARKER_AS_CATEGORY') . '</option>';
        $html[] = '<option value="sample"' . ($custom ? ' selected' : '') . '>' . Text::_('COM_HOTSPOTS_CUSTOM_MARKER') . '</option>';
        $html[] = '</select>';
        $html[] = $this->sampleIconsHtml($this->id);
        $html[] = '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . self::escape($value) . '">';
        $html[] = '</div>';

        return implode("\n", $html);
    }
}
