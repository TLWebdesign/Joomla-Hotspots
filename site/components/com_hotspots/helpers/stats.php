<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperStats
 *
 * @since  4.0
 */
class HotspotsHelperStats
{
	/**
	 * Get count array with hotspots stats
	 *
	 * @static
	 * @return array
	 */
	public static function getStats()
	{
		$all = self::countHotspots();
		$published = self::countHotspots('state = 1');
		$stats = array(
			'all' => $all,
			'published' => $published,
			'unpublished' => $all - $published
		);

		return $stats;
	}

	/**
	 * Count hotspots
	 *
	 * @param   string  $where  -  the where part of the query
	 *
	 * @return mixed
	 */
	private static function countHotspots($where = null)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*) AS count')
			->from('#__hotspots_marker');

		if ($where)
		{
			$query->where($where);
		}

		$db->setQuery($query);

		return $db->loadObject()->count;
	}
}
