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


class plgHotspotslinksContent extends JPlugin
{
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->loadLanguage('plg_hotspotslinks_content.sys');
    }

    public function onCreateLink($id) {

        $link = '';
        $route = JPATH_ROOT . '/components/com_content/helpers/route.php';

        if (version_compare(JVERSION, '4.0', 'gt')) {
            $route = JPATH_ROOT . '/components/com_content/src/Helper/RouteHelper.php';
        }

        if(file_exists($route)) {
            require_once($route);
            if($id) {
                $link = JRoute::_(ContentHelperRoute::getArticleRoute($id, $this->getCatId($id)));
            }
        }
        return $link;
    }

    private function getCatId($contentId) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('catid')->from('#__content')->where('id='.$db->quote($contentId));
        $db->setQuery($query);
        $cat = $db->loadObject();
        if(is_object($cat)) {
            return $cat->catid;
        }
        return 0;
    }
}
