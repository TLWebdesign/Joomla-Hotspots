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

jimport('joomla.filesystem.file');
jimport('joomla.application.categories');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/version.php';
JLoader::discover('HotspotsHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers');
JLoader::discover('HotspotsHelper', JPATH_COMPONENT . '/helpers');
JLoader::registerPrefix('HotspotsLibrary', JPATH_COMPONENT_ADMINISTRATOR . '/libraries');

require_once JPATH_COMPONENT . '/includes/defines.php';
require_once JPATH_COMPONENT . '/views/view.php';
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables');

// Load language
CompojoomLanguage::load('com_hotspots', JPATH_ADMINISTRATOR);
CompojoomLanguage::load('com_hotspots', JPATH_SITE);

$controller = JControllerLegacy::getInstance('Hotspots');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
