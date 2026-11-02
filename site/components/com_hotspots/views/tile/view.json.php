<?php
/**
 * @author Daniel Dimitrov - compojoom.com
 * @date: 08.08.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE . '/views/json.php');

class hotspotsViewTile extends HotspotsJson
{
    public function display($tpl = null) {
        $tile = $this->getModel();
        $this->items = $tile->getItems(false);

        parent::display();
    }
}