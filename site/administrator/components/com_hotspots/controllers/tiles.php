<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       13.08.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerlegacy');

/**
 * Class HotspotsControllerTiles
 *
 * @since  4.0
 */
class HotspotsControllerTiles extends JControllerLegacy
{
	/**
	 * Deletes all Generated tiles
	 *
	 * @return void
	 */
	public function delete()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$appl = JFactory::getApplication();
		$path = JPATH_ROOT . '/media/com_hotspots/tiles';
		$files = JFolder::files($path, false, true, array('index.html'));
		$msg = 'COM_HOTSPOTS_TILES_SUCCESSFULLY_DELETED';

		if (!empty($files))
		{
			if (JFile::delete($files) !== true)
			{
				$msg = 'COM_HOTSPOTS_TILES_DELETE_UNSUCCESSFUL';
			}
		}

		$appl->redirect('index.php?option=com_hotspots&view=dashboard', JText::_($msg));
	}
}
