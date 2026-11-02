<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

\defined('_JEXEC') or die;

/**
 * Select one marker in a modal (type="modal_hotspot"), for example in the menu item of a marker page.
 */
class HotspotField extends ModalSelectField
{
    protected $type = 'Modal_Hotspot';

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // "id:alias" to id.
        if ($value && str_contains((string) $value, ':')) {
            $value = (int) explode(':', (string) $value, 2)[0];
        }

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        Factory::getApplication()->getLanguage()->load('com_hotspots', JPATH_ADMINISTRATOR);

        $url = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $url->setQuery([
            'option'                => 'com_hotspots',
            'view'                  => 'hotspots',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            Session::getFormToken() => 1,
        ]);

        $this->urls['select']         = (string) $url;
        $this->modalTitles['select']  = Text::_('COM_HOTSPOTS_SELECT_HOTSPOT');
        $this->hint                   = $this->hint ?: Text::_('COM_HOTSPOTS_SELECT_HOTSPOT');

        return $result;
    }

    protected function getValueTitle()
    {
        $id = (int) $this->value;

        if (!$id) {
            return '';
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__hotspots_marker'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        return (string) ($db->setQuery($query)->loadResult() ?: $id);
    }
}
