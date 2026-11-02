<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('category');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @since  11.1
 */
class JFormFieldHotspotscategory extends JFormFieldCategory
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'hotspotscategory';

	/**
	 * We will get the options from the parent function. Then we will check
	 * if the enable_category option is set to true and if it is, then we will only
	 * leave the options that are specified in catid
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Get all categories
		$options = parent::getOptions();

		// Should we just use the selected options in the menu?
		if(HotspotsHelperSettings::get('enable_category', 0))
		{
			$selected = HotspotsHelperSettings::get('catid');
			$newOptions = array();
			// Ads Select category text
			$newOptions[] = $options[0];

			// Go over the selected items
			foreach($selected as $id)
			{
				// Find only the options that we need
				foreach($options as $key => $category)
				{
					if($category->value == $id) {
						$newOptions[] = $options[$key];
						break;
					}
				}
			}

			// Clean up
			unset($options);

			// return our new options
			return $newOptions;
		}

		return $options;
	}
}
