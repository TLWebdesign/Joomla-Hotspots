<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;

\defined('_JEXEC') or die;

/**
 * One marker for the marker page.
 */
class HotspotModel extends ItemModel
{
    protected $_context = 'com_hotspots.hotspot';

    protected function populateState()
    {
        $app = Factory::getApplication();

        $this->setState('hotspot.id', $app->getInput()->getInt('id'));
        $this->setState('params', $app->getParams());
    }

    /**
     * The marker, or null when it does not exist or the user may not see it.
     *
     * Visitors see published markers (within the publish dates) of their access levels. The owner and
     * users who may edit the marker also see it when it is unpublished or restricted.
     */
    public function getItem($pk = null): ?object
    {
        $pk = (int) ($pk ?: $this->getState('hotspot.id'));

        if ($pk <= 0) {
            return null;
        }

        if (isset($this->_item[$pk])) {
            return $this->_item[$pk] ?: null;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['m.*', $db->quoteName('m.id', 'hotspots_id'), $db->quoteName('u.name', 'user_name'), $db->quoteName('u.username', 'user_username')])
            ->from($db->quoteName('#__hotspots_marker', 'm'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('m.created_by'))
            ->where($db->quoteName('m.id') . ' = :id')
            ->bind(':id', $pk, ParameterType::INTEGER);

        $item = $db->setQuery($query)->loadObject();

        if ($item && !$this->canView($item)) {
            $item = null;
        }

        if ($item) {
            $item->params = new Registry($item->params);
        }

        $this->_item[$pk] = $item ?: false;

        return $item ?: null;
    }

    /**
     * True when the item is not published for visitors (unpublished, outside its dates).
     */
    public static function isHidden(object $item): bool
    {
        $now = Factory::getDate()->toSql();

        return (int) $item->state !== 1
            || (!empty($item->publish_up) && $item->publish_up > $now)
            || (!empty($item->publish_down) && $item->publish_down < $now);
    }

    private function canView(object $item): bool
    {
        $user = $this->getCurrentUser();

        if (SecurityHelper::authorise('edit', $item, $user)) {
            return true;
        }

        $levels = array_map('intval', $user->getAuthorisedViewLevels());
        $isOwn  = $user->id && (int) $user->id === (int) $item->created_by;

        return !self::isHidden($item) && ($isOwn || \in_array((int) $item->access, $levels, true));
    }
}
