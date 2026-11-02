<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       02.12.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * Class HotspotsModelJson
 *
 * @todo   Merge this class with the HotspotsModelHotspots class
 *
 * @since  3.0
 */
class HotspotsModelJson extends JModelLegacy
{
	private $hotspots = null;

	private $catid = null;

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->catid = JFactory::getApplication()->input->getString('cat', 1);
	}

	/**
	 * Populate state
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$this->setState('filter.language', $app->getLanguageFilter());
	}

	/**
	 * this function is nearly the same as the one in the Hotspots model
	 * TODO: think of a way to combine the functions and get rid of that model
	 *
	 * @return array with objects
	 */
	public function getHotspots()
	{
		if (!$this->hotspots)
		{
			$input = JFactory::getApplication()->input;
			$appl = JFactory::getApplication();

			JPluginHelper::importPlugin('hotspots');

			$db = JFactory::getDBO();

			$offset = JFactory::getApplication()->input->getInt('offset');

			$searchWord = JFactory::getApplication()->input->getString('search', null);

			$query = $db->getQuery(true);

			$appl->triggerEvent('onBeforeBuildQuery', array(&$this, &$query));

			$boundariesQuery = $this->buildWhereBoundariesQuery();
			$query->select('SQL_CALC_FOUND_ROWS m.id as hotspots_id, m.*')
				->from('#__hotspots_marker as m')
				->order('m.' . HotspotsHelperSettings::get('hotspots_order', 'title ASC'));

			// If we just set an empty where, then the next time we try to use q->where joomla will automatically
			// add an AND
			if(count($boundariesQuery)) {
				$query->where(implode(' AND ', $this->buildWhereBoundariesQuery()));
			}

			$this->buildWhereGeneralQuery($query);

			if ($searchWord)
			{
				// Restrict by search term
				$this->buildWhereSearchQuery($searchWord, $query);
			}

			$appl->triggerEvent('onAfterBuildQuery', array(&$this, &$query));

			$db->setQuery($query, $offset, HotspotsHelperSettings::get('marker_list_length', 20));
			$rows = $db->loadObjectList();

			$this->hotspots['hotspots'] = $rows;

			$oldQuery = clone $query;

			$this->hotspots['viewCount'] = $this->selectFoundRows();

			// If we are searching for a word and we don't have anything in the current view
			if ($searchWord && $this->hotspots['viewCount'] == 0 && $input->getBool('fs'))
			{
				// Do we have entries around the globe?
				$worldCount = $this->countTheWorld($searchWord, $query);

				// Let's see if those entries are nearBy
				if ($worldCount > 0)
				{
					$this->hotspots['newboundaries'] = true;

					// Search within 50km radius
					$this->hotspots['hotspots'] = $this->searchInRadius($searchWord, $oldQuery);
					$this->hotspots['viewCount'] = $this->selectFoundRows();

					// Okay, so we really don't have any results in the given boundaries. Let us just search all over the world
					if (!$this->hotspots['viewCount'])
					{
						$this->hotspots['hotspots'] = $this->searchTheWorld($searchWord, $oldQuery);
						$this->hotspots['viewCount'] = $this->selectFoundRows();
					}
				}
			}
		}

		return $this->hotspots;
	}

	/**
	 * Count how many hotspots we have around the globe
	 *
	 * @param   string  $searchWord  - the search word
	 * @param   object  $query       - the query object
	 *
	 * @return mixed
	 */
	private function countTheWorld($searchWord, $query)
	{
		$db = $this->getDbo();

		// Now count how many we have in the whole world
		$query->clear('select');
		$query->clear('join');
		$query->select('COUNT(*) AS count');
		$query->clear('where');

		$this->buildWhereGeneralQuery($query);
		$this->buildWhereSearchQuery($searchWord, $query);

		$db->setQuery($query);

		return  $db->loadObject()->count;
	}

	/**
	 * Search for a hotspot that meats the search criteria around the globe
	 *
	 * @param   string  $searchWord  - the search word
	 * @param   object  $query       - the query object
	 *
	 * @return mixed
	 */
	private function searchTheWorld($searchWord, $query)
	{
		$db = $this->getDbo();

		$query->clear('select');
		$query->clear('join');
		$query->clear('where');
		$query->clear('order');

		$query->select('SQL_CALC_FOUND_ROWS m.id AS hotspots_id, m.*, m.params as params, c.params as cat_params');

		// Restrict by search term
		$this->buildWhereSearchQuery($searchWord, $query);

		// Restrict by general stuff such as publish, publish_up...
		$this->buildWhereGeneralQuery($query);

		$query->order('m.' . HotspotsHelperSettings::get('hotspots_order', 'title ASC'));

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Find out how many rows the previous query returned
	 *
	 * @return mixed
	 */
	private function selectFoundRows()
	{
		$this->getDbo()->setQuery('SELECT FOUND_ROWS()');

		return $this->getDbo()->loadResult();
	}

	/**
	 * Search for hotspots in a specific radius around the center point
	 *
	 * @param   string  $searchWord  - the search word
	 * @param   object  $query       - the query object
	 *
	 * @return mixed
	 */
	private function searchInRadius($searchWord, $query)
	{
		$input = JFactory::getApplication()->input;
		$db = $this->getDbo();
		/*
		 * TLWebdesign patch 2026-11-02: "c" comes from the request and is put into the SQL below as is.
		 * Cast both parts to float, otherwise any visitor can inject SQL.
		 */
		$center = explode(',', $input->getString('c', '')) + array(0, 0);
		$center = array((float) $center[0], (float) $center[1]);
		$query->clear('where');
		$query->clear('join');
		$query->clear('order');

		/**
		 * http://www.scribd.com/doc/2569355/Geo-Distance-Search-with-MySQL
		 * We do a new search within 100 miles
		 */
		$query->select(
			'3956 * 2 * ASIN(SQRT( POWER(SIN((' . $center[0]
			. '-	abs(m.gmlat)) * pi()/180 / 2),2) + COS(' . $center[0] . ' * pi()/180 ) * COS(
						abs
						(m.gmlat) *  pi()/180) * POWER(SIN((' . $center[1] . '- m.gmlng) *  pi()/180 / 2), 2) ))
				as distance'
		);

		$lon1 = $center[1] . ' -100/abs(cos(radians(' . $center[0] . '))*69)';
		$lon2 = $center[1] . ' +100/abs(cos(radians(' . $center[0] . '))*69)';
		$lat1 = $center[0] . ' -(100/69)';
		$lat2 = $center[0] . ' +(100/69)';

		// Restrict by search term
		$this->buildWhereSearchQuery($searchWord, $query);

		// Restrict by general stuff such as publish, publish_up...
		$this->buildWhereGeneralQuery($query);

		$query->where('(m.gmlng between ' . $lon1 . ' and ' . $lon2 . ' and m.gmlat between ' . $lat1 . ' and ' . $lat2 . ')');
		$query->order('distance');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Gets the category ids
	 *
	 * @return array
	 */
	private function getCats()
	{
		$secure = array();

		if ($this->catid)
		{
			$cats = explode(';', $this->catid);

			if (is_array($cats))
			{
				foreach ($cats as $cat)
				{
					if (is_numeric($cat) && $cat > 0)
					{
						$secure[] = $cat;
					}
				}
			}
		}
		else
		{
			// There is no category filter set, let's enforce that only published categories are used
			$catObject = JCategories::getInstance('Hotspots')->get();
			$cats = $catObject->getChildren(true);

			foreach ($cats as $cat)
			{
				$secure[] = $cat->id;
			}
		}

		return $secure;
	}

	public function buildWhereCatsQuery() {
		$db = JFactory::getDbo();
		$cats = $this->getCats();

		$secure = array();

		foreach ($cats as $cat)
		{
			$secure[] = $db->quote($cat);
		}

		if (count($secure))
		{
			$where[] = ' m.catid IN (' . implode(',', $secure) . ')';
		}

		return $where;

	}

	/**
	 * the Where part of the query
	 *
	 * @param   null  $cats  - the category
	 *
	 * @return array
	 */
	public function buildWhereBoundariesQuery()
	{
		$db = JFactory::getDBO();
		$input = JFactory::getApplication()->input;
		$where = array();

		$level = $input->getInt('level');

		$levels = array(0, 1);

		/**
		 * at small zoom levels we can end up having 2 datelines
		 * because of this our queries will fail. To go around that
		 * problem we will get all hotspots in the categories
		 * a nasty trick, but it should work in most situations...
		 */
		if (!in_array($level, $levels))
		{
			$ne = $input->getString('ne');
			$sw = $input->getString('sw');
			// TLWebdesign patch 2026-11-02: no warnings when ne/sw are missing or incomplete.
			list($nelat, $nelng) = explode(',', (string) $ne) + array('', '');
			list($swlat, $swlng) = explode(',', (string) $sw) + array('', '');

			/**
			 * We need to take in account the meridian in the Mercator
			 * projection of the map. In the Mercator projection the meridian of the earth
			 * is at the left and right edges. When you slide to the left the
			 * or right, the map will wrap as you move past the meridian
			 * at +/- 180 degrees. In that case, the bounds are partially split
			 * across the left and right edges of the map and the northeast
			 * corner is actually positioned at a poin that is greater than 180 degree.
			 * The gmaps API automatically adjusts the longitude values to fit
			 * between -180 and +180 degrees so we ned to request 2 portions of the map
			 * from our database covering the left and right sides.
			 */
			if ($nelng > $swlng)
			{
				$where[] = ' (m.gmlng > ' . $db->quote($swlng) . ' AND m.gmlng < ' . $db->quote($nelng) . ')';
				$where[] = ' (m.gmlat <= ' . $db->quote($nelat) . ' AND m.gmlat >= ' . $db->quote($swlat) . ')';
			}
			else
			{
				$where[] = ' (m.gmlng >= ' . $db->quote($swlng) . ' OR m.gmlng <= ' . $db->quote($nelng) . ')';
				$where[] = ' (m.gmlat <= ' . $db->quote($nelat) . ' AND m.gmlat >= ' . $db->quote($swlat) . ')';
			}
		}

		return $where;
	}

	/**
	 * The where part of the query for a search
	 *
	 * @param   string          $sentence  - the word we search for
	 * @param   JDatabaseQuery  &$q        - the query
	 *
	 * @return void
	 */
	public function buildWhereSearchQuery($sentence, &$q)
	{
		$name = $q->qn('m.title');
		$description = $q->qn('m.description');
		$descriptionSmall = $q->qn('m.description_small');
		$plz = $q->qn('m.plz');
		$catName = $q->qn('c.title');
		$street = $q->qn('m.street');
		$state = $q->qn('m.administrative_area_level_1');
		$country = $q->qn('m.country');
		$town = $q->qn('m.town');
		$and = array();
		$cats = $this->getCats();

		if (count($cats))
		{
			$q->select($catName . ' AS cat_title');
			$q->leftJoin('#__categories AS c ON c.id = m.catid');
		}

		if (preg_match('/"([^"]+)"/', $sentence, $m))
		{
			/*
			 * example:
			 * 1. "test something" else
			 * will match -> "something else" AND else
			 * 2. test something else
			 * will match -> test OR something OR else
			 */
			$searchWord = $q->Quote('%' . $q->escape(trim($m[1]), true) . '%', false);

			$search[] = $name . ' LIKE ' . $searchWord;
			$search[] = $description . ' LIKE ' . $searchWord;
			$search[] = $descriptionSmall . ' LIKE ' . $searchWord;
			$search[] = $plz . ' LIKE ' . $searchWord;
			$search[] = $state . ' LIKE ' . $searchWord;

			if (count($cats))
			{
				$search[] = $catName . ' LIKE ' . $searchWord;
			}

			$search[] = $street . ' LIKE ' . $searchWord;
			$search[] = $country . ' LIKE ' . $searchWord;
			$search[] = $town . ' LIKE ' . $searchWord;

			$word = trim(str_replace('"' . $m[1] . '"', '', $sentence));

			if ($word)
			{
				$searchWord = $q->Quote('%' . $q->escape(trim($word), true) . '%', false);
				$and[] = $name . ' LIKE ' . $searchWord;
				$and[] = $description . ' LIKE ' . $searchWord;
				$and[] = $descriptionSmall . ' LIKE ' . $searchWord;
				$and[] = $plz . ' LIKE ' . $searchWord;
				$and[] = $state . ' LIKE ' . $searchWord;

				if (count($cats))
				{
					$and[] = $catName . ' LIKE ' . $searchWord;
				}

				$and[] = $street . ' LIKE ' . $searchWord;
				$and[] = $country . ' LIKE ' . $searchWord;
				$and[] = $town . ' LIKE ' . $searchWord;
			}
		}
		else
		{
			$words = explode(' ', $sentence);

			foreach ($words as $word)
			{
				$searchWord = $q->Quote('%' . $q->escape($word, true) . '%', false);
				$search[] = $name . ' LIKE ' . $searchWord;
				$search[] = $description . ' LIKE ' . $searchWord;
				$search[] = $descriptionSmall . ' LIKE ' . $searchWord;
				$search[] = $plz . ' LIKE ' . $searchWord;
				$search[] = $state . ' LIKE ' . $searchWord;

				if (count($cats))
				{
					$search[] = $catName . ' LIKE ' . $searchWord;
				}

				$search[] = $street . ' LIKE ' . $searchWord;
				$search[] = $country . ' LIKE ' . $searchWord;
				$search[] = $town . ' LIKE ' . $searchWord;
			}
		}

		$q->where('(' . implode(' OR ', $search) . ')');

		if (count($and))
		{
			$q->where('(' . implode(' OR ', $and) . ')');
		}
	}

	/**
	 * Where part of the query
	 *
	 * @param   object  &$query  - the query object
	 *
	 * @return void
	 */
	public function buildWhereGeneralQuery(&$query)
	{
		$input = JFactory::getApplication()->input;

		$query->where($this->buildWhereCatsQuery());
		$query->where(' m.state = 1');

		$nowDate = $query->Quote(JFactory::getDate()->toSQL());

		$query->where('(m.publish_up IS NULL  OR m.publish_up <= ' . $nowDate . ')');
		$query->where('(m.publish_down IS NULL  OR m.publish_down >= ' . $nowDate . ')');

		if ($this->getState('filter.language'))
		{
			$query->where('m.language in (' . $query->quote($input->getString('hs-language')) . ',' . $query->quote('*') . ')');
		}

		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('m.access IN (' . $groups . ')');
	}
}
