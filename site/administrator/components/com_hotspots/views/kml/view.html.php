<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
jimport('joomla.filesystem');

/**
 * Class HotspotsViewKml
 *
 * @since  3.0
 */
class HotspotsViewKml extends HotspotsView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Ads the toolbar
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_HOTSPOTS_EDIT_KML'), 'kml');
		JToolBarHelper::apply('kml.apply');
		JToolBarHelper::save('kml.save');
		JToolBarHelper::cancel('kml.cancel');
	}
}
