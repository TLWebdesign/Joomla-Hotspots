<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsViewHotspots
 *
 * @since  2.0
 */
class HotspotsViewCustomfields extends JViewLegacy
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
		$this->items = $this->get('items');
		$this->state = $this->get('state');
		$this->pagination = $this->get('pagination');
		$this->canDo = HotspotsHelperSettings::getActions();

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
		$canDo = HotspotsHelperSettings::getActions();

		if (HOTSPOTS_PRO)
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('customfield.add');
			}

			if ($canDo->get('core.edit'))
			{
				JToolBarHelper::editList('customfield.edit');
			}

			if ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::publishList('customfields.publish');
				JToolBarHelper::unpublishList('customfields.unpublish');
			}

			JToolBarHelper::deleteList(JText::_('COM_HOTSPOTS_DO_YOU_REALLY_WANTO_TO_REMOVE_THIS_CUSTOMFIELD'), 'customfields.delete');
		}


		JToolBarHelper::help('screen.hotspots', false, 'https://compojoom.com/support/documentation/hotspots?tmpl=component');

	}
}
