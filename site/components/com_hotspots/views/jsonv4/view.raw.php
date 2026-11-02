<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       01.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die ('Restricted access');
require_once JPATH_COMPONENT_SITE . '/views/json.php';

/**
 * Class hotspotsViewJson
 *
 * @since  3
 */
class HotspotsViewJsonv4 extends HotspotsJson
{
	/**
	 * The display function
	 *
	 * @param   string  $tpl  - the template
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$model = JModelLegacy::getInstance('Json', 'HotspotsModel');
		$usersModel = JModelLegacy::getInstance('Users', 'HotspotsModel');
		$this->users = array();
		$users = array();
		$ids = array();

		$list = ($model->getHotspots());

		// Prepare the hotspot - create proper links etc...
		if (!isset($list['hotspots']))
		{
			$list['hotspots'] = array();
		}

		foreach ($list['hotspots'] as $key => $hotspot)
		{
			if ($hotspot->created_by)
			{
				$users[] = $hotspot->created_by;
			}

			$ids[] = $hotspot->id;
			$this->catids[] = $hotspot->catid;
		}

		// Get any images
		$galleria = CompojoomGalleria::getData($ids, 'com_hotspots.hotspot', false, 'small', 'medium');

		// Attach any images to the multimedia property of the Hotspot & prepare it for output
		foreach ($list['hotspots'] as $key => $hotspot)
		{
			$hotspot->multimedia = '';

			if (isset($galleria[$hotspot->id]))
			{
				$hotspot->multimedia = $galleria[$hotspot->id];
			}

			$list['hotspots'][$key] = HotspotsHelperUtils::prepareHotspot($hotspot);
		}

		if (count($users))
		{
			$this->users = $usersModel->getUsers($users);
		}

		$this->list = $list;

		parent::display($tpl);
	}
}
