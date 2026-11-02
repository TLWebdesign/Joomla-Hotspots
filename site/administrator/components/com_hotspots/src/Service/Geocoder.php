<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Service;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Log\Log;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;

\defined('_JEXEC') or die;

/**
 * Google Geocoding API. The Joomla HTTP client applies the proxy of the global configuration.
 *
 * Results (same shapes as the old HotspotsGeocoder):
 * - geocode():        ['status' => 'OK', 'location' => {lat, lng}, 'address' => [...]] or ['status' => '<Google status>']
 * - reverseGeocode(): ['status' => 'OK', 'address' => [...]] or ['status' => '<Google status>']
 * - false when the request failed or the answer could not be read.
 * The address array can have street, plz, town and country.
 */
class Geocoder
{
    private const URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * Short timeouts: geocoding runs during CB registration and must not hang the request.
     */
    private const CONNECT_TIMEOUT = 3;

    private const TIMEOUT = 5;

    private const ERROR_STATUSES = ['ZERO_RESULTS', 'OVER_QUERY_LIMIT', 'OVER_DAILY_LIMIT', 'REQUEST_DENIED', 'INVALID_REQUEST', 'UNKNOWN_ERROR'];

    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (string) SettingsHelper::get('api_key', '');
    }

    public function geocode(string $address): array|false
    {
        $json = $this->request(['address' => $address]);

        if ($json === null) {
            return false;
        }

        if ($json->status === 'OK' && isset($json->results[0]->geometry->location)) {
            return [
                'status'   => 'OK',
                'location' => $json->results[0]->geometry->location,
                'address'  => $this->addressFromComponents($json->results[0]->address_components ?? []),
            ];
        }

        return \in_array($json->status, self::ERROR_STATUSES, true) ? ['status' => $json->status] : false;
    }

    public function reverseGeocode(float $lat, float $lng): array|false
    {
        $json = $this->request(['latlng' => $lat . ',' . $lng]);

        if ($json === null) {
            return false;
        }

        if ($json->status === 'OK') {
            return [
                'status'  => 'OK',
                'address' => $this->addressFromComponents($json->results[0]->address_components ?? []),
            ];
        }

        return \in_array($json->status, self::ERROR_STATUSES, true) ? ['status' => $json->status] : false;
    }

    /**
     * The decoded answer, or null when the request failed.
     */
    private function request(array $query): ?object
    {
        $query['key'] = $this->apiKey;

        try {
            $http     = HttpFactory::getHttp(['transport.curl' => [CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT]]);
            $response = $http->get(self::URL . '?' . http_build_query($query), [], self::TIMEOUT);
        } catch (\Throwable $e) {
            Log::add('Hotspots geocoder: ' . $e->getMessage(), Log::WARNING, 'com_hotspots');

            return null;
        }

        $json = json_decode((string) $response->getBody());

        return \is_object($json) && isset($json->status) ? $json : null;
    }

    private function addressFromComponents(array $components): array
    {
        $address = [];
        $street  = [];

        foreach ($components as $component) {
            $type = $component->types[0] ?? '';

            match ($type) {
                'country'       => $address['country'] = $component->long_name,
                'postal_code'   => $address['plz'] = $component->long_name,
                'locality'      => $address['town'] = $component->long_name,
                'street_number', 'route' => $street[] = $component->long_name,
                default         => null,
            };
        }

        if ($street) {
            $address['street'] = implode(' ', $street);
        }

        return $address;
    }
}
