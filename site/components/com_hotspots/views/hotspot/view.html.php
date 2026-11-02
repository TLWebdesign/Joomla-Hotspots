<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       27.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Class HotspotsViewHotspot
 *
 * @since  3.0
 */
class HotspotsViewHotspot extends HotspotsView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->profile = '';
		$this->customFields = array();
		$mainframe = JFactory::getApplication();
		$model = $this->getModel();
		$customFieldsModel = JModelLegacy::getInstance('Customfields', 'HotspotsModel');
		$user = JFactory::getUser();
		$hotspot = $this->prepareHotspot($model->getHotspot());
		$category = HotspotsHelperUtils::prepareCategory(JCategories::getInstance('Hotspots')->get($hotspot->catid));

		$settings = $this->prepareSettings();
		$pathway = $mainframe->getPathWay();
		$pathway->additem($hotspot->title, '');

		$this->hotspot = $hotspot;
		$this->category = $category;
		$this->settings = $settings;
		$this->name = $user->name;
		$customFields = $customFieldsModel->getFields('com_hotspots.hotspot', $hotspot->catid);

		// Make a new array that has the slug for key
		foreach ($customFields as $field)
		{
			$this->customFields[$field->slug] = $field;
		}

		if (HotspotsHelperSettings::get('profile_link', ''))
		{
			$profileSystem = CompojoomProfiles::getInstance(HotspotsHelperSettings::get('profile_link', ''));
			$this->profile = $profileSystem->getLink($this->hotspot->created_by);
		}

		if (!$this->hotspot->state)
		{
			if (!HotspotsHelperSecurity::canEdit($this->hotspot))
			{
				throw new Exception('The resource doesn\'t exist', 404);
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_HOTSPOTS_UNPUBLISHED_BUT_VISIBLE_TO_MODERATOR'), 'notice');
			}
		}

		$this->galleria = CompojoomGalleria::getData($this->hotspot->id, 'com_hotspots.hotspot', false);

		parent::display($tpl);
	}

	/**
	 * Sets some settings so that they can be easily accessed
	 *
	 * @return JObject
	 */
	private function prepareSettings()
	{
		$settings = new JObject;
		$properties = array(
			'show_address' => HotspotsHelperSettings::get('show_address', 1),
			'show_author' => HotspotsHelperSettings::get('show_author', 1),
			'show_date' => HotspotsHelperSettings::get('show_date', 1),
			'show_detailpage' => HotspotsHelperSettings::get('hotspot_detailpage', 1)
		);

		$settings->setProperties($properties);

		return $settings;
	}

	/**
	 * Prepare the hotspot date for echoing
	 *
	 * @param   object  $hotspot  - the hotspot object
	 *
	 * @return mixed
	 */
	private function prepareHotspot($hotspot)
	{
		if (HotspotsHelperSettings::get('marker_allow_plugin', 0) == 1)
		{
			$hotspot->description_small = JHTML::_('content.prepare', $hotspot->description_small, '');
			$hotspot->description = JHTML::_('content.prepare', $hotspot->description, '');
		}

		$hotspot->created = HotspotsHelperUtils::getLocalDate($hotspot->created);

		if ($hotspot->customfields)
		{
			$hotspot->customfields = json_decode($hotspot->customfields);
		}

		$hotspot->params = new JRegistry($hotspot->params);

		return $hotspot;
	}
}
