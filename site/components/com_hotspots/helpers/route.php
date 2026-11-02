<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  Copyright 2011 Daniel Dimitrov. (http://compojoom.com)
 *  All rights reserved
 *
 *  This script is part of the Hotspots project. The Hotspots project is
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

class HotspotsHelperRoute {

    /**
     * @param $id
     * @param int $catid
     * @return string
     */
    public static function getHotspotRoute($id, $catid = 0) {

		$needles = array(
			'hotspot' => (int) $id,
			'category' => (int) $catid,
		);

		$link = 'index.php?option=com_hotspots&view=hotspot&catid='.$catid.'&id=' . $id;

		if ($item = HotspotsHelperRoute::_findItem($needles)) {
			$link .= '&Itemid=' . $item->id;
		}
		return $link;
	}

    /**
     * Ah friendly URLs! How much we love you and how much we hate you!
     *
     * This function tries its best to find the Itemid. First we try to find out if the menu start category
     * matches the category of the hotspot. If this doesn't work we repeat the search but this time we get the
     * most generic result returned.
     *
     * @param $needles
     * @return null || obj Itemid
     */
    private static function _findItem($needles) {
		$component = JComponentHelper::getComponent('com_hotspots');
		$application = JFactory::getApplication();
		$menus = $application->getMenu('site', array());
		$items = $menus->getItems('component_id', $component->id);
		
		$match = null;

        if (count($items)) {
            // try to find a match
            foreach ($items as $item) {
                if ((@$item->query['id'] == $needles['hotspot'])) {
                    $match = $item;
                    break;
                }

                if(!isset($match)) {
                    // check if we have a menu with default category equal to the hotspot category
                    if ((@$item->query['view'] == 'hotspots' && !isset($item->query['layout']))) {
                        $cats = ($item->getParams()->get('hs_startcat')) ? $item->getParams()->get('hs_startcat') : array();
                        if(in_array($needles['category'], $cats)) {
                            $match = $item;
                            break;
                        }
                    }
                }
            }

            // if the previous check didn't work - try one more time
            if (!isset($match)) {
                foreach ($items as $item) {
                    if ((@$item->query['view'] == 'hotspots' && !isset($item->query['layout']))) {
                        $match = $item;
                        break;
                    }
                }
            }
        }

		return $match;
	}

}
