<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       19.09.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_hotspots'))
{
	// TLWebdesign patch 2026-11-02: JError was removed in Joomla 4.
	throw new \Joomla\CMS\Access\Exception\NotAllowed(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

require_once JPATH_LIBRARIES . '/compojoom/include.php';

// Load the category for j2.5
jimport('joomla.application.categories');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/version.php';
JLoader::discover('HotspotsHelper', JPATH_COMPONENT_SITE . '/helpers');
JLoader::discover('hotspotsHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers');
JLoader::registerPrefix('HotspotsLibrary', JPATH_COMPONENT_ADMINISTRATOR . '/libraries');

require_once JPATH_COMPONENT_SITE . '/includes/defines.php';
require_once JPATH_COMPONENT_SITE . '/views/view.php';
require_once JPATH_COMPONENT . '/controller.php';

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

// Load language
CompojoomLanguage::load('com_hotspots', JPATH_SITE);
CompojoomLanguage::load('com_hotspots', JPATH_ADMINISTRATOR);
CompojoomLanguage::load('com_hotspots.sys', JPATH_ADMINISTRATOR);

$input = JFactory::getApplication()->input;

$controller = JControllerLegacy::getInstance('Hotspots');
$controller->execute($input->getCmd('task'));
$controller->redirect();
