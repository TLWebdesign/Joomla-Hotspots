<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       31.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Class HotspotsViewKmls
 *
 * @since  3
 */
class HotspotsViewKmls extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   null  $tpl  - the template to use
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$appl = JFactory::getApplication();
		$this->kmls = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		$context = 'com_hotspots.kmls.list.';
		$filter_order = $appl->getUserStateFromRequest($context . 'filter_order', 'filter_order', '', 'cmd');
		$filter_order_Dir = $appl->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

		// Table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		$this->lists = $lists;

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add toolbar buttons
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		if (HOTSPOTS_PRO)
		{
			JToolBarHelper::addNew('kml.add');
			JToolBarHelper::editList('kml.edit');
			JToolBarHelper::publishList('kmls.publish');
			JToolBarHelper::unpublishList('kmls.unpublish');
			JToolBarHelper::deleteList(JText::_('COM_HOTSPOTS_DO_YOU_REALLY_WANTO_TO_REMOVE_THIS_KML_FILE'), 'kmls.remove');
		}

		JToolBarHelper::help('screen.hotspots', false, 'https://compojoom.com/support/documentation/hotspots?tmpl=component');
	}
}
