<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       18.01.2015
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_LIBRARIES . '/compojoom/include.php';

/**
 * HotspotsHelper for the categories menu
 *
 * @since  5.0
 */
abstract class HotspotsHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $submenu  - the currently selected submenu
	 *
	 * @return void
	 */
	public static function addSubmenu($submenu)
	{
		// Load language
		CompojoomLanguage::load('com_hotspots', JPATH_SITE);
		CompojoomLanguage::load('com_hotspots', JPATH_ADMINISTRATOR);
		CompojoomLanguage::load('com_hotspots.sys', JPATH_ADMINISTRATOR);

		JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers');
		$menus = HotspotsHelperMenu::getMenu();

		foreach ($menus as $menu)
		{
			if (JVERSION < 3)
			{
				// Joomla 2.5...
				JSubMenuHelper::addEntry(
					JText::_($menu['title']),
					$menu['link'],
					$submenu == $menu['keywords']
				);
			}
			else
			{
				JHtmlSidebar::addEntry(
					JText::_($menu['title']),
					$menu['link'],
					$submenu == $menu['keywords']
				);
			}
		}
	}
}
