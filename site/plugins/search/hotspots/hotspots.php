<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       17.05.16
 *
 * @copyright  Copyright (C) 2008 - 2016 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Hotspots Search plugin
 *
 * @since  5.0
 */
class PlgSearchHotspots extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * Returns the search areas
	 *
	 * @return array An array of search areas
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'hotspots' => 'Hotspots'
		);

		return $areas;
	}

	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		return $this->search($text, $phrase, $ordering, $areas);
	}

	/**
	 * Do the search
	 *
	 * @param   string  $text      - the text we are looking for
	 * @param   string  $phrase    - phrase
	 * @param   string  $ordering  - ordering
	 * @param   null    $areas     - areas to search in
	 *
	 * @return array|mixed
	 */
	private function search($text, $phrase = '', $ordering = '', $areas = null)
	{
		JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers');

		if (!$text)
		{
			return array();
		}

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$db = JFactory::getDBO();

		if ($phrase == 'exact')
		{
			$text = $db->Quote('%' . $db->escape($text, true) . '%', false);
			$where = "(LOWER(m.title) LIKE $text)
			   OR (LOWER(m.description_small) LIKE $text)" .
				" OR (LOWER(m.description) LIKE $text)
				 OR (LOWER(m.street) LIKE $text)" .
				" OR (LOWER(m.plz) LIKE $text)";
		}
		else
		{
			$words = explode(' ', $text);
			$wheres = array();

			foreach ($words as $word)
			{
				$word = $db->Quote('%' . $db->escape($word, true) . '%', false);
				$wheres[] = "(LOWER(m.title) LIKE $word)
					   OR (LOWER(m.description_small) LIKE $word)" .
					" OR (LOWER(m.description) LIKE $word)
		  			   OR (LOWER(m.street) LIKE $word)" .
					" OR (LOWER(m.plz) LIKE $word)";
			}

			if ($phrase == 'all')
			{
				$seperator = "AND";
			}
			else
			{
				$seperator = "OR";
			}

			$where = '(' . implode(") $seperator (", $wheres) . ')';
		}

		$where .= ' AND c.published = 1 AND m.state = 1';

		switch ($ordering)
		{
			case 'oldest':
				$order = 'm.created ASC';
				break;
			case 'alpha':
				$order = 'm.title ASC';
				break;
			case 'newest':
			default:
				$order = 'm.created DESC';
				break;
		}

		$pluginParams = $this->params;
		$limit = $pluginParams->get('search_limit', 50);

		$query = "SELECT m.id, m.title AS title, m.description_small AS text, m.created AS created, m.catid, c.id as cat_id, " .
			" c.title  AS section, " .
			" '2' AS browsernav" .
			' FROM ' . $db->qn('#__hotspots_marker') . ' AS m' .
			' LEFT JOIN ' . $db->qn('#__categories') . ' AS c' .
			' ON m.catid = c.id ' .
			" WHERE $where" .
			" ORDER BY $order";
		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		if (is_array($rows))
		{
			foreach ($rows as $key => $row)
			{
				$urlcat = $row->catid . ':' . JFilterOutput::stringURLSafe($row->section);
				$urlid = $row->id . ':' . JFilterOutput::stringURLSafe($row->title);
				$itemId = HotspotsHelperUtils::getItemid('com_hotspots', 'hotspots', $row->cat_id);
				$rows[$key]->href = JRoute::_("index.php?option=com_hotspots&view=hotspot&catid=" . $urlcat . "&id=" . $urlid . '&Itemid=' . $itemId);
			}
		}

		return $rows;
	}
}
