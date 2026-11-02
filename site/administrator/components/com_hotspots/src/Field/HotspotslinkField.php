<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

\defined('_JEXEC') or die;

/**
 * Where "Read more" of a marker goes: the marker page, or a target of an enabled hotspotslinks plugin
 * (article, external URL). The id or URL goes into the separate field link_to_id.
 */
class HotspotslinkField extends ListField
{
    protected $type = 'Hotspotslink';

    protected function getOptions()
    {
        $language = Factory::getApplication()->getLanguage();
        $options  = [HTMLHelper::_('select.option', '0', Text::_('COM_HOTSPOTS_LINK_TO_MARKER'))];

        foreach (PluginHelper::getPlugin('hotspotslinks') as $plugin) {
            $language->load('plg_hotspotslinks_' . $plugin->name . '.sys', JPATH_ADMINISTRATOR);

            $options[] = HTMLHelper::_('select.option', $plugin->name, Text::_('PLG_HOTSPOTSLINKS_COM_' . strtoupper($plugin->name)));
        }

        return array_merge(parent::getOptions(), $options);
    }
}
