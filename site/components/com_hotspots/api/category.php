<?php
/**
 * @package    MatukioEvents
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-09-28
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe, Daniel Dimitrov. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * API HotspotsApiCategory
 *
 * @since  6.1.2
 */
class HotspotsApiCategory extends HotspotsApi
{
	/**
	 * Get all categories
	 *
	 * index.php?option=com_hotspots&view=api&request=category
	 *
	 * @return  array
	 *
	 * @since   6.1.2
	 */
	public function _()
	{
		$offset = $this->input->getInt('offset', 0);
		$limit  = $this->input->getInt('limit', 100);

		$search = $this->input->getString('search', null);
		$catId  = $this->input->getString('catid', null);
		$order  = $this->input->getString('order', 'c.title ASC');

		$categories = $this->loadCategories($search, $catId, $offset, $limit, $order);
		$categories = $this->cleanupCategories($categories);

		return array(
			'categories' => $categories,
			'count'  => count($categories)
		);
	}

	/**
	 * Load categories
	 *
	 * @param   string  $search      Search
	 * @param   int     $locationId  Get only categories which have an event at this location
	 * @param   int     $offset      Offset
	 * @param   int     $limit       The limit
	 * @param   string  $orderBy     Order by
	 *
	 * @return  mixed
	 *
	 * @since   6.1.2
	 */
	protected function loadCategories($search = null, $catId = null, $offset = 0, $limit = 0, $orderBy = 'c.title ASC')
	{
		$query = $this->db->getQuery(true);

		$query->select('c.*')
			->from('#__categories as c')
			->where('c.published = 1');

		if (!empty($search))
		{
			$query->where('c.title LIKE \'%' . $this->db->escape($search) . '%\'');
		}

		if (!empty($catId))
		{
			if (is_array($catId))
			{
				$catId = implode(',', $catId);
			}

			$query->where('c.id IN (' . $catId . ')');
		}

		// Permissions
		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

		$query->where('c.access IN (' . $groups . ')');

		// Filter only for categories of Matukio
		$query->where("c.extension = 'com_hotspots'");

		$query->order($this->db->escape($orderBy));

		$this->db->setQuery($query, $offset, $limit);

		return $this->db->loadObjectList();
	}

	/**
	 * Remove things we don't want to expose through the API
	 *
	 * @param   array  $categories  Categories
	 *
	 * @return  array
	 *
	 * @since  6.1.2
	 */
	protected function cleanupCategories($categories)
	{
		if (empty($categories))
		{
			return null;
		}

		// Files
		foreach ($categories as $category)
		{
			unset($category->lft);
			unset($category->rgt);
			unset($category->asset_id);
			unset($category->note);
			unset($category->checked_out);
			unset($category->checked_out_time);
			unset($category->created_user_id);
			unset($category->created_time);
			unset($category->modified_user_id);
			unset($category->modified_time);
			unset($category->hits);
		}

		return $categories;
	}
}
