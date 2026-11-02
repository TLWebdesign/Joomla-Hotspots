<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsControllerImport
 *
 * @since  3.0
 */
class HotspotsControllerMultimedia extends CompojoomControllerMultimedia
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return object
	 */
	public function getModel($name = 'Multimedia', $prefix = 'HotspotsModel', $config = array())
	{
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR . '/models');

		return parent::getModel($name, $prefix, $config);
	}
}
