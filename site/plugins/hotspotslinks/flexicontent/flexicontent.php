<?php
/* * *************************************************************
 *  Copyright notice
 *
 *  Copyright 2012 Daniel Dimitrov. (http://compojoom.com)
 *  All rights reserved
 *
 *  This script is part of the Hotspots project. The hotspots project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

defined('_JEXEC') or die('Restricted access');

class plgHotspotslinksFlexicontent extends JPlugin
{
	public function __construct($subject, $params = array())
	{
		parent::__construct($subject, $params);

		$this->loadLanguage('plg_hotspotslinks_flexicontent.sys');
	}

	public function onCreateLink($id)
	{
		$link = '';
		$route = JPATH_ROOT . '/components/com_flexicontent/helpers/route.php';
		if (file_exists($route))
		{
			require_once($route);
			if ($id)
			{
				$item = $this->getItemData($id);
				if ($item)
				{
					$link = FlexicontentHelperRoute::getItemRoute($id . ':' . urlencode($item->alias), $item->catid);
				}
				else
				{
					$link = FlexicontentHelperRoute::getItemRoute($id);
				}
			}
		}
		return JRoute::_($link, false);
	}


	/**
	 * Get the category id for the url
	 *
	 * @param $itemId
	 *
	 * @return int
	 */
	private function getItemData($itemId)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select(array('alias', 'catid'))->from($db->qn('#__content'))->where($db->qn('id') . '=' . $db->q($itemId));
		$db->setQuery($query, 0, 1);
		return $db->loadObject();

	}
}