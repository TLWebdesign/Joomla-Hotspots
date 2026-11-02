<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsViewCustomfield
 *
 * @since  2.0
 */
class HotspotsViewCustomfield extends JViewLegacy
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
		$this->item = $this->get('item');
		$this->form = $this->get('form');
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds a toolbar
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		if ($this->item->id)
		{
			JToolBarHelper::title('Hotspots - ' . JText::_('COM_HOTSPOTS_EDIT_CUSTOM_FIELD'));
		}
		else
		{
			JToolBarHelper::title('Hotspots - ' . JText::_('COM_HOTSPOTS_CREATE_CUSTOM_FIELD'));
		}

		$canDo = HotspotsHelperSettings::getActions();

		if (HOTSPOTS_PRO)
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('customfield.apply');
				JToolBarHelper::save('customfield.save');
			}

			JToolBarHelper::cancel('customfield.cancel');
		}
	}
}
