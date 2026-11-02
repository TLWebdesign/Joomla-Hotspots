<?php
/**
 * @package    CComment
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       26.08.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . '/components/com_community/libraries/core.php';
require_once JPATH_ADMINISTRATOR . '/components/com_hotspots/libraries/geocoder/geocoder.php';

JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers/');
JLoader::discover('HotspotsModel', JPATH_SITE . '/components/com_hotspots/models/');

/**
 * Class PlgCommunityCcomment
 *
 * @since  3.6
 */
class PlgCommunityHotspots extends CApplications
{
	private static $address;
	/**
	 * Override the constructor and load the countries language
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $config = null)
	{
		// Set the params for the current object
		parent::__construct($subject, $config);

		// Load the country language file
		$language = JFactory::getLanguage();
		$language->load('com_community.country', JPATH_SITE, null, true);
	}

	/**
	 * Add/update hotspot when a profile is updated
	 *
	 * @param   object  $arrItems  - user object
	 *
	 * @return void
	 */
	/**
	 * Add/update hotspot when a profile is updated
	 *
	 * @param   string  $id       - the userid
	 * @param   bool    $success  - was the profile update succssfull?
	 *
	 * @return void
	 */
	public function onAfterProfileUpdate($id, $success)
	{
		if (!$success)
		{
			return;
		}

		$cuser = CFactory::getUser($id);

		$addressInfo = $this->getAddressInfo($cuser);

		if ($addressInfo['status'] == 'OK')
		{
			$this->saveHotspot($addressInfo, $cuser, true);
		}
	}

	/**
	 * Get the gocoded result from google
	 *
	 * @param   object  $user  - CFactory user object
	 *
	 * @return array|bool|mixed
	 */
	private function getAddressInfo($user)
	{
		$address = $this->getUserAddress($user);
		$addressInfo = false;

		if ($address)
		{
			$geocoder = new HotspotsGeocoder;

			$addressInfo = $geocoder->geocode(implode(',', $address));
		}

		return $addressInfo;
	}

	/**
	 * Saves the hotspot and mappigns into the db
	 *
	 * @param   array   $addressInfo  - array with address information
	 * @param   object  $user         - the event object
	 * @param   bool    $update       - are we updating an event?
	 * @param   int     $published    - should we publish or unpublish the event
	 *
	 * @return void
	 */
	public function saveHotspot($addressInfo, $user, $update, $published = 1)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables');
		$model = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
		$table = JTable::getInstance('Marker', 'Table');
		$params = $this->params;

		$description = $params->get('description', '');
		$title = $params->get('name', '');
		$location = array();
		$markerId = 0;

		$coordinates = $addressInfo['location'];
		$address = $addressInfo['address'];

		if ($update)
		{
			$markerId = HotspotsHelperMappings::getMarkerId($user->id, 'com_community');

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

		$location['name'] = HotspotsHelperContent::replacePlaceholders($title, $user);
		$location['catid'] = $params->get('catid', 1);
		$location['state'] = $published;
		$location['language'] = '*';

		$location['hotspotText'] = HotspotsHelperContent::replacePlaceholders($description, $user, 'getInfo');
		$location['created_by'] = $user->id;
		$location['params'] = array();

		if ($params->get('link_to', 0))
		{
			$location['params']['link_to_id'] = $user->id;
			$location['params']['link_to'] = 'community';
		}

		$location['customfields'] = HotspotsHelperMappings::getMappings($this->params->get('custom_fields', ''), $user);

		$table->bind($location);

		if ($model->save($location) && !$markerId)
		{
			HotspotsHelperMappings::saveMapping($model->getState('hotspot.id'), $user->id, 'com_community');
		}
	}

	/**
	 * Get the user address out of the user object
	 *
	 * @param   object  $user  - the user object
	 *
	 * @return array
	 */
	private function getUserAddress($user)
	{
		$address = array();
		$fields = $this->getAddressFields();

		foreach ($fields as $key => $field)
		{
			$address[$key] = $user->getInfo($field);
		}

		// Translate the country name
		$address['country'] = JText::_($address['country']);

		return $address;
	}

	/**
	 * Get an array with the address fields
	 *
	 * @return array
	 */
	private function getAddressFields()
	{
		$params = $this->params;
		$fields = array(
			'street' => $params->get('address', 'FIELD_ADDRESS'),
			'city' => $params->get('city', 'FIELD_CITY'),
			'state' => $params->get('state', 'FIELD_STATE'),
			'country' => $params->get('country', 'FIELD_COUNTRY')
		);

		if ($params->get('zipcode', ''))
		{
			$fields['plz'] = $params->get('zipcode', '');
		}

		return $fields;
	}

	/**
	 * Ads a hotspot when the user registers
	 *
	 * @param   object  $user  - the user object
	 *
	 * @return void
	 */
	public function onProfileCreate($user)
	{
		$addressInfo = $this->getAddressInfo($user);

		if (self::$address)
		{
			$geocoder = new HotspotsGeocoder;

			$addressInfo = $geocoder->geocode(implode(',', self::$address));
		}

		if ($addressInfo['status'] == 'OK')
		{
			$this->saveHotspot($addressInfo, $user, false);
		}
	}

	/**
	 * This is a hack!!! In a JomSocial word with correct events we would not have to use
	 * static variables to persist data between functions.
	 *
	 * The onProfileCreate function is called before the custom fields are persisted. Because of
	 * that we can't figure out what the user's address is.
	 *
	 * That is why, we use the onRegisterProfileValidate(note this function should be used
	 * for validation and not for this!) function to read the array with fields
	 * grab the address, store it in a static variable only to use it later on by the onProfileCreate
	 *
	 * @param   array  $params  - the custom fields
	 *
	 * @return void
	 */
	public function onRegisterProfileValidate($params)
	{
		$model = JModelLegacy::getInstance('Profile', 'CommunityModel');
		$fields = $this->getAddressFields();

		foreach ($fields as $key => $value)
		{
			$address[$key] = $params['field' . $model->getFieldId($value)];
		}

		// Translate the country name
		if (isset($address['country']))
		{
			$address['country'] = JText::_($address['country']);
		}

		self::$address = $address;
	}
}
