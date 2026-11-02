<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       19.03.2023
 *
 * @copyright  Copyright (C) 2008 - 2023 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

class HotspotsHelperLocationProviderIpapi {

	private $url = 'http://api.ipapi.com/';

	public function getLatLng($ip) {
		$apiKey = HotspotsHelperSettings::get('geoip_provider_api_key', '');

		if(!$apiKey) {
			throw new Exception('No API key provided for Geo IP Provider. Check your hotspots settings.');
		}


		$url = $this->url . $ip . '?access_key=' . $apiKey . '&fields=latitude,longitude';

		$http = new JHttp();

		try
		{
			$get = $http->get($url, array(), 3);
		}
		catch (Exception $e)
		{
			return false;
		}


		if ($get->code === 200)
		{
			$response = json_decode($get->body);

			$location = new stdClass;
			$location->latitude = $response->latitude;
			$location->longitude = $response->longitude;

			return $location;
		}


		return false;
	}
}
