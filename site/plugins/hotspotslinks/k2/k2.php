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

/**
 * Class plgHotspotslinksK2
 *
 * @since  3.6
 */
class PlgHotspotslinksK2 extends JPlugin
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

		$this->loadLanguage('plg_hotspotslinks_k2.sys');
	}

	/**
	 * Creates a link to k2
	 *
	 * @param   int  $id  - the k2 item id
	 *
	 * @return string - a link to the k2 item
	 */
	public function onCreateLink($id)
	{
		$link = '';
		$route = JPATH_ROOT . '/components/com_k2/helpers/route.php';

		if (file_exists($route))
		{
			require_once $route;

			if ($id)
			{
				$item = $this->getItemData($id);

				if ($item)
				{
					$link = K2HelperRoute::getItemRoute($id . ':' . urlencode($item->alias), $item->catid);
				}
				else
				{
					$link = K2HelperRoute::getItemRoute($id);
				}
			}
		}

		return JRoute::_($link, false);
	}


	/**
	 * Get the category id for the url
	 *
	 * @param   int  $itemId  - the k2 item id
	 *
	 * @return int
	 */
	private function getItemData($itemId)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select(array('alias', 'catid'))->from($db->qn('#__k2_items'))->where($db->qn('id') . '=' . $db->q($itemId));
		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}
}
