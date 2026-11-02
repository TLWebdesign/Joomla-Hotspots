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
class HotspotsHelperBasic
{
	/**
	 * Generates the menu
	 *
	 * @return  array
	 */
	public static function getFooterText()
	{
		return '<p class="copyright" style="text-align: center; margin-top: 15px;">' . JText::_('COM_HOTSPOTS_POWERED_BY') .
				' <a href="https://compojoom.com" title="Joomla extensions, modules and plugins">compojoom.com</a></p>';
	}
}
