<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       10.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.modellist');

/**
 * Class HotspotsModelHotspots
 *
 * @since  3.0
 */
class HotspotsModelHotspots extends JModelList
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_HOTSPOTS';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'street', 'a.street',
				'plz', 'a.plz',
				'town', 'a.town',
				'country', 'a.country',
				'gmlat', 'a.gmlat',
				'gmlng', 'a.gmlng',
				'cat_name', 'cat.cat_name', 'category_title',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'published', 'a.state',
				'language', 'a.language',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
			);

		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$filterLanguage = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $filterLanguage);

		if (JFactory::getApplication()->isClient("administrator"))
		{
			$userId = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
			$this->setState('filter.created_by', $userId);
		}
		// List state information.
		parent::populateState('a.title', 'asc');

		/*
		 * TLWebdesign patch 2026-11-02: in the frontend, set the owner filter after parent::populateState(),
		 * which otherwise overwrites it from the request (filter[created_by]). Guests get -1 (no markers);
		 * before, created_by 0 meant guests saw all markers.
		 */
		if (!JFactory::getApplication()->isClient('administrator'))
		{
			$user = (int) JFactory::getUser()->get('id', 0);

			// If the user is able to edit, then we are dealing with a moderator! so he can see all hotspots
			if ($user && HotspotsHelperSecurity::authorise('edit'))
			{
				$user = 0;
			}

			$this->setState('filter.created_by', $user ?: -1);
		}
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, cat.title AS cat_name, u.name AS user_name')
			->from('#__hotspots_marker AS a')
			->leftJoin('#__users AS u ON u.id = a.created_by')
			->leftJoin('#__categories as cat ON cat.id = a.catid');

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$query->where('a.catid = ' . (int) $categoryId);
		}
		elseif (is_array($categoryId))
		{
			// TLWebdesign patch 2026-11-02: the ids come from the request; integers only (SQL injection).
			$categoryId = array_filter(\Joomla\Utilities\ArrayHelper::toInteger($categoryId));

			if ($categoryId)
			{
				$query->whereIn($db->quoteName('a.catid'), $categoryId);
			}
		}

		$createdBy = (int) $this->getState('filter.created_by');

		if ($createdBy)
		{
			$query->where('a.created_by = ' . $createdBy);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = explode(',', substr($search, 3));
				$query->where(CompojoomQueryHelper::in('a.id', $ids, $db));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ')');
			}
		}

		if ($this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->q($this->getState('filter.language')));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
