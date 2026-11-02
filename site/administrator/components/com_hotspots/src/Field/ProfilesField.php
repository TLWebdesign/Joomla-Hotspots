<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use TLWebdesign\Component\Hotspots\Administrator\Helper\UserHelper;

\defined('_JEXEC') or die;

/**
 * Where the profile links of marker authors go to: none or Community Builder (disabled when CB is not installed).
 */
class ProfilesField extends ListField
{
    protected $type = 'Profiles';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            HTMLHelper::_('select.option', '0', Text::_('JNONE')),
            HTMLHelper::_('select.option', UserHelper::PROVIDER_CB, Text::_('COM_HOTSPOTS_COMMUNITY_BUILDER'), 'value', 'text', !UserHelper::cbAvailable()),
        ]);
    }
}
