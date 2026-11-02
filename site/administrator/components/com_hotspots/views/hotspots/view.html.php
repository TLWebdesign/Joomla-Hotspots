<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       22.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
jimport('joomla.html.html.category');

/**
 * Class HotspotsViewHotspots
 *
 * @since  2.0
 */
class HotspotsViewHotspots extends JViewLegacy
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
		$this->state = $this->get('State');
		$appl = JFactory::getApplication();

		// Filter
		$context = 'com_hotspots.marker.list.';
		$filter_sectionid = $appl->getUserStateFromRequest($context . 'filter_category_id', 'filter_category_id', 0, 'int');
		$filter_order = $appl->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'cc.catid', 'cmd');
		$filter_order_Dir = $appl->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

		$this->list = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// Get the categories and add a select category entry to it
		$categories = JHtml::_('category.categories', 'com_hotspots');
		array_unshift($categories, JHtml::_('select.option', '', JText::_('JOPTION_SELECT_CATEGORY')));

		$languages = Jhtml::_('contentlanguage.existing', true, true);
		array_unshift($languages, JHtml::_('select.option', '', JText::_('JOPTION_SELECT_LANGUAGE')));
		$lists['sectionid'] = JHTML::_('select.genericlist', $categories, 'filter_category_id', array('onchange' => 'this.form.submit();', 'class' => 'form-control'), 'value', 'text', $filter_sectionid);
		$lists['language'] = JHTML::_('select.genericlist', $languages, 'filter_language', array('onchange' => 'this.form.submit();', 'class' => 'form-control'), 'value', 'text', $this->state->get('filter.language'));

		// Ordering allowed ?
		$ordering = ($lists['order'] == 'cc.catid');

		$this->lists = $lists;
		$this->ordering = $ordering;

		if (JFactory::getApplication()->input->get('layout') == 'element')
		{
			$this->setLayout('element');
		}

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

		JToolBarHelper::title(JText::_('COM_HOTSPOTS_MARKERS'), 'generic.png');

		if (HOTSPOTS_PRO || (!HOTSPOTS_PRO && $this->pagination->total <= 100))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('hotspot.add');
			}
		}

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('hotspot.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::publishList('hotspots.publish');
			JToolBarHelper::unpublishList('hotspots.unpublish');
		}

		JToolBarHelper::deleteList(JText::_('COM_HOTSPOTS_DO_YOU_REALLY_WANTO_TO_REMOVE_THIS_MARKER'), 'hotspots.remove');

		// Add a batch button
		if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state'))
		{
			// Get the toolbar object instance
			$bar   = JToolBar::getInstance('toolbar');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');

            $layout = new JLayoutFile('hotspots.toolbar.geocode');

            $dhtml = $layout->render(array('title' => 'Geocode'));
            $bar->appendButton('Custom', $dhtml, JText::_('COM_HOTSPOTS_GEOCODE'));
		}
	}
}
