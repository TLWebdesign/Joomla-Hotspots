<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       11.03.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers/');

// Load the compojoom framework
require_once JPATH_LIBRARIES . '/compojoom/include.php';

/**
 * Class PlgHotspotslinksMatukio
 *
 * @since  3.6
 */
class PlgHotspotslinksCommunity extends JPlugin
{
	/**
	 * The constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $params    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $params = array())
	{
		parent::__construct($subject, $params);

		$this->loadLanguage('plg_hotspotslinks_community.sys');
	}

	/**
	 * Function to create a proper link to Matukio item
	 *
	 * @param   int  $id  - the id
	 *
	 * @return string
	 */
	public function onCreateLink($id)
	{
		if (JComponentHelper::isEnabled('com_community'))
		{
			if ($id)
			{
				$profileSystem = CompojoomProfiles::getInstance('community');

				return $profileSystem->getLink($id);
			}
		}

		return '';
	}
}
