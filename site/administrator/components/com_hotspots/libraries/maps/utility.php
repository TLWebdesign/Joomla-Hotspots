<?php
/**
 * @author Daniel Dimitrov - compojoom.com
 * @date: 07.08.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/maps/boundary.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/maps/point.php';
/**
 * Static utility class for map manipulation
 *
 */
class HotspotsMapUtility {
    const TILE_SIZE = 256;

    /**
     * @static
     * @param $point
     * @param $zoom
     * @return HotspotsMapPoint
     */
    public static function fromXYToLatLng($point,$zoom) {
        $scale = (1 << ($zoom)) * self::TILE_SIZE;

        return new HotspotsMapPoint(
            (int) ($point->x * $scale),
            (int)($point->y * $scale)
        );
    }

    /**
     * @static
     * @param $point
     * @return mixed
     */
    public static function fromMercatorCoords($point) {
        $point->x *= 360;
        $point->y = rad2deg(atan(sinh($point->y))*M_PI);
        return $point;
    }

    /**
     * @static
     * @param $lat
     * @param $lng
     * @param $zoom
     * @return HotspotsMapPoint
     */
    public static function getPixelOffsetInTile($lat,$lng,$zoom) {
        $pixelCoords = self::toZoomedPixelCoords($lat, $lng, $zoom);
        return new HotspotsMapPoint(
            $pixelCoords->x % self::TILE_SIZE,
            $pixelCoords->y % self::TILE_SIZE
        );
    }

    /**
     * Calculates the boundaries for the given tile
     * @static
     * @param $x
     * @param $y
     * @param $zoom
     * @return HotspotsMapBoundary
     */
    public static function getTileRect($x,$y,$zoom) {
        $tilesAtThisZoom = 1 << $zoom;
        $lngWidth = 360.0 / $tilesAtThisZoom;
        $lng = -180 + ($x * $lngWidth);

        $latHeightMerc = 1.0 / $tilesAtThisZoom;
        $topLatMerc = $y * $latHeightMerc;
        $bottomLatMerc = $topLatMerc + $latHeightMerc;

        $bottomLat = (180 / M_PI) * ((2 * atan(exp(M_PI *
            (1 - (2 * $bottomLatMerc))))) - (M_PI / 2));
        $topLat = (180 / M_PI) * ((2 * atan(exp(M_PI *
            (1 - (2 * $topLatMerc))))) - (M_PI / 2));

        $latHeight = $topLat - $bottomLat;

        return new HotspotsMapBoundary($lng, $bottomLat, $lngWidth, $latHeight);
    }

    /**
     * @static
     * @param $lat
     * @param $lng
     * @return HotspotsMapPoint
     */
    public static function toMercatorCoords($lat, $lng) {
        if ($lng > 180) {
            $lng -= 360;
        }

        $lng /= 360;
        $lat = asinh(tan(deg2rad($lat)))/M_PI/2;
        return new HotspotsMapPoint($lng, $lat);
    }

    /**
     * @static
     * @param $point
     * @return mixed
     */
    public static function toNormalisedMercatorCoords($point) {
        $point->x += 0.5;
        $point->y = abs($point->y-0.5);
        return $point;
    }

    /**
     * @static
     * @param $lat
     * @param $lng
     * @param $zoom
     * @return Point
     */
    public static function toTileXY($lat, $lng, $zoom) {
        $normalised = self::toNormalisedMercatorCoords(
            self::toMercatorCoords($lat, $lng)
        );
        $scale = 1 << ($zoom);
        return new Point((int)($normalised->x * $scale), (int)($normalised->y * $scale));
    }

    /**
     * @static
     * @param $lat
     * @param $lng
     * @param $zoom
     * @return HotspotsMapPoint
     */
    public static function toZoomedPixelCoords($lat, $lng, $zoom) {
        $normalised = self::toNormalisedMercatorCoords(
            self::toMercatorCoords($lat, $lng)
        );
        $scale = (1 << ($zoom)) * self::TILE_SIZE;
        return new HotspotsMapPoint(
            (int) ($normalised->x * $scale),
            (int)($normalised->y * $scale)
        );
    }
}



