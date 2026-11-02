<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Helper;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Log\Log;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;

\defined('_JEXEC') or die;

/**
 * Location of the visitor through a GeoIP provider (option geoip_provider; only "ipapi" exists), cached per IP.
 */
abstract class LocationHelper
{
    /**
     * @return  object|null  With latitude and longitude.
     */
    public static function getUserLocation(): ?object
    {
        $ip = self::getUserIp();

        if ($ip === '') {
            return null;
        }

        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('output', ['defaultgroup' => 'com_hotspots_geoip', 'caching' => true, 'lifetime' => 1440]);
        $key   = md5($ip);
        $data  = $cache->get($key);

        if ($data === false) {
            $location = self::lookup($ip);
            $data     = $location ? json_encode($location) : 'none';
            $cache->store($data, $key);
        }

        $location = $data === 'none' ? null : json_decode((string) $data);

        return \is_object($location) ? $location : null;
    }

    /**
     * Public IP of the visitor; proxy headers first (Cloudflare, reverse proxies).
     */
    public static function getUserIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'];

        foreach ($headers as $header) {
            foreach (explode(',', (string) ($_SERVER[$header] ?? '')) as $ip) {
                if (self::isPublicIp(trim($ip))) {
                    return trim($ip);
                }
            }
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }

    public static function isPublicIp(string $ip): bool
    {
        return $ip !== '' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private static function lookup(string $ip): ?object
    {
        if (SettingsHelper::get('geoip_provider', '') !== 'ipapi') {
            return null;
        }

        $apiKey = (string) SettingsHelper::get('geoip_provider_api_key', '');

        if ($apiKey === '' || !self::isPublicIp($ip)) {
            return null;
        }

        $url = 'https://api.ipapi.com/' . rawurlencode($ip) . '?' . http_build_query(['access_key' => $apiKey, 'fields' => 'latitude,longitude']);

        try {
            $response = HttpFactory::getHttp()->get($url, [], 3);
        } catch (\Throwable $e) {
            Log::add('Hotspots GeoIP: ' . $e->getMessage(), Log::WARNING, 'com_hotspots');

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = json_decode((string) $response->getBody());

        if (!isset($data->latitude, $data->longitude) || $data->latitude === null) {
            return null;
        }

        return (object) ['latitude' => (float) $data->latitude, 'longitude' => (float) $data->longitude];
    }
}
