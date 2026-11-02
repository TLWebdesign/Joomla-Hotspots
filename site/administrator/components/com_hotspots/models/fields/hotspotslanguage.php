<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       26.08.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * Class JFormFieldHotspotsLanguage
 *
 * @since  3.6
 */
class JFormFieldHotspotsLanguage extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'HotspotsLanguage';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$components = $this->getAttribute('languages', '');
		}
		else
		{
			// Hack for joomla 2.5...
			$components = (string) $this->element->attributes()->languages;
		}

		$language = JFactory::getLanguage();


		if ($components)
		{
			$components = explode(',', $components);

			foreach ($components as $component)
			{
				$component = trim($component);
				$language->load($component, JPATH_ADMINISTRATOR, 'en-GB', true);
				$language->load($component, JPATH_ADMINISTRATOR, $language->getDefault(), true);
				$language->load($component, JPATH_ADMINISTRATOR, null, true);
			}
		}

		return '';
	}
}
