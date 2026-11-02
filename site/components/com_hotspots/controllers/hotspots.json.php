<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       22.09.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * Class HotspotsControllerHotspots
 *
 * @since  4
 */
class HotspotsControllerHotspots extends JControllerLegacy
{
	/**
	 * Fetches a single hotspot and outputs it as json
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function hotspot()
	{
		$model = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
		$item  = $model->getHotspot();

		// TLWebdesign patch 2026-11-02: unpublished markers only for those who may edit them (as in the HTML view).
		if (!$item || (!$item->state && !HotspotsHelperSecurity::canEdit($item)))
		{
			throw new \Exception('The resource doesn\'t exist', 404);
		}

		$hotspot = HotspotsHelperHotspot::prepareHotspotForJsonOutput(
						HotspotsHelperUtils::prepareHotspot($item)
					);

		echo json_encode($hotspot);
		jexit();
	}
}
