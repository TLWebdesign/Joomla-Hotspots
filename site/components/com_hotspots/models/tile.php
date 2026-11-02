<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       08.08.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/maps/utility.php';

/**
 * Class HotspotsModelTile
 *
 * @since  3.1
 */
class HotspotsModelTile extends JModelLegacy
{
	/**
	 * Gets the items for the tile
	 *
	 * @param   bool  $cat  - do we need the category id?
	 *
	 * @return mixed
	 */
	public function getItems($cat = true)
	{
		$input = JFactory::getApplication()->input;
		$cats = $input->getString('cats', '', 'get');
		$search = $input->getString('search', '', 'get');
		$lang = $input->getString('hs-language');
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$x = $input->getInt('x', 0, 'get');
		$y = $input->getInt('y', 0, 'get');
		$z = $input->getInt('z', 0, 'get');

		$rect = HotspotsMapUtility::getTileRect($x, $y, $z);

		$swlat = $rect->y;
		$swlng = $rect->x;
		$nelat = $swlat + $rect->height;
		$nelng = $swlng + $rect->width;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('m.id'))
			->select($db->quoteName('gmlng', 'lng'))
			->select($db->quoteName('gmlat', 'lat'))
			->select($db->quoteName('m.title'));

		if ($cat)
		{
			$query->select($db->quoteName('catid'));
		}

		$query->from($db->qn('#__hotspots_marker', 'm'));

		$query->where('(' . $db->qn('gmlng') . ' > ' . $db->q($swlng) . ' AND ' . $db->qn('gmlng') . '<' . $db->q($nelng) . ')');
		$query->where('(' . $db->qn('gmlat') . ' <= ' . $db->q($nelat) . ' AND ' . $db->qn('gmlat') . '>=' . $db->q($swlat) . ')');

		if ($cats != '')
		{
			$cats = explode(';', $cats);

			foreach ($cats as $key => $cat)
			{
				$cats[$key] = $db->quote($cat);
			}

			$query->where($db->quoteName('catid') . ' IN (' . implode(',', $cats) . ')');
		}
		else
		{
			// There is no category filter set, let's enforce that only published categories are used
			$cats = JCategories::getInstance('Hotspots')->get()->getChildren(true);
			$secure = array();

			foreach ($cats as $cat)
			{
				$secure[] = $cat->id;
			}

			if (count($secure))
			{
				$query->where(CompojoomQueryHelper::in('catid', $secure, $db));
			}
		}

		if ($search != '')
		{
			$query->leftJoin(
				$db->quoteName('#__categories') . ' AS ' . $db->quoteName('c')
				. 'ON ' . $db->quoteName('m.catid') . ' = ' . $db->qn('c.id')
			);
			$this->buildSearchWhere($search, $query);
		}

		$query->where($db->qn('m.state') . ' = ' . $db->q(1));

		$nowDate = $db->Quote(JFactory::getDate()->toSQL());

		$query->where('(' . $db->qn('m.publish_up') . ' IS NULL  OR ' . $db->qn('m.publish_up') . ' <= ' . $nowDate . ')');
		$query->where('(' . $db->qn('m.publish_down') . ' IS NULL OR ' . $db->qn('m.publish_down') . ' >= ' . $nowDate . ')');
		$query->where('m.access IN (' . $groups . ')');

		if ($lang)
		{
			$query->where('m.language in (' . $query->quote($lang) . ',' . $query->quote('*') . ')');
		}

		$query->order('RAND(1)');

		// At high zoom levels when we have a lot of locations the map looks cluttered. There is no point
		// to load that many locations there
		if ($z < 6)
		{
			$limit = 100;
		}
		else
		{
			$limit = 500;
		}

		$db->setQuery($query, 0, $limit);

		return $db->loadAssocList();
	}

	/**
	 * The where part of the query when we search
	 *
	 * @param   string          $sentence  - the string that we search for
	 * @param   JDatabaseQuery  &$query    - the query object
	 *
	 * @return void
	 */
	protected function buildSearchWhere($sentence, &$query)
	{
		$db = JFactory::getDbo();

		$name = $db->qn('m.title');
		$description = $db->qn('m.description');
		$descriptionSmall = $db->qn('m.description_small');
		$plz = $db->qn('m.plz');
		$catName = $db->qn('c.title');
		$street = $db->qn('m.street');
		$country = $db->qn('m.country');
		$town = $db->qn('m.town');
		$and = array();

		if (preg_match('/"([^"]+)"/', $sentence, $m))
		{
			/*
			 * example:
			 * 1. "test something" else
			 * will match -> "something else" AND else
			 * 2. test something else
			 * will match -> test OR something OR else
			 */
			$searchWord = $db->Quote('%' . $db->escape(trim($m[1]), true) . '%', false);

			$search[] = $name . ' LIKE ' . $searchWord;
			$search[] = $description . ' LIKE ' . $searchWord;
			$search[] = $descriptionSmall . ' LIKE ' . $searchWord;
			$search[] = $plz . ' LIKE ' . $searchWord;
			$search[] = $catName . ' LIKE ' . $searchWord;
			$search[] = $street . ' LIKE ' . $searchWord;
			$search[] = $country . ' LIKE ' . $searchWord;
			$search[] = $town . ' LIKE ' . $searchWord;

			$word = trim(str_replace('"' . $m[1] . '"', '', $sentence));

			if ($word)
			{
				$searchWord = $db->Quote('%' . $db->escape(trim($word), true) . '%', false);
				$and[] = $name . ' LIKE ' . $searchWord;
				$and[] = $description . ' LIKE ' . $searchWord;
				$and[] = $descriptionSmall . ' LIKE ' . $searchWord;
				$and[] = $plz . ' LIKE ' . $searchWord;
				$and[] = $catName . ' LIKE ' . $searchWord;
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
				$searchWord = $db->Quote('%' . $db->escape($word, true) . '%', false);
				$search[] = $name . ' LIKE ' . $searchWord;
				$search[] = $description . ' LIKE ' . $searchWord;
				$search[] = $descriptionSmall . ' LIKE ' . $searchWord;
				$search[] = $plz . ' LIKE ' . $searchWord;
				$search[] = $catName . ' LIKE ' . $searchWord;
				$search[] = $street . ' LIKE ' . $searchWord;
				$search[] = $country . ' LIKE ' . $searchWord;
				$search[] = $town . ' LIKE ' . $searchWord;
			}
		}

		$query->where('(' . implode(' OR ', $search) . ')');

		if (count($and))
		{
			$query->where('(' . implode(' OR ', $and) . ')');
		}
	}
}
