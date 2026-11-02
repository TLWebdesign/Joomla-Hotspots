<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       02.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die ('Restricted access');

// Load the compojoom framework
require_once JPATH_LIBRARIES . '/compojoom/include.php';
$adminPath = JPATH_ADMINISTRATOR . '/components/com_hotspots';
$frontendPath = JPATH_BASE . '/components/com_hotspots';

jimport('joomla.filesystem.file');
require_once $adminPath . '/version.php';
JLoader::discover('HotspotsHelper', $adminPath . '/helpers');
JLoader::discover('HotspotsHelper', $frontendPath . '/helpers');
require_once $frontendPath . '/includes/defines.php';
require_once $frontendPath . '/views/view.php';
JTable::addIncludePath($adminPath . '/components/com_hotspots/tables');

// Load language
CompojoomLanguage::load('com_hotspots', JPATH_ADMINISTRATOR);
CompojoomLanguage::load('com_hotspots', JPATH_SITE);
