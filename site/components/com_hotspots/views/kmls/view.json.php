<?php
/**
 * @author Daniel Dimitrov
 * @date: 23.03.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE .'/views/json.php');
class hotspotsViewKmls extends HotspotsJson
{

	public function display($tpl = null)
	{
		$this->kmls = $this->get('Kmls');
		parent::display();
	}
}