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

require_once JPATH_ADMINISTRATOR . '/components/com_hotspots/libraries/geocoder/geocoder.php';

JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers/');
JLoader::discover('HotspotsModel', JPATH_SITE . '/components/com_hotspots/models/');

/**
 * Class PlgContentHotspotsmatukio
 *
 * @since  3.0
 */
class PlgContentHotspotsmatukio extends JPlugin
{
	/**
	 * Gets the publish down date for an event
	 *
	 * @param   object  $event  - the event object
	 *
	 * @return string - either null date or the date the event should end
	 */
	private function getPublishDownDate($event)
	{
		$showend = MatukioHelperSettings::getSettings('event_stopshowing', 2);
		$ordering = array(
			'begin',
			'end',
			'booked',
			''
		);

		if ($ordering[$showend])
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__matukio_recurring')->where('event_id = ' . $query->q($event->id))
				->order($ordering[$showend] . ' DESC ');

			$db->setQuery($query, 0, 1);

			$result = $db->loadObject();

			if ($result)
			{
				return JFactory::getDate($result->end)->toSql();
			}
		}

		return null;
	}

	/**
	 * Once the event is saved we need to map it with hotspots
	 *
	 * @param   string  $context   - the context
	 * @param   object  $event     - the matukio object
	 * @param   bool    $newEvent  - is it a new event or an updated event
	 *
	 * @return bool
	 */
	public function onAfterSaveRecurring($context, $event, $newEvent)
	{
		if ($context == 'com_matukio.event')
		{
			$address = $event->gmaploc;

			if ($event->place_id > 0)
			{
				$locobj = MatukioHelperEvents::getLocation($event->place_id);

				if ($locobj)
				{
					$address = $locobj->gmaploc;

					// Geocode only because we need the street, town etc
					if ($locobj->lat && $locobj->lng)
					{
						$geocoder = new HotspotsGeocoder;
						$addressInfo = $geocoder->geocode($address);

						// If the Geocoder finds something, use it but make sure that we use the coordiantes from Matukio
						if ($addressInfo['status'] == 'OK')
						{
							$addressInfo['location']->lat = $locobj->lat;
							$addressInfo['location']->lng = $locobj->lng;

							$this->saveHotspot($addressInfo, $event, !$newEvent);

							return true;
						}
						else
						{
							// If the Geocoder doesn't find anything. Then it is still fine
							// We can still use the entry as we have lat & lng.
							$addressInfo['location'] = new stdClass;
							$addressInfo['location']->lat = $locobj->lat;
							$addressInfo['location']->lng = $locobj->lng;

							$addressInfo['address'] = array();

							$this->saveHotspot($addressInfo, $event, !$newEvent);

							return true;
						}
					}
				}
			}

			// If we end up here, then our location doesn't have lat & lng coordiantes, we'll try to geocode
			if ($address)
			{
				$geocoder = new HotspotsGeocoder;
				$addressInfo = $geocoder->geocode($address);

				// If location was found, save the hotspot
				if ($addressInfo['status'] == 'OK')
				{
					$this->saveHotspot($addressInfo, $event, !$newEvent);
				}
			}
		}

		return true;
	}

	/**
	 * Saves the hotspot and mappigns into the db
	 *
	 * @param   array   $addressInfo  - array with address information
	 * @param   object  $event        - the event object
	 * @param   bool    $update       - are we updating an event?
	 * @param   int     $published    - should we publish or unpublish the event
	 *
	 * @return void
	 */
	public function saveHotspot($addressInfo, $event, $update, $published = 1)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables');
		$model = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
		$params = $this->params;

		$description = $params->get('description', '');
		$title = $params->get('name', '');
		$location = array();
		$markerId = 0;

		$coordinates = $addressInfo['location'];
		$address = $addressInfo['address'];

		if ($update)
		{
			$markerId = HotspotsHelperMappings::getMarkerId($event->id, 'com_matukio');

			if ($markerId)
			{
				$location['id'] = $markerId;
			}
		}

		$location = array_merge($location, $address);

		if ($coordinates)
		{
			$location['gmlat'] = $coordinates->lat;
			$location['gmlng'] = $coordinates->lng;
		}

		$categoryMappings = $this->getCategoryMappings();

		$location['title'] = HotspotsHelperContent::replacePlaceholders($title, $event);
		$location['catid'] = isset($categoryMappings[$event->catid]) ? $categoryMappings[$event->catid] : $params->get('catid', 1);
		$location['state'] = $published;
		$location['language'] = '*';
		$location['publish_down'] = $this->getPublishDownDate($event);

		$location['hotspotText'] = HotspotsHelperContent::replacePlaceholders($description, $event);
		$location['created_by'] = $event->created_by ? $event->created_by : JFactory::getUser()->id;
		$location['params'] = array();

		if ($params->get('link_to', 0))
		{
			$location['params']['link_to_id'] = $event->id;
			$location['params']['link_to'] = 'matukio';
		}

		$location['customfields'] = HotspotsHelperMappings::getMappings($this->params->get('custom_fields', ''), $event);

		if ($model->save($location) && !$markerId)
		{
			HotspotsHelperMappings::saveMapping($model->getState('hotspot.id'), $event->id, 'com_matukio');
		}
	}

	/**
	 * When a state changes in Matukio we need to update the Hotspot as well
	 *
	 * @param   string  $context  - the context
	 * @param   array   $ids      - array with changed ids
	 * @param   int     $state    - changed value
	 *
	 * @return void
	 */
	public function onEventStateChange($context, $ids, $state)
	{
		if ($context == 'com_matukio.event')
		{
			$markerIds = HotspotsHelperMappings::getMarkerIds($ids, 'com_matukio');

			// Execute the query only if we have markerIds
			if ($markerIds)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update('#__hotspots_marker')
					->set('state=' . $db->q($state))->where('id IN (' . implode(',', $markerIds) . ')');

				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * When an event is deleted in Matukio we need to delete the hotspot as well
	 *
	 * @param   string  $context  - the context
	 * @param   array   $ids      - array with changed ids
	 *
	 * @return void
	 */
	public function onEventAfterDelete($context, $ids)
	{
		if ($context == 'com_matukio.event')
		{
			$markerIds = HotspotsHelperMappings::getMarkerIds($ids, 'com_matukio');

			// Execute the query only if we have markerIds
			if ($markerIds)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->delete('#__hotspots_marker')->where('id IN (' . implode(',', $markerIds) . ')');

				$db->setQuery($query);
				$db->execute();

				// Delete the mappings as well
				HotspotsHelperMappings::deleteMappings($ids, 'com_matukio');
			}
		}
	}


	/**
	 * Get the category mappings specified in the plugin settings
	 *
	 * @return array
	 */
	private function getCategoryMappings()
	{
		$raw = $this->params->get('catmappings');
		$return = array();
		$lines = explode("\n", $raw);

		foreach ($lines as $line)
		{
			if ($line)
			{
				$cats = explode('=', $line);
				$return[(int) $cats[0]] = (int) $cats[1];
			}
		}

		return $return;
	}
}
