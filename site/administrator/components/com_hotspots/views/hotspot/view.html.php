<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       12.11.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Class HotspotsViewHotspot
 *
 * @since  4.0
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
		$this->form = $this->get('Form');
		$this->hotspot = $this->get('Item');

		$this->canDo = HotspotsHelperSettings::getActions($this->hotspot->id);

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds a toolbar with options
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		JToolBarHelper::apply('hotspot.apply');
		JToolBarHelper::save('hotspot.save');
		JToolbarHelper::save2new('hotspot.save2new');
		JToolBarHelper::cancel('hotspot.cancel');
	}
}
