<?php
/**
 * @package    Ccomment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       10.06.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JLoader::discover('HotspotsHelper', JPATH_BASE . '/components/com_hotspots/helpers/');
/**
 * Class plgHotspotsAup
 *
 * @package  Ccomment
 * @since    5
 */
class PlgHotspotsAup extends JPlugin
{
	/**
	 * The constructor
	 *
	 * @param   object  &$subject  - the subject
	 * @param   array   $params    - the params
	 */
	public function __construct(&$subject, $params)
	{
		$this->loadLanguage('plg_hotspots_aup', JPATH_ADMINISTRATOR);
		parent::__construct($subject, $params);
	}

	/**
	 * Ads points after comment
	 *
	 * @param   string  $context  -  the plugin context
	 * @param   object  $data     -  the comment
	 *
	 * @return  void
	 */
	public function onAfterHotspotSave($context, $data)
	{
		if ($context == 'com_hotspots.hotspot')
		{
			$api_AUP = JPATH_SITE . '/components/com_alphauserpoints/helper.php';

			if (file_exists($api_AUP))
			{
				require_once $api_AUP;
				$data->hotspots_id = $data->id;
				$note = JText::sprintf('PLG_HOTSPOTS_AUP_NEW_HOTSPOT',
					HotspotsHelperUtils::createLink($data),
					$data->title
				);

				$aupid = AlphaUserPointsHelper::getAnyUserReferreID(JFactory::getUser()->id);
				AlphaUserPointsHelper::newpoints('plgaup_hotspots_hotspot', $aupid, '', $note);
			}
		}
	}
}
