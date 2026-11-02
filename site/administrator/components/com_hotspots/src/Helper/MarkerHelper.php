<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Helper;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Site\Helper\RouteHelper;

\defined('_JEXEC') or die;

/**
 * Prepares markers for output. Public API, also used by the module, the content plugin and the client's CB plugin.
 */
abstract class MarkerHelper
{
    /**
     * URL path of the folder with the category and marker icons.
     */
    public static function iconBaseUrl(): string
    {
        return Uri::root() . 'media/com_hotspots/images/categories/';
    }

    /**
     * Makes params a Registry, runs content plugins on the short description when enabled,
     * makes its links absolute, and sets postdate and link.
     *
     * The marker needs at least id, catid, title, created, params and description_small.
     */
    public static function prepare(object $marker): object
    {
        if (!$marker->params instanceof Registry) {
            $marker->params = new Registry($marker->params ?? '');
        }

        $marker->hotspots_id ??= $marker->id;

        $description = (string) ($marker->description_small ?? '');

        if ((int) SettingsHelper::get('marker_allow_plugin', 0) === 1) {
            $description = HTMLHelper::_('content.prepare', $description, '', 'com_hotspots.marker');
        }

        $marker->description_small = self::absoluteLinks($description);
        $marker->postdate          = self::localDate($marker->created ?? null);
        $marker->link              = self::link($marker);

        return $marker;
    }

    /**
     * The "Read more" link of a marker: a hotspotslinks plugin target (article, URL) or the marker page.
     * Empty when read more is switched off.
     */
    public static function link(object $marker): string
    {
        $params = $marker->params instanceof Registry ? $marker->params : new Registry($marker->params ?? '');

        $readMore = $params->get('show_readmore');

        if (!(($readMore === null || $readMore === '') ? SettingsHelper::get('hotspot_detailpage', 1) : $readMore)) {
            return '';
        }

        $type   = (string) $params->get('link_to', '');
        $linkId = $params->get('link_to_id', '');

        if ($type !== '' && $type !== '0' && $linkId !== '' && PluginHelper::isEnabled('hotspotslinks', $type)) {
            PluginHelper::importPlugin('hotspotslinks', $type);

            $event = new Event('onCreateLink', [
                'linkId'   => $linkId,
                'markerId' => (int) ($marker->hotspots_id ?? $marker->id),
                'type'     => $type,
                'result'   => [],
            ]);

            $links = Factory::getApplication()->getDispatcher()->dispatch('onCreateLink', $event)->getArgument('result', []);

            if (!empty($links[0])) {
                return (string) $links[0];
            }
        }

        return self::markerUrl($marker);
    }

    /**
     * SEF URL of the marker page.
     */
    public static function markerUrl(object $marker): string
    {
        $id       = (int) ($marker->hotspots_id ?? $marker->id);
        $catid    = (int) $marker->catid;
        $category = Categories::getInstance('Hotspots')?->get($catid);
        $catSlug  = $category ? $catid . ':' . $category->alias : (string) $catid;
        $slug     = $id . ':' . OutputFilter::stringURLSafe((string) $marker->title);

        return Route::link('site', RouteHelper::getHotspotRoute($slug, $catSlug), false);
    }

    /**
     * The address in the format of the "address_format" option. With $itemprop the parts get schema.org markup.
     * The parts are escaped.
     */
    public static function formattedAddress(object $marker, bool $itemprop = false): string
    {
        $format = (string) SettingsHelper::get('address_format', '{{street}}, {{zip}} {{city}}, {{state}}, {{country}}');
        $parts  = [
            '{{street}}'  => ['street', 'streetAddress'],
            '{{zip}}'     => ['plz', 'postalCode'],
            '{{city}}'    => ['town', 'addressLocality'],
            '{{country}}' => ['country', 'addressCountry'],
            '{{state}}'   => ['administrative_area_level_1', 'addressRegion'],
        ];

        $replace = [];

        foreach ($parts as $placeholder => [$field, $prop]) {
            $value = htmlspecialchars((string) ($marker->$field ?? ''), ENT_QUOTES, 'UTF-8');

            $replace[$placeholder] = ($itemprop && $value !== '') ? '<span itemprop="' . $prop . '">' . $value . '</span>' : $value;
        }

        $address = str_replace(array_keys($replace), array_values($replace), $format);

        // Drop the separators of empty parts: ", , " and leading or trailing commas.
        $address = preg_replace(['/(\s*,\s*)+(?=,)/', '/^\s*,\s*|\s*,\s*$/'], ['', ''], $address);

        return trim($address);
    }

    /**
     * A date from the database (UTC) in the user's time zone, in the "date_format" option.
     */
    public static function localDate(?string $date): string
    {
        if ($date === null || $date === '' || str_starts_with($date, '0000')) {
            return '';
        }

        return HTMLHelper::_('date', $date, (string) SettingsHelper::get('date_format', 'Y-m-d H:i:s'));
    }

    /**
     * The short description is shown in the map (outside of Joomla's SEF plugin), so relative links and
     * images would break on SEF URLs: route index.php links and make relative src/href absolute.
     */
    private static function absoluteLinks(string $content): string
    {
        if ($content === '' || !Factory::getApplication()->get('sef')) {
            return $content;
        }

        $content = preg_replace_callback(
            '#href="index\.php\?([^"]*)#m',
            static fn (array $m): string => 'href="' . Route::_('index.php?' . str_replace('&amp;', '&', $m[1])),
            $content
        );

        $base      = Uri::base(true) . '/';
        $protocols = '[a-zA-Z0-9\-]+:';

        return preg_replace('#(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m', '$1="' . $base . '$2"', $content);
    }
}
