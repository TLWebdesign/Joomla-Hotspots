<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;

\defined('_JEXEC') or die;

/**
 * List of markers.
 *
 * State filters (also usable from the site, the module and other extensions with ignore_request):
 * filter.search ("id:1,2" or text in the title), filter.published (0/1, '' = both, '*' = all),
 * filter.category_id (int or int[]), filter.created_by (int, -1 = nothing), filter.language,
 * filter.access (int or int[] of view levels).
 */
class HotspotsModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'street', 'a.street',
                'plz', 'a.plz',
                'town', 'a.town',
                'country', 'a.country',
                'gmlat', 'a.gmlat',
                'gmlng', 'a.gmlng',
                'catid', 'a.catid', 'category_title',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'state', 'a.state', 'published',
                'language', 'a.language',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'category_id', 'level',
            ];
        }

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.title', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . serialize($this->getState('filter.category_id'));
        $id .= ':' . $this->getState('filter.created_by');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . serialize($this->getState('filter.access'));

        return parent::getStoreId($id);
    }

    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->select($db->quoteName(['c.title', 'u.name', 'ag.title'], ['category_title', 'user_name', 'access_level']))
            // Old name, used by templates and the client's CB plugin.
            ->select($db->quoteName('c.title', 'cat_name'))
            ->from($db->quoteName('#__hotspots_marker', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));

        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $state = (int) $published;
            $query->where($db->quoteName('a.state') . ' = :state')->bind(':state', $state, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->whereIn($db->quoteName('a.state'), [0, 1]);
        }

        $categories = $this->getState('filter.category_id');
        $categories = array_filter(ArrayHelper::toInteger(\is_array($categories) ? $categories : [$categories]));

        if ($categories) {
            $query->whereIn($db->quoteName('a.catid'), $categories);
        }

        $createdBy = (int) $this->getState('filter.created_by');

        if ($createdBy) {
            $query->where($db->quoteName('a.created_by') . ' = :createdBy')->bind(':createdBy', $createdBy, ParameterType::INTEGER);
        }

        $access = $this->getState('filter.access');
        $access = array_filter(ArrayHelper::toInteger(\is_array($access) ? $access : [$access]));

        if ($access) {
            $query->whereIn($db->quoteName('a.access'), $access);
        }

        $language = (string) $this->getState('filter.language');

        if ($language !== '') {
            $query->where($db->quoteName('a.language') . ' = :language')->bind(':language', $language);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
                $ids = array_filter(ArrayHelper::toInteger(explode(',', substr($search, 3))));
                $query->whereIn($db->quoteName('a.id'), $ids ?: [0]);
            } else {
                $like = '%' . str_replace(' ', '%', $search) . '%';
                $query->where('(' . $db->quoteName('a.title') . ' LIKE :search1 OR ' . $db->quoteName('a.town') . ' LIKE :search2)')
                    ->bind(':search1', $like)
                    ->bind(':search2', $like);
            }
        }

        $ordering  = (string) $this->getState('list.ordering', 'a.title');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        // populateState only accepts the filter_fields, but the model can also be used with ignore_request.
        $ordering = match (true) {
            \in_array($ordering, ['state', 'published'], true)                => 'a.state',
            \in_array($ordering, ['category_title', 'access_level'], true)    => $ordering,
            str_starts_with($ordering, 'a.') && \in_array($ordering, $this->filter_fields, true) => $ordering,
            \in_array('a.' . $ordering, $this->filter_fields, true)            => 'a.' . $ordering,
            default                                                          => 'a.title',
        };

        $query->order($db->escape($ordering) . ' ' . $direction);

        return $query;
    }
}
