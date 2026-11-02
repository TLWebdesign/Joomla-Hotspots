<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       10.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Load the compojoom framework
require_once JPATH_LIBRARIES . '/compojoom/include.php';

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$list = ModHotspotsHelper::getList($params);

$images = CompojoomGalleria::getData(array_map(
	function($o) {
		return $o->id;
	},
	$list
), 'com_hotspots.hotspot', false, 'small', 'medium');

require JModuleHelper::getLayoutPath('mod_hotspots_list', $params->get('layout', 'default'));
