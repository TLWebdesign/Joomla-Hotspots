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

/**
 * Class HotspotsHelperLocation
 *
 * @since  3.5
 */
class HotspotsHelperLanguage
{
	/**
	 * Currently google supports only those languages
	 * @var array
	 */
	private static $googleLangs = array(
		'ar', 'bg', 'bn', 'ca', 'cs', 'da', 'de', 'el', 'en', 'es', 'eu', 'eu', 'fa', 'fi', 'fil', 'fr', 'gl', 'gu',
		'hi', 'hr', 'hu', 'id', 'it', 'iw', 'ja', 'kn', 'ko', 'lt', 'lv', 'ml', 'mr', 'nl', 'no', 'pl', 'pt', 'ro',
		'ru', 'sk', 'sl', 'sr', 'sv', 'ta', 'te', 'th', 'tl', 'tr', 'uk', 'vi', 'zh'
	);

	/**
	 * Checks if the provided language string is available in google maps. If it is, it returns it
	 * otherwise we return false
	 *
	 * @param   string  $lang  - the langauge code
	 *
	 * @return bool|string  - false when the language is not available, the language when it is
	 */
	public static function mapLang($lang)
	{
		if (in_array($lang, self::$googleLangs))
		{
			return $lang;
		}

		return false;
	}
}
