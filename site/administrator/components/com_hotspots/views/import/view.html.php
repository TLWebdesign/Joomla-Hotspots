<?php
/**
 * Hotspots - Adminstrator
 * @package  Joomla!
 * @Copyright (C) 2012 Daniel Dimitrov - compojoom.com
 * @All      rights reserved
 * @Joomla   ! is Free Software
 * @Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die ('Restricted access');
jimport('joomla.application.component.view');

class HotspotsViewImport extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->addToolbar();
		parent::display($tpl);
	}

	private function addToolbar()
	{
		JToolBarHelper::help('screen.hotspots', false, 'https://compojoom.com/support/documentation/hotspots?tmpl=component');
	}
}
