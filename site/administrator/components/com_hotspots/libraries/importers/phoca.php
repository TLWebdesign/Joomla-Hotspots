<?php
/**
 * @package    Com_Hotspots
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       23.02.2016
 *
 * @copyright  Copyright (C) 2008 - 2016 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsImporterPhoca
 *
 * Imports the categories and markers from Phocamaps
 *
 * @since  5.2.2
 */
class HotspotsImporterPhoca
{
	/**
	 * Do the import
	 *
	 * @return bool|mixed
	 */
	public function import()
	{
		// Import the categories and returns a mapping of the phocacats to hotspots cats
		$categories = $this->importCategories();

		return $this->importMarkers($categories);
	}

	/**
	 * Import the phocamaps as categories in Hotspots
	 *
	 * @return array|int
	 */
	private function importCategories()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, title, alias')
			->from('#__phocamaps_map');

		$db->setQuery($query);

		$phocaMaps = $db->loadObjectList();

		$categoryMapping = array();

		foreach ($phocaMaps as $map)
		{
			$table = JTable::getInstance('Category', 'JTable');

			$category_array = array();
			$category_array['title'] = $map->title;
			$category_array['extension'] = "com_hotspots";
			$table->published = 1;
			$table->params = '{"target":"","image":""}';
			$category_array['created_user_id'] = JFactory::getUser()->id;
			$category_array['parent'] = 1;
			$table->setLocation(1, 'last-child');

			$category_array['access'] = 1;
			$category_array['language'] = '*';
			$category_array['metadata'] = '{"page_title":"","author":"","robots":"", "tags":null}';
			$rules = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';
			$table->setRules($rules);

			$table->bind($category_array);
			$table->check();
			$table->store();

			$categoryMapping[$map->id] = $table->id;
		}

		return $categoryMapping;
	}

	/**
	 * Import the phoca markers
	 *
	 * @param   array  $categories  - the categories mapping array
	 *
	 * @return bool|mixed
	 */
	private function importMarkers($categories)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from('#__phocamaps_marker');

		$db->setQuery($query);

		$phocaMarkers = $db->loadObjectList();

		$markers = array();

		foreach ($phocaMarkers as $phocaMarker)
		{
			$markers[] = array(
				'title' => $phocaMarker->title,
				'alias' => $phocaMarker->alias,
				'catid' => $categories[$phocaMarker->catid],
				'gmlat' => $phocaMarker->latitude,
				'gmlng' => $phocaMarker->longitude,
				'description_small' => $phocaMarker->description,
				'state' => $phocaMarker->published,
				'access' => $phocaMarker->access,
				'language' => $phocaMarker->language,
				'created_by' => JFactory::getUser()->id
			);
		}

		if (count($markers))
		{
			$query->clear();

			$query->insert('#__hotspots_marker')
				->columns(implode(',', array_keys($markers[0])))
				->values(
					// Values expects array (value, value, value), so let's map it to this
					array_map(
						function($array) use ($db) {
							// Apply db quoting on each value of the array
							return implode(',', array_map(
									function($v) use ($db){
										return $db->q($v);
									}, $array
								)
							);
						},
						$markers
					)
				);

			$db->setQuery($query);

			return $db->execute();
		}

		return false;
	}
}
