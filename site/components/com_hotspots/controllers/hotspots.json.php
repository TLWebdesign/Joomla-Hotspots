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

		$hotspot = HotspotsHelperHotspot::prepareHotspotForJsonOutput(
						HotspotsHelperUtils::prepareHotspot($model->getHotspot())
					);

		echo json_encode($hotspot);
		jexit();
	}
}
