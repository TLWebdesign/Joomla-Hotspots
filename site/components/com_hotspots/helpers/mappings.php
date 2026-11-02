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
 * Class HotspotsHelperLocation
 * Helps with the mappings
 *
 * @since  3.5
 */
class HotspotsHelperMappings
{
	/**
	 * Get the markerID if we have an entry in hotspots_mappings
	 *
	 * @param   int     $foreignId  - the foreign id
	 * @param   string  $component  - the component
	 *
	 * @return int - 0 when nothing found
	 */
	public static function getMarkerId($foreignId, $component)
	{
		$db = JFactory::getDbo();
		$markerId = 0;
		$query = $db->getQuery(true);
		$query->select('marker_id')->from('#__hotspots_mappings')
			->where('foreign_id = ' . $db->q($foreignId))
			->where('component = ' . $db->q($component));

		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			$markerId = $row->marker_id;
		}

		return $markerId;
	}

	/**
	 * Get the markerID if we have an entry in hotspots_mappings
	 *
	 * @param   array   $foreignIds  - the foreign id
	 * @param   string  $component   - the component
	 *
	 * @return array
	 */
	public static function getMarkerIds($foreignIds, $component)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('marker_id')->from('#__hotspots_mappings')
			->where(CompojoomQueryHelper::in('foreign_id', $foreignIds, $db))
			->where('component = ' . $db->q($component));

		$db->setQuery($query);

		return array_keys($db->loadObjectList('marker_id'));
	}

	/**
	 * Gets the mappings between the hotspots fields and the object fields
	 *
	 * @param   string  $mappings  - the defined mappings hotspotcustomfield=object_customfield
	 * @param   object  $object    - the object whre to look for the custom field
	 *
	 * @internal param object $user - the user object
	 *
	 * @return array
	 */
	public static function getMappings($mappings, $object)
	{
		$customFields = array();

		if ($mappings != '')
		{
			$mappings = explode("\n", $mappings);

			foreach ($mappings as $mapping)
			{
				$fields = explode('=', $mapping);

				if (property_exists($object, $fields[1]))
				{
					$customFields[$fields[0]] = $object->$fields[1];
				}
			}
		}

		return $customFields;
	}

	/**
	 * Save a mapping in the mappings table
	 *
	 * @param   int     $markerId   - the hotspots marker id
	 * @param   int     $foreignId  - the foreign id
	 * @param   string  $component  - the component to save the mapping for
	 *
	 * @return void
	 */
	public static function saveMapping($markerId, $foreignId, $component)
	{
		$mappings = JTable::getInstance('Mappings', 'HotspotsTable');

		$mappings->bind(
			array(
				'marker_id' => $markerId,
				'foreign_id' => $foreignId,
				'component' => $component
			)
		);

		$mappings->store();
	}

	/**
	 * Delete a mapping
	 *
	 * @param   array   $foreignId  - the foreign id
	 * @param   string  $component  - the component
	 *
	 * @return void
	 */
	public static function deleteMappings($foreignId, $component)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete('#__hotspots_mappings')->where(CompojoomQueryHelper::in('foreign_id', $foreignId, $db))
			->where('component = ' . $db->q($component));

		$db->setQuery($query);
		$db->execute();
	}
}
