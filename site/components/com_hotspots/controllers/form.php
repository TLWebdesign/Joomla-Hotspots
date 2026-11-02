<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       11.01.16
 *
 * @copyright  Copyright (C) 2008 - 2016 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controllerform');

/**
 * Class HotspotsControllerForm
 *
 * @since  4.0
 */
class HotspotsControllerForm extends JControllerForm
{
	public $context = 'hotspot';

	public $view_item = 'form';

	public $view_list = 'userhotspots';

	/**
	 * TLWebdesign patch 2026-11-02: the form posts to hotspot.save, which filters the owner, dates and
	 * permissions. form.save went straight to the model without those checks, so it is blocked.
	 *
	 * @param   string  $key     Not used
	 * @param   string  $urlVar  Not used
	 *
	 * @return  boolean
	 */
	public function save($key = null, $urlVar = null)
	{
		throw new \Joomla\CMS\Access\Exception\NotAllowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
	}

	/**
	 * Check if the user has the sufficient user rights to edit the hotspot
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return    boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;

		// Need to do a lookup from the model.
		$record = $this->getModel()->getItem($recordId);

		if (empty($record))
		{
			return false;
		}

		return HotspotsHelperSecurity::authorise('edit', $record);
	}
}
