<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       04.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('color');

JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers');

/**
 * JFormFieldCategorymarkerimage
 *
 * @since  3.0
 */
class JFormFieldhotspotscolor extends JFormFieldColor
{
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'categorymarkericon';


	/**
	 * Creates the output
	 *
	 * @return string
	 */
	protected function getInput()
	{
		// On joomla 3.0 the format is in HEX and not in rgb
		if ($this->value && strstr($this->value, ','))
		{
			$this->value = hotspotsHelperColor::rgb2hex($this->value);
		}

		return parent::getInput();
	}
}
