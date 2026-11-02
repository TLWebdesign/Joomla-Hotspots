<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       13.03.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * Class JFormFieldHotspotsK2Extrafields
 *
 * @since  3.6
 */
class JFormFieldHotspotsK2Extrafields extends JFormFieldList
{
	protected $name = 'HotspotsK2Extrafields';

	/**
	 * Get the k2 custom fields of type textfield
	 *
	 * @return    array    The field option objects.
	 */
	public function getOptions()
	{
		$options = array();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__k2_extra_fields')
			->where('published = 1')
			->where('type = ' . $db->q('textfield'))
			->order('ordering');

		$db->setQuery($query);
		$items = $db->loadObjectList();
		$attributes = null;

		// If we don't have extra fields, return an empty array
		if (empty($items))
		{
			return array();
		}

		foreach ($items as $item)
		{
			$options[] = JHTML::_('select.option', $item->id, '   ' . $item->name);
		}

		return $options;
	}
}
