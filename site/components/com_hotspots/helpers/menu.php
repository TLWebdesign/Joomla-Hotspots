<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperMenu
 *
 * @since  4.0
 */
class HotspotsHelperMenu
{
	/**
	 * Generates the menu
	 *
	 * @return  array
	 */
	public static function getMenu()
	{
		$menu = array();

		$menu['dashboard'] = array(
			'link' => 'index.php?option=com_hotspots&view=dashboard',
			'title' => 'COM_HOTSPOTS_DASHBOARD',
			'icon' => 'fa-dashboard',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'dashboard home overview cpanel'
		);
		$menu['hotspots'] = array(
			'link' => 'index.php?option=com_hotspots&view=hotspots',
			'title' => 'COM_HOTSPOTS_LOCATIONS',
			'icon' => 'fa-map-marker',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'lists hotspots markers locations'
		);


		$menu['kmls'] = array(
			'link' => 'index.php?option=com_hotspots&view=kmls',
			'title' => 'COM_HOTSPOTS_KML',
			'icon' => 'fa-file',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'kml'
		);


		$menu['categories'] = array(
			'link' => 'index.php?option=com_categories&view=categories&extension=com_hotspots',
			'title' => 'COM_HOTSPOTS_CATEGORIES',
			'icon' => 'fa-users',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'categories'
		);
		$menu['customfields'] = array(
			'link' => 'index.php?option=com_hotspots&view=customfields',
			'title' => 'COM_HOTSPOTS_CUSTOM_FIELDS',
			'icon' => 'fa-puzzle-piece',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'customfields'
		);
		$menu['import'] = array(
			'link' => 'index.php?option=com_hotspots&view=import',
			'title' => 'COM_HOTSPOTS_IMPORT',
			'icon' => 'fa-exchange',
			'anchor' => '',
			'children' => array(),
			'label' => '',
			'keywords' => 'import'
		);

		return $menu;
	}

	/**
	 * In old versionsof the component the selected categories were saved in the wrong format
	 * With this function we update the menu in the background, so the user won't have to do this manually
	 *
	 * @return void
	 */
	public static function updateMenu()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__menu')
			->where('link = "index.php?option=com_hotspots&view=hotspots" AND client_id = 0');

		$db->setQuery($query, 0, 1);

		$menu = $db->loadObject();

		if ($menu)
		{
			$params = json_decode($menu->params);

			if (isset($params->hs_startcat) && is_string($params->hs_startcat))
			{
				$params->hs_startcat = array($params->hs_startcat);
				$query->update('#__menu')->set('params = ' . $db->quote(json_encode($params)))->where('id = ' . $db->quote($menu->id));
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * In hotspots 5 we started using the Joomla category manager. Because of that the already
	 * selected categories in the menu have now wrong start categories.
	 * Here we try to fix this issue.
	 *
	 * @return void
	 */
	public static function updateMenuTo5()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__menu')
			->where('link = "index.php?option=com_hotspots&view=hotspots" AND client_id = 0');

		$db->setQuery($query, 0, 1);

		$menus = $db->loadObjectList();

		foreach ($menus as $menu)
		{
			$params = new JRegistry($menu->params);
			$startCats = $params->get('hs_startcat');

			if (count($startCats))
			{
				$categories = JCategories::getInstance('Hotspots')->get()->getChildren(true);
				$mappings = array();
				$newStartArray = array();

				foreach ($categories as $category)
				{
					$catParams = new JRegistry($category->params);
					$mappings[$catParams->get('import_id')] = $category->id;
				}

				foreach ($startCats as $startCat)
				{
					if (isset($mappings[$startCat]))
					{
						$newStartArray[] = $mappings[$startCat];
					}
				}

				$params->set('hs_startcat', $newStartArray);

				$query->clear();
				$query->update('#__menu')->set('params = ' . $db->quote($params->toString()))->where('id = ' . $db->quote($menu->id));
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
}
