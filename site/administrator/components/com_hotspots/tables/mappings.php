<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       05.03.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsTableMappings
 *
 * @since  3.5
 */
class HotspotsTableMappings extends JTable
{
	/**
	 * The constructor
	 *
	 * @param   JDatabaseDriver  &$db  - the db object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__hotspots_mappings', array('marker_id', 'foreign_id'), $db);
	}
}
