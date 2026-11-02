<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

/**
 * Marker icon of a category: upload a new image (media field named in "uploadfield") or pick a sample icon.
 *
 * The value gets a prefix that the content plugin hotspotscategory resolves on save:
 * "[sample]/sample/<file>" (the sample is copied) or "[upload]<path>".
 */
class CategorymarkericonField extends FormField
{
    use SampleIconsTrait;

    protected $type = 'Categorymarkericon';

    protected function getInput()
    {
        $this->useIconPickerScript();

        $value   = (string) $this->value;
        $preview = '';

        if ($value !== '') {
            // After a failed save the value still has the prefix.
            $path    = str_replace(['[sample]', '[upload]'], ['media/com_hotspots/images/categories', ''], $value);
            $preview = Uri::root(true) . '/' . ltrim($path, '/');
        }

        $upload = $this->form->getField((string) ($this->element['uploadfield'] ?? 'hs-marker-image'), 'params');

        $html   = [];
        $html[] = '<div class="hs-icon-picker" data-mode="category" data-input="' . $this->id . '"'
            . ' data-upload="' . ($upload ? self::escape($upload->id) : '') . '">';
        $html[] = '<div class="hs-icon-preview mb-2" id="' . $this->id . '_preview">'
            . ($preview !== '' ? '<img src="' . self::escape($preview) . '" alt="" style="max-height:48px">' : '') . '</div>';
        $html[] = '<select class="form-select hs-icon-source" id="' . $this->id . '_source" aria-label="' . self::escape(Text::_('COM_HOTSPOTS_ICON')) . '">';
        $html[] = '<option value="">' . Text::_('COM_HOTSPOTS_SELECT') . '</option>';
        $html[] = '<option value="new">' . Text::_('COM_HOTSPOTS_UPLOAD_NEW_IMAGE') . '</option>';
        $html[] = '<option value="sample">' . Text::_('COM_HOTSPOTS_SELECT_SAMPLE_IMAGE') . '</option>';
        $html[] = '</select>';
        $html[] = $this->sampleIconsHtml($this->id);
        $html[] = '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . self::escape($value) . '"'
            . ($this->required ? ' class="required" required' : '') . '>';
        $html[] = '</div>';

        return implode("\n", $html);
    }
}
