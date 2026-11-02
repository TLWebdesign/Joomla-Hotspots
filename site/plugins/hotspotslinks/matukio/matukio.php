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

JLoader::register('MatukioHelperRoute', JPATH_ADMINISTRATOR . '/components/com_matukio/helpers/util_route.php');

/**
 * Class PlgHotspotslinksMatukio
 *
 * @since  3.6
 */
class PlgHotspotslinksMatukio extends JPlugin
{
	/**
	 * The constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $params    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $params = array())
	{
		parent::__construct($subject, $params);

		$this->loadLanguage('plg_hotspotslinks_matukio.sys');
	}

	/**
	 * Function to create a proper link to Matukio item
	 *
	 * @param   int  $id         - the id
	 * @param   int  $hotspotId  - the hotspot id
	 *
	 * @return string|The link
	 */
	public function onCreateLink($id, $hotspotId)
	{
		if (JComponentHelper::isEnabled('com_matukio'))
		{
			if ($id)
			{
				try
				{
					$link = JRoute::_(MatukioHelperRoute::getEventIdLink($id), false);
				}
				catch (Exception $e)
				{
					$appl = JFactory::getApplication();
					if($appl->isClient("administrator"))
					{
						$appl->enqueueMessage(
							JText::sprintf(
								'COM_HOTSPOTS_MATUKIO_NO_LINK_TO',
								$id,
								'index.php?option=com_hotspots&task=hotspot.edit&id='.$hotspotId
							),
							'warning'
						);
					}

					$link = '';
				}

				return $link;
			}
		}

		return '';
	}
}
