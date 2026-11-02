<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       11.03.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperContent
 *
 * @since  3.5
 */
class HotspotsHelperContent
{
	/**
	 * Replaces placeholder in the text with matches from the user object
	 *
	 * @param   string $text   - the text to replace
	 * @param   object $object - the object from where to take the values
	 * @param   string $func   - any object function that reads the properties of the object
	 *
	 * @return mixed
	 */
	public static function replacePlaceholders($text, $object, $func = '')
	{
		// Look for any variables in the Text
		preg_match_all('/{\w+}/', $text, $matches);

		if (count($matches))
		{
			foreach ($matches[0] as $value)
			{
				$property = str_replace('{', '', (str_replace('}', '', $value)));

				if ($func)
				{
					$text = str_replace($value, $object->$func($property), $text);
				}
				elseif (property_exists($object, $property))
				{
					$text = str_replace($value, $object->$property, $text);
				}
			}
		}

		return $text;
	}
}
