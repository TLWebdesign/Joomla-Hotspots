<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       05.10.2014
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperModules
 *
 * @since  4.0.5
 */
class HotspotsHelperModules
{
	/**
	 * Get the modules for our map
	 *
	 * @return array
	 */
	public static function getModules()
	{
		$modules = array();
		$positions = array(
			'hotspots_above_map',
			'hotspots_above_filter',
			'hotspots_below_filter',
			'hotspots_below_map',
			'hotspots_above_list',
			'hotspots_below_list',
			'hotspots_below_directions',
			'hotspots_below_settings'
		);

		foreach ($positions as $value)
		{
			$modules[$value] = HOTSPOTS_PRO ? JModuleHelper::getModules($value) : array();
		}

		return $modules;
	}

	/**
	 * Render the modules
	 *
	 * @param   array  $modules  - modules to render
	 *
	 * @return string
	 */
	public static function renderModules($modules)
	{
		$moduleContent = '';

		if (!count($modules))
		{
			return $moduleContent;
		}

		foreach ($modules as $module)
		{
			$moduleContent = JModuleHelper::renderModule($module);
		}

		return $moduleContent;
	}
}
