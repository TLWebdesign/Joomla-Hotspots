<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       13.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controlleradmin');


class HotspotsControllerKmls extends JControllerAdmin
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('unpublish', 'publish');
	}

	public function getModel($name = 'Kml', $prefix = 'HotspotsModel')
	{
		$model = parent::getModel($name, $prefix);

		return $model;
	}

	public function remove()
	{
		/*
		 * TLWebdesign patch 2026-11-02: there was no token or permission check and the ids went into
		 * the SQL unescaped (SQL injection through a link opened by an administrator).
		 */
		\Joomla\CMS\Session\Session::checkToken() or jexit(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'));

		$appl = JFactory::getApplication();

		if (!$appl->getIdentity()->authorise('core.delete', 'com_hotspots'))
		{
			$this->setRedirect('index.php?option=com_hotspots&view=kmls', JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'error');

			return;
		}

		$cid = \Joomla\Utilities\ArrayHelper::toInteger($appl->input->get('cid', array(), 'array'));
		$db = JFactory::getDbo();

		if (count($cid))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__hotspots_kmls'))
				->whereIn($db->quoteName('hotspots_kml_id'), $cid);

			try
			{
				$db->setQuery($query)->execute();
			}
			catch (\RuntimeException $e)
			{
				$appl->enqueueMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect('index.php?option=com_hotspots&view=kmls');
	}
}
