<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       15.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelper
 *
 * @since  3.0
 */
class HotspotsHelperSettings
{
	private static $instance;

	/**
	 * Gets a setting and if not existing sets it
	 *
	 * @param   string  $title    - the setting name
	 * @param   string  $default  - default value
	 *
	 * @return mixed <type>
	 */
	public static function get($title = '', $default = '')
	{
		if (!isset(self::$instance))
		{
			self::$instance = self::_loadSettings();
		}

		return self::$instance->get($title, $default);
	}

	/**
	 * Loads the component settings and merges it with the menu settings
	 *
	 * @return JObject - loads a singleton object with all settings
	 */
	private static function _loadSettings()
	{
		$params = JComponentHelper::getParams('com_hotspots');

		// Grab the settings from the menu and merge them in the object
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		if (is_object($menu))
		{
			$item = $menu->getActive();

			$input = JFactory::getApplication()->input;

			// We are making a trick here for the hotspotsanywhere plugin.If the itemId is set there, then we try to fetch
			// the menu entry with that Itemid. This way we can load custom settings
			if ($input->get('HotspotsAnywhereMenuItem', 0))
			{
				$temp = $menu->getItem($input->get('HotspotsAnywhereMenuItem', 0));

				if ($temp)
				{
					$item = $temp;
				}
			}

			// Check if we are dealing with the Hotspots component. In the case of hotspotsanywhere
			// we might have a different component and this leaves us with wrong parameters
			if ($item && $item->component == 'com_hotspots')
			{
				$menuParams = $menu->getParams($item->id);

				foreach ($menuParams->toArray() as $key => $value)
				{
					if ($key == 'show_page_heading')
					{
						$key = 'show_page_title';
					}

					// If there is no value in the menu for styled map, just skip it
					if ($key == 'styled_maps')
					{
						if (trim($value) == '')
						{
							continue;
						}
					}

					$params->set($key, $value);
				}

				// Handle the settings override
				$override = $item->getParams()->get('settings_override', '');

				if ($override)
				{
					$overrideSettings = explode("\n", $override);

					foreach ($overrideSettings as $value)
					{
						$setting      = explode('=', $value);
						$settingValue = trim($setting[1]);

						if (is_numeric($settingValue))
						{
							$params->set($setting[0], (int) $settingValue);
						}
						else
						{
							$params->set($setting[0], $settingValue);
						}
					}
				}
			}
		}

		return $params;
	}

	/**
	 * Gets allowed actions
	 *
	 * @param   int     $messageId  - message id
	 * @param   string  $unit       - the unit
	 * @param   string  $assetName  - asset name
	 *
	 * @return JObject
	 */
	public static function getActions($messageId = 0, $unit = 'component', $assetName = 'com_hotspots')
	{
		jimport('joomla.access.access');
		$user = JFactory::getUser();
		$result = new JObject;

		if (empty($messageId))
		{
			$asset = $assetName;
		}
		else
		{
			$asset = $assetName . '.' . $unit . '.' . (int) $messageId;
		}

		$actions = JAccess::getActionsFromFile(
			JPATH_ADMINISTRATOR . '/components/' . $assetName . '/access.xml',
			"/access/section[@name='" . $unit . "']/"
		);

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $asset));
		}

		return $result;
	}
}
