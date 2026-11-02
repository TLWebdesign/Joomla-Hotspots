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
		$tpl = 'raw';
		$id = JFactory::getApplication()->input->getInt('id');
		$catId = JFactory::getApplication()->input->getInt('catid');
		$model = JModelLegacy::getInstance('Customfieldsconfig', 'CompojoomModel');

		$this->items = $model->getFields('com_hotspots.hotspot', $catId);

		$this->form = new JForm('customfields', array('control' => 'jform'));
		$this->form->load(CompojoomFormCustom::generateFormXML($this->items, 'com_hotspots'));

		if ($id)
		{
			$hotspotsModel = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
			$hotspot = $hotspotsModel->getItem();
			$this->form->bind(array('customfields' => json_decode($hotspot->customfields)));
		}

		parent::display($tpl);
	}
}
