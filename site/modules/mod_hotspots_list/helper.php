<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       10.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class ModHotspotsHelper
 *
 * @since  3.5
 */
class ModHotspotsHelper
{
	/**
	 * Gets a list with the hotspots
	 *
	 * @param   object  $params  - the module parameters
	 *
	 * @return mixed
	 */
	public static function getList($params)
	{
		JLoader::register('HotspotsModelHotspots', JPATH_ADMINISTRATOR . '/components/com_hotspots/models/hotspots.php');
		$model = new HotspotsModelHotspots(array('ignore_request' => true));

		$model->setState('filter.published', 1);
		$model->setState('list.start', 0);
		$model->setState('list.limit', $params->get('limit', 10));
		$model->setState('list.ordering', 'a.' . $params->get('ordering', 'created'));
		$model->setState('list.direction', $params->get('direction', 'desc'));
		$model->setState('filter.category_id', $params->get('catid', ''));

		$hotspots = $model->getItems();

		return $hotspots;
	}
}
