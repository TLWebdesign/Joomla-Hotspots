<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2011
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

\defined('_JEXEC') or die;

/**
 * List of KML files.
 */
class KmlsModel extends ListModel
{
    public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'kmls.hotspots_kml_id',
                'title', 'kmls.title',
                'category_title', 'cat.title',
                'original_filename', 'kmls.original_filename',
                'created', 'kmls.created',
                'state', 'kmls.state',
                'published', 'category_id',
            ];
        }

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'kmls.title', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.category_id');

        return parent::getStoreId($id);
    }

    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('kmls.*')
            ->select($db->quoteName(['cat.title', 'u.name'], ['category_title', 'user_name']))
            ->from($db->quoteName('#__hotspots_kmls', 'kmls'))
            ->join('LEFT', $db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('kmls.catid'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('kmls.created_by'));

        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $state = (int) $published;
            $query->where($db->quoteName('kmls.state') . ' = :state')->bind(':state', $state, ParameterType::INTEGER);
        } else {
            $query->whereIn($db->quoteName('kmls.state'), [0, 1]);
        }

        $categoryId = (int) $this->getState('filter.category_id');

        if ($categoryId > 0) {
            $query->where($db->quoteName('kmls.catid') . ' = :catid')->bind(':catid', $categoryId, ParameterType::INTEGER);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
                $id = (int) substr($search, 3);
                $query->where($db->quoteName('kmls.hotspots_kml_id') . ' = :id')->bind(':id', $id, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', $search) . '%';
                $query->where('(' . $db->quoteName('kmls.title') . ' LIKE :search1 OR ' . $db->quoteName('kmls.original_filename') . ' LIKE :search2)')
                    ->bind(':search1', $search)
                    ->bind(':search2', $search);
            }
        }

        // The ordering is checked against filter_fields by ListModel::populateState().
        $query->order($db->escape($this->getState('list.ordering', 'kmls.title')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }
}
