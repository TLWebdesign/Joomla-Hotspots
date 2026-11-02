<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

/**
 * Class HotspotsModelHotspots
 *
 * @since  3.0
 */
class HotspotsModelCustomfields extends CompojoomModelCustomfields
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setState('filter.component', 'com_hotspots.hotspot');
	}

	/**
	 * Gets the category lations for the customfields
	 *
	 * @param   array  $ids  - array with ids
	 *
	 * @return JDatabaseQuery
	 */
	protected function getCatRelationsQuery($ids)
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('c.title, j.compojoom_customfields_id, j.catid')->from('#__compojoom_customfields_cats AS j')
			->leftJoin('#__categories as c ON j.catid = c.id')
			->where('j.compojoom_customfields_id IN (' . implode(',', $ids) . ')');

		return $query;
	}
}
