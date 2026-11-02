<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       13.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controlleradmin');


class HotspotsControllerKmls extends JControllerAdmin
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('unpublish', 'publish');
	}

	public function getModel($name = 'Kml', $prefix = 'HotspotsModel')
	{
		$model = parent::getModel($name, $prefix);

		return $model;
	}

	public function remove()
	{
        $input = JFactory::getApplication()->input;
        $cid = $input->get('cid', array(), 'array');
		$db = & JFactory::getDBO();

		if (count($cid))
		{
			$cids = implode(',', $cid);
			$query = "DELETE FROM #__hotspots_kmls WHERE hotspots_kml_id IN ( $cids )";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo "<script> alert ('" . $db->getErrorMsg() . "');
			window.history.go(-1); </script>\n";
			}
		}

		$this->setRedirect('index.php?option=com_hotspots&view=kmls');
	}
}
