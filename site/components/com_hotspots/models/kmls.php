<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       09.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * Class hotspotsModelKmls
 *
 * @since  3.0
 */
class HotspotsModelKmls extends JModelLegacy
{
	private $catid = '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->catid = explode(';', JFactory::getApplication()->input->getString('cat', 1));
	}

	/**
	 * Loads the necessary KML files
	 *
	 * @return mixed
	 */
	public function getKmls()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$where = array();

		if (count($this->catid) && is_numeric($this->catid[0]))
		{
			foreach ($this->catid as $cat)
			{
				$where[] = $cat;
			}
		}
		else
		{
			// There is no category filter set, let's enforce that only published categories are used
			$cats = JCategories::getInstance('Hotspots')->get()->getChildren(true);

			foreach ($cats as $cat)
			{
				$where[] = $cat->id;
			}
		}

		$query->select('*')
			->from('#__hotspots_kmls')
			->where(CompojoomQueryHelper::in('catid', $where, $db))
			->where('state = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
