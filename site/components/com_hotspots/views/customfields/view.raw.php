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
 * The view class for raw requests
 *
 * Class HotspotsViewHotspot
 *
 * @package  Hotspots
 * @since    3.4
 */
class HotspotsViewCustomfields extends HotspotsView
{
	/**
	 * The display funciton
	 *
	 * @param   null  $tpl  - the template
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$id = JFactory::getApplication()->input->getInt('id');
		$catId = JFactory::getApplication()->input->getInt('catid');
		$model = $this->getModel();

		$this->items = $model->getFields('com_hotspots.hotspot', $catId);

		$this->form = new JForm('customfields', array('control' => 'jform'));
		$this->form->load(CompojoomFormCustom::generateFormXML($this->items, 'com_hotspots'));

		if ($id)
		{
			$hotspotsModel = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
			$hotspot = $hotspotsModel->getHotspot();

			if (!HotspotsHelperSecurity::canEdit($hotspot))
			{
				throw new Exception('The resource doesn\'t exist', 404);
			}

			$this->form->bind(array('customfields' => json_decode($hotspot->customfields)));
		}

		parent::display();
	}
}
