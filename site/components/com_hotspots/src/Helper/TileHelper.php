<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Google Maps tile maths (Mercator) for the marker tiles, and where the tiles are cached.
 */
abstract class TileHelper
{
    public const TILE_SIZE = 256;

    /**
     * Cache folder of the generated tiles. Joomla's "Clear cache" (group com_hotspots.tiles) empties it.
     */
    public static function cacheFolder(): string
    {
        // The site cache folder, also from the administrator (where JPATH_CACHE is administrator/cache).
        $cache = (string) \Joomla\CMS\Factory::getApplication()->get('cache_path', '') ?: JPATH_SITE . '/cache';

        return rtrim($cache, '/\\') . '/com_hotspots.tiles';
    }

    /**
     * Area of a tile: ['lng' => west, 'lat' => south, 'width' => degrees, 'height' => degrees].
     */
    public static function getTileRect(int $x, int $y, int $zoom): array
    {
        $tiles    = 1 << $zoom;
        $lngWidth = 360.0 / $tiles;
        $lng      = -180 + ($x * $lngWidth);
        $height   = 1.0 / $tiles;
        $top      = $y * $height;
        $bottom   = $top + $height;
        $toLat    = static fn (float $merc): float => (180 / M_PI) * ((2 * atan(exp(M_PI * (1 - (2 * $merc))))) - (M_PI / 2));

        $bottomLat = $toLat($bottom);

        return ['lng' => $lng, 'lat' => $bottomLat, 'width' => $lngWidth, 'height' => $toLat($top) - $bottomLat];
    }

    /**
     * Pixel position of a point inside its tile: [x, y].
     */
    public static function getPixelOffsetInTile(float $lat, float $lng, int $zoom): array
    {
        [$x, $y] = self::toZoomedPixelCoords($lat, $lng, $zoom);

        return [$x % self::TILE_SIZE, $y % self::TILE_SIZE];
    }

    /**
     * @return  int[]  [x, y] in pixels of the whole world at this zoom level.
     */
    public static function toZoomedPixelCoords(float $lat, float $lng, int $zoom): array
    {
        if ($lng > 180) {
            $lng -= 360;
        }

        $x     = $lng / 360 + 0.5;
        $y     = abs(asinh(tan(deg2rad($lat))) / M_PI / 2 - 0.5);
        $scale = (1 << $zoom) * self::TILE_SIZE;

        return [(int) ($x * $scale), (int) ($y * $scale)];
    }
}
