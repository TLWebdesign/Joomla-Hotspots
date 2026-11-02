<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       02.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JLoader::discover('HotspotsHelperLocationProvider', JPATH_SITE . '/components/com_hotspots/helpers/location_providers');

/**
 * Class HotspotsHelperLocation
 *
 * @since  3.5
 */
class HotspotsHelperLocation
{
	/**
	 * Gets the user's location on the base of his IP address.
	 * The location is determined by the free geo IP service and the
	 * requests are cached to save bandwith
	 *
	 * @return mixed
	 */
	public static function getUserLocation()
	{
		$ip = self::getUserIp();

		$cache = JFactory::getCache('com_hotspots_geoip', 'output');
		$cache->setCaching(true);

		$location = $cache->get($ip);

		if (!$location && $location !== 404)
		{
			$location = self::getLocation($ip);

			// If we don't have an object with the location write 404 for this IP
			if (!$location)
			{
				$location = 404;
			}

			$cache->store($location, $ip);
		}

		// We don't have a location, so return false
		if ($location === 404)
		{
			return false;
		}

		return $location;
	}

	/**
	 * Makes a best quess what the user's IP is
	 *
	 * @return mixed
	 */
	private static function getUserIp()
	{
		// Get real visitor IP behind CloudFlare network
		if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"]) && self::validateIP($_SERVER['HTTP_CF_CONNECTING_IP']))
		{
			return $_SERVER["HTTP_CF_CONNECTING_IP"];
		}

		// Get real visitor IP behind NGINX proxy - https://easyengine.io/tutorials/nginx/forwarding-visitors-real-ip/
		if (!empty($_SERVER["HTTP_X_REAL_IP"]) && self::validateIP($_SERVER['HTTP_X_REAL_IP']))
		{
			return $_SERVER["HTTP_X_REAL_IP"];
		}

		// Check for shared Internet/ISP IP
		if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::validateIP($_SERVER['HTTP_CLIENT_IP']))
		{
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		// Check for IP addresses passing through proxies
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{

			// Check if multiple IP addresses exist in var
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false)
			{
				$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($iplist as $ip)
				{
					if (self::validateIP($ip))
						return $ip;
				}
			}
			else
			{
				if (self::validateIP($_SERVER['HTTP_X_FORWARDED_FOR']))
					return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::validateIP($_SERVER['HTTP_X_FORWARDED'])) {
			return $_SERVER['HTTP_X_FORWARDED'];
		}


		if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::validateIP($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
			return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		}

		if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::validateIP($_SERVER['HTTP_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_FORWARDED_FOR'];
		}

		if (!empty($_SERVER['HTTP_FORWARDED']) && self::validateIP($_SERVER['HTTP_FORWARDED'])) {
			return $_SERVER['HTTP_FORWARDED'];
		}

		// Return unreliable IP address since all else failed
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Ensures an IP address is both a valid IP address and does not fall within
	 * a private network range.
	 */
	public static function validateIP($ip)
	{
		if (strtolower($ip) === 'unknown') {
			return false;
		}


		// Generate IPv4 network address
		$ip = ip2long($ip);

		// Do additional filtering on IP
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			return false;
		}

		// If the IP address is set and not equivalent to 255.255.255.255
		if ($ip !== false && $ip !== -1)
		{

			// Make sure to get unsigned long representation of IP address
			// due to discrepancies between 32 and 64 bit OSes and
			// signed numbers (ints default to signed in PHP)
			$ip = sprintf('%u', $ip);

			// Do private network range checking
			if ($ip >= 0 && $ip <= 50331647)
				return false;
			if ($ip >= 167772160 && $ip <= 184549375)
				return false;
			if ($ip >= 2130706432 && $ip <= 2147483647)
				return false;
			if ($ip >= 2851995648 && $ip <= 2852061183)
				return false;
			if ($ip >= 2886729728 && $ip <= 2887778303)
				return false;
			if ($ip >= 3221225984 && $ip <= 3221226239)
				return false;
			if ($ip >= 3232235520 && $ip <= 3232301055)
				return false;
			if ($ip >= 4294967040)
				return false;
		}

		return true;
	}

	/**
	 * Get's the user location by querying the freegeoip service
	 *
	 * @param   string  $ip  - the IP that we need a location object for
	 *
	 *
	 *
	 * @return bool|string
	 */
	private static function getLocation($ip)
	{
		$provider = HotspotsHelperSettings::get('geoip_provider', '');

		if (!$provider)
		{
			throw new Exception('You are trying to center by user\'s position, but haven\'t configured any geoip provider selected in the Hotspots settings.');
		}

		$provider = 'HotspotsHelperLocationProvider' . ucfirst($provider);
		$provider = new $provider;

		return $provider->getLatLng($ip);
	}
}
