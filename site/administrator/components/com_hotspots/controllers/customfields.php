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

jimport('joomla.application.component.controlleradmin');
/**
 * Class HotspotsControllerCustomfields
 *
 * @since  4.0
 */
class HotspotsControllerCustomfields extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  The config array
	 *
	 * @return  JModel
	 */
	public function getModel($name = 'Customfield', $prefix = 'HotspotsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
