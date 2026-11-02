<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;

\defined('_JEXEC') or die;

/**
 * The markers of the logged-in user ("My markers").
 */
class UserhotspotsModel extends ListModel
{
    public function __construct($config = [])
    {
        $config['filter_fields'] ??= ['id', 'a.id', 'title', 'a.title', 'state', 'a.state', 'created', 'a.created', 'catid', 'a.catid', 'category_id'];

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        // Default order from the menu item ("title ASC", "created DESC", ...).
        [$column, $dir] = array_pad(explode(' ', trim((string) SettingsHelper::get('hotspots_order', 'title ASC'))), 2, 'ASC');
        $column         = 'a.' . preg_replace('/^a\./', '', $column);

        parent::populateState(\in_array($column, $this->filter_fields, true) ? $column : 'a.title', strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');

        // Only ever the markers of the current user; guests have none.
        $this->setState('filter.owner', (int) $this->getCurrentUser()->id);

        // The search tools give strings: integers only (whereIn() binds them as integers too).
        $this->setState('filter.category_id', array_values(array_filter(ArrayHelper::toInteger((array) $this->state->get('filter.category_id', [])))));
        $this->setState('params', Factory::getApplication()->getParams());
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.owner');
        $id .= ':' . serialize($this->getState('filter.category_id'));
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $owner = (int) $this->getState('filter.owner');
        $query = $db->getQuery(true)
            ->select([
                'a.id', 'a.title', 'a.alias', 'a.catid', 'a.state', 'a.created', 'a.created_by', 'a.access', 'a.publish_up', 'a.publish_down',
                $db->quoteName('c.title', 'cat_name'),
                $db->quoteName('c.alias', 'cat_alias'),
            ])
            ->from($db->quoteName('#__hotspots_marker', 'a'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->where($db->quoteName('a.created_by') . ' = :owner')
            ->bind(':owner', $owner, ParameterType::INTEGER);

        if ($owner <= 0) {
            $query->where('1 = 0');
        }

        if ($categories = array_filter((array) $this->getState('filter.category_id'))) {
            $query->whereIn($db->quoteName('a.catid'), $categories);
        }

        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $state = (int) $published;
            $query->where($db->quoteName('a.state') . ' = :state')->bind(':state', $state, ParameterType::INTEGER);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
                $id = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')->bind(':id', $id, ParameterType::INTEGER);
            } else {
                $search = '%' . $search . '%';
                $query->where($db->quoteName('a.title') . ' LIKE :search')->bind(':search', $search);
            }
        }

        $ordering  = $this->getState('list.ordering', 'a.title');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $query->order($db->escape(\in_array($ordering, $this->filter_fields, true) ? $ordering : 'a.title') . ' ' . $direction);

        return $query;
    }
}
