<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Component options, on the site merged with the options of the active Hotspots menu item.
 */
abstract class SettingsHelper
{
    private static ?Registry $settings = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getAll()->get($key, $default);
    }

    public static function getAll(): Registry
    {
        if (self::$settings === null) {
            self::$settings = self::load();
        }

        return self::$settings;
    }

    /**
     * Forget the loaded settings (tests, or after the active menu item changed).
     */
    public static function reset(): void
    {
        self::$settings = null;
    }

    private static function load(): Registry
    {
        $params = clone ComponentHelper::getParams('com_hotspots');
        $app    = Factory::getApplication();

        if (!$app->isClient('site')) {
            return $params;
        }

        $item = $app->getMenu()?->getActive();

        if (!$item || $item->component !== 'com_hotspots') {
            return $params;
        }

        $menuParams = $item->getParams();

        foreach ($menuParams->toArray() as $key => $value) {
            if ($key === 'show_page_heading') {
                $key = 'show_page_title';
            }

            // An empty styled map in the menu item keeps the global one.
            if ($key === 'styled_maps' && trim((string) $value) === '') {
                continue;
            }

            $params->set($key, $value);
        }

        // "key=value" lines in the menu item override any option.
        foreach (preg_split('/\R/', (string) $menuParams->get('settings_override', '')) as $line) {
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            if ($key !== '') {
                $params->set($key, is_numeric($value) ? (int) $value : $value);
            }
        }

        return $params;
    }
}
