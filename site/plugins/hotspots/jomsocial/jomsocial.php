<?php
/**
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       10.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

/**
 * Class PlgHotspotsJomSocial
 *
 * @since  3
 */
class PlgHotspotsJomSocial extends JPlugin
{
	/**
	 * The constructor
	 *
	 * @param   object  &$subject  - the subject
	 * @param   array   $params    - the params
	 */
	public function __construct(&$subject, $params)
	{
		JPlugin::loadLanguage('plg_hotspots_jomsocial', JPATH_ADMINISTRATOR);
		parent::__construct($subject, $params);
	}

	/**
	 * Function is executed after the hotspot is saved
	 *
	 * @param   string  $context  - the context
	 * @param   object  $data     - the data
	 *
	 * @return bool
	 */
	public function onAfterHotspotSave($context, $data)
	{
		if ($context != 'com_hotspots.hotspot')
		{
			return false;
		}

		$userPoints = $this->params->get('userPointsOnAddHotspot', 0);
		$activityStream = $this->params->get('activityStreamNewHotspot', 0);

		if ($userPoints)
		{
			$this->setUserPointsOnAddHotspot($data);
		}

		if ($activityStream)
		{
			$this->setActivity($data, 'save');
		}

		return true;
	}

	/**
	 * Assigns the user points
	 *
	 * @param   object  $data  - the hotspots data
	 *
	 * @return void
	 */
	private function setUserPointsOnAddHotspot($data)
	{
		include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
		include_once JPATH_ROOT . '/components/com_community/libraries/userpoints.php';
		CuserPoints::assignPoint('com_hotspots.addHotspot', $data->created_by);
	}

	/**
	 * Ads the activity
	 *
	 * @param   object  $data      - the hotspots data
	 * @param   string  $activity  - type of activity
	 *
	 * @return void
	 */
	private function setActivity($data, $activity)
	{
		jimport('joomla.filesystem.file');
		JLoader::discover('HotspotsHelper', JPATH_BASE . '/components/com_hotspots/helpers/');
		$utils = JPATH_ROOT . '/components/com_hotspots/hotspots.php';
		$appl = JFactory::getApplication();

		// If hotspots is not installed, if the hotspot is unpublished per default or if
		// We are in backend we don't assign any activity
		if ($data->state == 0 || !JFile::exists($utils) || $appl->isClient("administrator"))
		{
			return;
		}

		$target = 0;

		if ($activity == 'save')
		{
			$catTitle = JFilterOutput::stringURLSafe($this->getCatTitle($data->catid));
			$url = JRoute::_(
				'index.php?option=com_hotspots&view=hotspot&catid=' .
				$data->catid . ':' . $catTitle . '&id=' . $data->id . ':'
				. JFilterOutput::stringURLSafe($data->title) .
				'&Itemid=' . HotspotsHelperUtils::getItemid('com_hotspots', 'hotspots')
			);
			$title = $data->title;

			$actTitle = JText::_('JOMSOCIAL_ADDHOTSPOTS') . ':' . ' <a href="' . $url . '">' . $title . '</a>';

			$content = stripslashes($data->description_small);
		}

		$act = new stdClass;
		$act->cmd = 'com_hotspots.addHotspot';
		$act->actor = $data->created_by;
		$act->target = $target;
		$act->title = $actTitle;
		$act->content = $content;
		$act->app = 'com_hotspots';
		$act->cid = $data->id;

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);
	}

	/**
	 * Gets the category title
	 *
	 * @param   int  $id  - the category id
	 *
	 * @return mixed
	 */
	private function getCatTitle($id)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT title  FROM ' . $db->qn('#__categories')
			. ' WHERE ' . $db->qn('id') . '=' . $db->q($id);
		$db->setQuery($query, 0, 1);

		return $db->loadObject()->title;
	}
}
