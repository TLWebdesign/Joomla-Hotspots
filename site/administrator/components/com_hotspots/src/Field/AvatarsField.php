<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use TLWebdesign\Component\Hotspots\Administrator\Helper\UserHelper;

\defined('_JEXEC') or die;

/**
 * Where avatars of marker authors come from: none or Community Builder (disabled when CB is not installed).
 */
class AvatarsField extends ListField
{
    protected $type = 'Avatars';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            HTMLHelper::_('select.option', '0', Text::_('JNONE')),
            HTMLHelper::_('select.option', UserHelper::PROVIDER_CB, Text::_('COM_HOTSPOTS_COMMUNITY_BUILDER'), 'value', 'text', !UserHelper::cbAvailable()),
        ]);
    }
}
