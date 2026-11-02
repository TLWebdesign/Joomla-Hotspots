<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       16.11.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsViewHotspots
 *
 * @since  4.0
 */
class HotspotsViewUserHotspots extends HotspotsView
{
	/**
	 * Display the map or user hotspots
	 *
	 * @param   string  $tpl  - the template name
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$this->user = JFactory::getUser();
		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
		$model = JModelLegacy::getInstance('Hotspots', 'HotspotsModel');

		$this->pagination = $model->getPagination();

		// Get filter form.
		$this->filterForm = $model->getFilterForm();

		$this->activeFilters = $model->getActiveFilters();

		$ordering = explode(' ', HotspotsHelperSettings::get('hotspots_order', 'a.title DESC'));
		$model->setState('list.ordering', (strstr('a.', $ordering[0])) === false ? 'a.'.$ordering[0] : $ordering[0]);
		$model->setState('list.direction', $ordering[1]);
		$hotspots = $model->getItems();

		foreach ($hotspots as $key => $hotspot)
		{
			$urlcat = $hotspot->catid . ':' . JFilterOutput::stringURLSafe($hotspot->cat_name);
			$urlid = $hotspot->id . ':' . JFilterOutput::stringURLSafe($hotspot->title);
			$hotspots[$key]->link = JRoute::_(HotspotsHelperRoute::getHotspotRoute($urlid, $urlcat));
		}

		$this->hotspots = $hotspots;
		$this->state = $model->getState();
		parent::display($tpl);
	}
}
