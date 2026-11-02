<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       11.03.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JLoader::register('HotspotsHelperSettings', JPATH_ROOT . '/components/com_hotspots/helpers/settings.php');

/**
 * Class HotspotsGeocoder
 *
 * @since  3.0
 */
class HotspotsGeocoder
{
	private $url = 'https://maps.googleapis.com/maps/api/geocode/json?';

	/**
	 * Performs the request
	 *
	 * @param   string  $type  - type of query
	 * @param   string  $data  - the address
	 *
	 * @return mixed
	 */
	public function request($type, $data)
	{
	    $apiKey = HotspotsHelperSettings::get('api_key', '');
		$ch = curl_init($this->url . $type . '=' . urlencode($data) . '&sensor=false&key='.$apiKey);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// TLWebdesign patch 2026-11-02: short timeouts + use the Joomla proxy (server has no direct internet).
		// Without this, CB registration hangs ~30s. Re-apply after a Hotspots update.
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$app = \Joomla\CMS\Factory::getApplication();

		if ($app->get('proxy_enable'))
		{
			curl_setopt($ch, CURLOPT_PROXY, $app->get('proxy_host') . ':' . $app->get('proxy_port'));

			if ($app->get('proxy_user'))
			{
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $app->get('proxy_user') . ':' . $app->get('proxy_pass'));
			}
		}

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	/**
	 * Find out the lat & lng for address
	 *
	 * @param   string  $address  - the address
	 *
	 * @return array|bool|mixed
	 */
	public function geocode($address)
	{
		$response = $this->request('address', $address);
		$json = json_decode($response);

		$status = $json->status;

		if ($status == 'OK')
		{
			$response = array(
				'status' => $status,
				'location' => $json->results[0]->geometry->location,
				'address' => $this->getAddressArray($json->results[0]->address_components)
			);

			return $response;
		}
		elseif ($status == 'ZERO_RESULTS')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		elseif ($status == 'OVER_QUERY_LIMIT')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		elseif ($status == 'REQUEST_DENIED')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		elseif ($status == 'INVALID_REQUEST')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		else
		{
			// To do handle this
		}

		return false;
	}

	/**
	 * Get the address out of lat and lng coordinates
	 *
	 * @param   string  $lat  - latitude
	 * @param   string  $lng  - longitude
	 *
	 * @return array|bool|mixed
	 */
	public function reverseGeocode($lat, $lng)
	{
		$response = $this->request('latlng', $lat . ',' . $lng);
		$json = json_decode($response);
		$status = $json->status;

		if ($status == 'OK')
		{
			$address = array();

			if (isset($json->results[0]->address_components))
			{
				$components = $json->results[0]->address_components;

				$address = $this->getAddressArray($components);
			}

			$response = array(
				'status' => $status,
				'address' => $address
			);

			return $response;
		}
		elseif ($status == 'ZERO_RESULTS')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		elseif ($status == 'OVER_QUERY_LIMIT')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		elseif ($status == 'REQUEST_DENIED')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		elseif ($status == 'INVALID_REQUEST')
		{
			$response = array(
				'status' => $status
			);

			return $response;
		}
		else
		{
			// To do handle this
		}

		return false;
	}

	/**
	 * Converts the components into an address array that we can use with hotspots
	 *
	 * @param   array  $components  - address components
	 *
	 * @return array
	 */
	private function getAddressArray($components)
	{
		$address = array();

		foreach ($components as $component)
		{
			if ($component->types[0] == 'country')
			{
				$address['country'] = $component->long_name;
			}

			if ($component->types[0] == 'street_number')
			{
				$street[] = $component->long_name;
			}

			if ($component->types[0] == 'route')
			{
				$street[] = $component->long_name;
			}

			if ($component->types[0] == 'postal_code')
			{
				$address['plz'] = $component->long_name;
			}

			if ($component->types[0] == 'locality')
			{
				$address['town'] = $component->long_name;
			}
		}

		if (isset($street))
		{
			$address['street'] = implode(' ', $street);
		}

		return $address;
	}
}
