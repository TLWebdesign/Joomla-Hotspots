<?php
/**
 * @package    Hotspots
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @copyright  Copyright (C) 2008 - 2014 Compojoom.com . All rights reserved.
 * @license    GNU GPL version 3 or later <http://www.gnu.org/licenses/gpl.html>
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * The updates provisioning Controller
 *
 * @since  4.0
 */
class HotspotsControllerUpdate extends JControllerLegacy
{
	// TLWebdesign patch 2026-11-02: removed updateinfo() and force(), they asked compojoom.com for updates.

	/**
	 * Calls the model Multimedia to migrate the items
	 * and echoes a json string
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function migrateImages()
	{
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$multimedia = JModelLegacy::getInstance('Multimedia', 'HotspotsModel');
		$path = JPATH_SITE . '\media\com_hotspots\images\hotspots';

		echo json_encode($multimedia->migrate('com_hotspots.migrate', $path));
		JFactory::getApplication()->close();
	}
}
