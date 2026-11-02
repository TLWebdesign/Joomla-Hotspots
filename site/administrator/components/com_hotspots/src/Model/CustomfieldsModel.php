<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;

\defined('_JEXEC') or die;

/**
 * List of the marker custom fields, with the categories of each field ($item->cats).
 */
class CustomfieldsModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] ??= [
            'id', 'cf.id',
            'title', 'cf.title',
            'type', 'cf.type',
            'enabled', 'cf.enabled',
            'ordering', 'cf.ordering',
            'published',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'cf.ordering', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    protected function getListQuery(): QueryInterface
    {
        $db        = $this->getDatabase();
        $component = CustomfieldsHelper::COMPONENT;
        $query     = $db->getQuery(true)
            ->select('cf.*')
            ->from($db->quoteName('#__compojoom_customfields', 'cf'))
            ->where($db->quoteName('cf.component') . ' = :component')
            ->bind(':component', $component);

        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $state = (int) $published;
            $query->where($db->quoteName('cf.enabled') . ' = :state')->bind(':state', $state, ParameterType::INTEGER);
        }

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
                $id = (int) substr($search, 3);
                $query->where($db->quoteName('cf.id') . ' = :id')->bind(':id', $id, ParameterType::INTEGER);
            } else {
                $like = '%' . $search . '%';
                $query->where('(' . $db->quoteName('cf.title') . ' LIKE :search1 OR ' . $db->quoteName('cf.slug') . ' LIKE :search2)')
                    ->bind(':search1', $like)
                    ->bind(':search2', $like);
            }
        }

        $ordering  = (string) $this->getState('list.ordering', 'cf.ordering');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        if (!\in_array($ordering, ['cf.id', 'cf.title', 'cf.type', 'cf.enabled', 'cf.ordering'], true)) {
            $ordering = 'cf.ordering';
        }

        $query->order($db->quoteName($ordering) . ' ' . $direction);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();

        if (!$items) {
            return $items;
        }

        $ids = array_map(static fn ($item) => (int) $item->id, $items);
        $db  = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([$db->quoteName('j.compojoom_customfields_id', 'field_id'), $db->quoteName('j.catid'), $db->quoteName('c.title')])
            ->from($db->quoteName('#__compojoom_customfields_cats', 'j'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('j.catid'))
            ->whereIn($db->quoteName('j.compojoom_customfields_id'), $ids);

        $cats = [];

        foreach ($db->setQuery($query)->loadObjectList() as $cat) {
            $cats[(int) $cat->field_id][] = $cat;
        }

        foreach ($items as $item) {
            $item->cats = $item->show === 'category' ? ($cats[(int) $item->id] ?? []) : [];
        }

        return $items;
    }
}
