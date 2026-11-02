<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.04.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperColor
 *
 * @since  3.2
 */
class HotspotsHelperColor
{
	/**
	 * @param $hex
	 *
	 * @return string rgb
	 */
	public static function hex2rgb($hex)
	{
		$hex = str_replace("#", "", $hex);

		if (strlen($hex) == 3)
		{
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		}
		else
		{
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}

		$rgb = array($r, $g, $b);
		return implode(",", $rgb);
	}

	/**
	 * @param $rgb - string with commas
	 *
	 * @return string
	 */
	public static function rgb2hex($rgb)
	{
		$hex = "#";
		$rgb = explode(',', $rgb);
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

		return $hex;
	}
}
