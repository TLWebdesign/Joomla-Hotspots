<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Helper;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;

\defined('_JEXEC') or die;

/**
 * Everything the map JavaScript (media/com_hotspots/js/v4) needs from PHP.
 *
 * The JS reads the global HotspotsConfig (and HotspotsMarker, HotspotsTour); media/com_hotspots/js/config.js
 * fills those from the script options com_hotspots.config, com_hotspots.marker and com_hotspots.tour.
 */
abstract class MapHelper
{
    /**
     * Module positions shown in the map page.
     */
    public const MODULE_POSITIONS = [
        'hotspots_above_map', 'hotspots_above_filter', 'hotspots_below_filter', 'hotspots_below_map',
        'hotspots_above_list', 'hotspots_below_list', 'hotspots_below_directions', 'hotspots_below_settings',
    ];

    /**
     * Languages the Google Maps API knows.
     */
    private const GOOGLE_LANGUAGES = [
        'ar', 'bg', 'bn', 'ca', 'cs', 'da', 'de', 'el', 'en', 'es', 'eu', 'fa', 'fi', 'fil', 'fr', 'gl', 'gu', 'hi', 'hr',
        'hu', 'id', 'it', 'iw', 'ja', 'kn', 'ko', 'lt', 'lv', 'ml', 'mr', 'nl', 'no', 'pl', 'pt', 'ro', 'ru', 'sk', 'sl',
        'sr', 'sv', 'ta', 'te', 'th', 'tl', 'tr', 'uk', 'vi', 'zh',
    ];

    /**
     * The HotspotsConfig object of the map JS.
     *
     * @param   array  $overrides  Keys to set or replace (numeric strings become int).
     */
    public static function getConfig(array $overrides = []): array
    {
        $app   = Factory::getApplication();
        $isMap = $app->isClient('site') && $app->getInput()->getCmd('view') === 'hotspots';
        $uri   = Uri::getInstance();
        $s     = SettingsHelper::getAll();
        $int   = static fn (string $key, int $default): int => (int) $s->get($key, $default);
        $str   = static fn (string $key, string $default): string => (string) $s->get($key, $default);

        $config = [
            'rootUrl'            => Uri::root(),
            'baseUrl'            => $uri->toString(['scheme', 'user', 'pass', 'host', 'port', 'path']),
            'mapStartPosition'   => $str('map_startposition', 'Karlsruhe, Germany'),
            'mapStartZoom'       => $int('map_startzoom', 4),
            'mapStartZoomSingle' => $int('map_startzoom_single', 15),
            'centerType'         => $int('map_centertyp', 1),
            'customUserZoom'     => $int('map_center_user_zoom', 15),
        ];

        // Center on the visitor's location (found through a GeoIP provider).
        if ($config['centerType'] === 2 && $isMap) {
            $config['highAccuracy'] = $int('high_accuracy', 0);
            $location               = LocationHelper::getUserLocation();

            if ($location && $location->latitude && $location->longitude) {
                $config['mapStartPosition'] = $location->latitude . ',' . $location->longitude;
                $config['mapStartZoom']     = 10;
            }
        }

        $config['searchZoom'] = $int('search_zoom', 14);

        if ($isMap) {
            $config['startCat'] = array_values(array_map('intval', (array) $s->get('hs_startcat', [])));
        }

        $config += [
            'apiKey'                 => $str('api_key', ''),
            'mapHeight'              => $int('map_height', 500),
            'mapHeightSingle'        => $int('map_height_single', 300),
            'mapHeightSmall'         => $int('map_height_small', 200),
            'startFullscreen'        => $int('hs_start_fullscreen', 0),
            'getDirections'          => $int('routenplaner', 1),
            'mapBackgroundColor'     => $str('map_background', '#E5E3DF'),
            'mapType'                => $int('map_type', 1),
            'panControl'             => $int('panControl', 1),
            'panControlPosition'     => $str('panControlPosition', 'TOP_LEFT'),
            'zoomControl'            => $int('zoomControl', 1),
            'zoomControlStyle'       => $str('zoomControlStyle', 'DEFAULT'),
            'zoomControlPosition'    => $str('zoomControlPosition', 'TOP_LEFT'),
            'mapTypeControl'         => $int('mapTypeControl', 1),
            'mapTypeControlStyle'    => $str('mapTypeControlStyle', 'DEFAULT'),
            'mapTypeControlPosition' => $str('mapTypeControlPosition', 'TOP_RIGHT'),
            'scaleControl'           => $int('scaleControl', 1),
            'fullscreenControl'      => $int('fullscreenControl', 1),
            'streetViewControl'      => $int('streetViewControl', 1),
            'streetViewPosition'     => $str('streetViewPosition', 'TOP_LEFT'),
            'overviewMapControl'     => $int('overviewMapControl', 1),
            'scrollwheel'            => $int('scrollwheel', 1),
            'gestureHandling'        => $str('gestureHandling', 'greedy'),
            'styledMaps'             => $str('styled_maps', ''),
            'userInterface'          => $int('user_interface', 1),
            'print'                  => $int('print_map', 1),
            'resizeMap'              => $int('resize_map', 1),
            'mailMap'                => $int('mail_map', 1),
            'listLength'             => $int('marker_list_length', 20),
            'showDirections'         => $int('show_marker_directions', 1),
            'showAddress'            => $int('show_address', 1),
            'showCountry'            => $int('show_address_country', 0),
            'showZoomButton'         => $int('show_zoom_button', 0),
            'showAuthor'             => $int('show_author', 1),
            'showDate'               => $int('show_date', 1),
            'numOfCatsToShow'        => $int('number_of_cats_to_show', 4),
            'categoryInfo'           => $int('category_info', 4),
            'showMarkerCount'        => $int('show_marker_count', 4),
            'weatherLayer'           => $int('weather_api', 0),
            'weatherLayerApiKey'     => $str('weather_api_key', ''),
            'weatherTemperatureUnit' => $str('weather_api_temperature_unit', 'metric'),
            'trafficLayer'           => $int('traffic_layer', 0),
            'transitLayer'           => $int('transit_layer', 0),
            'bicyclingLayer'         => $int('bicycling_layer', 0),
            'visualRefresh'          => $int('visual_refresh', 1),
            'draggableDirections'    => $int('draggable_directions', 1),
            'startClosedMenu'        => $int('start_closed_menu', 0),
            'startTab'               => $str('tab_opened', 'hotspots'),
            'language'               => $app->getLanguage()->getTag(),
            'tiles'                  => $int('custom_tiles', 0),
            'directionsUnit'         => $str('directions_unit', 'metric'),
            'categories'             => self::getCategoriesInfo(),
            'tabs'                   => self::getTabs(),
            'translations'           => [
                'enterValidAddress'   => Text::_('COM_HOTSPOTS_PLEASE_ENTER_VALID_ADDRESS'),
                'provideStartAddress' => Text::_('COM_HOTSPOTS_PLEASE_PROVIDE_START_ADDRESS'),
                'provideEndAddress'   => Text::_('COM_HOTSPOTS_PLEASE_PROVIDE_END_ADDRESS'),
                'myLocation'          => Text::_('COM_HOTSPOTS_MY_LOCATION'),
            ],
        ];

        foreach ($overrides as $key => $value) {
            $config[trim((string) $key)] = is_numeric($value) ? (int) $value : $value;
        }

        return $config;
    }

    /**
     * Adds the map config as script option and the config.js asset that turns it into HotspotsConfig.
     */
    public static function addConfig(WebAssetManager $wa, array $overrides = []): void
    {
        Factory::getApplication()->getDocument()->addScriptOptions('com_hotspots.config', self::getConfig($overrides));
        $wa->useScript('com_hotspots.config');
    }

    /**
     * Registers the Google Maps API as asset com_hotspots.googlemaps (its URL depends on the key and the language).
     */
    public static function registerGoogleMaps(WebAssetManager $wa, string $callback = ''): void
    {
        if (!$wa->assetExists('script', 'com_hotspots.googlemaps')) {
            $wa->registerScript('com_hotspots.googlemaps', self::getGmapsUrl($callback));
        }
    }

    /**
     * Loads the map of one marker (layout hotspots.single-map, the marker page and the content plugin).
     * One single marker map per page: the JS reads one global HotspotsMarker.
     *
     * @param   array  $marker     id, lat, lng, title, icon
     * @param   array  $overrides  HotspotsConfig keys, e.g. mapHeightSingle, mapStartZoomSingle
     */
    public static function loadSingleMap(array $marker, array $overrides = []): void
    {
        $document = Factory::getApplication()->getDocument();
        $wa       = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_hotspots');

        self::registerGoogleMaps($wa);
        self::loadJsStrings();

        $document->addScriptOptions('com_hotspots.marker', $marker);
        self::addConfig($wa, $overrides);

        if (SettingsHelper::get('emulate_bootstrap', 1)) {
            $wa->useStyle('com_hotspots.bootstrap2');
        }

        $wa->usePreset('com_hotspots.single-map');

        $scripts = [
            'entities/map.js', 'entities/direction.js', 'entities/marker.js', 'common/views.js', 'common/map/show/show_view.js',
            'subapps/hotspot/map/map_app.js', 'subapps/hotspot/map/show/show_controller.js', 'subapps/hotspot/map/show/show_view.js',
            'subapps/hotspot/map/marker/marker_controller.js', 'subapps/hotspot/map/marker/marker_view.js',
        ];

        if (SettingsHelper::get('show_marker_directions', 1)) {
            array_push(
                $scripts,
                'subapps/hotspot/directions/directions_app.js',
                'subapps/hotspot/directions/show/show_controller.js',
                'subapps/hotspot/directions/show/show_view.js'
            );
        }

        $previous = 'com_hotspots.app';

        foreach ($scripts as $script) {
            $name = 'com_hotspots.v4.single.' . str_replace(['/', '.js'], ['.', ''], $script);

            if (!$wa->assetExists('script', $name)) {
                $wa->registerScript($name, 'media/com_hotspots/js/v4/' . $script, [], [], [$previous]);
            }

            $wa->useScript($name);
            $previous = $name;
        }
    }

    public static function getGmapsUrl(string $callback = ''): string
    {
        $params = [];
        $key    = (string) SettingsHelper::get('api_key', '');
        $lang   = (string) SettingsHelper::get('map_language', 'map_auto');

        if ($key !== '') {
            $params['key'] = $key;
        }

        if ($lang === 'site_lang') {
            $tag       = Factory::getApplication()->getLanguage()->getTag();
            $languages = LanguageHelper::getLanguages('lang_code');
            $sef       = isset($languages[$tag]) ? $languages[$tag]->sef : strtolower(substr($tag, 0, 2));

            if (\in_array($sef, self::GOOGLE_LANGUAGES, true)) {
                $params['language'] = $sef;
            }
        } elseif ($lang !== 'map_auto' && $lang !== '') {
            $params['language'] = $lang;
        }

        if ($callback !== '') {
            $params['callback'] = $callback;
        }

        return 'https://maps.googleapis.com/maps/api/js' . ($params ? '?' . http_build_query($params) : '');
    }

    /**
     * Strings the map JS reads with Joomla.Text._().
     */
    public static function loadJsStrings(): void
    {
        $strings = [
            'COM_HOTSPOTS_JS_DIRECTIONS', 'COM_HOTSPOTS_GET_DIRECTIONS', 'COM_HOTSPOTS_ZOOM', 'COM_HOTSPOTS_TO',
            'COM_HOTSPOTS_FROM', 'COM_HOTSPOTS_SUBMIT', 'COM_HOTSPOTS_LOADING_DATA', 'COM_HOTSPOTS_NO_HOTSPOTS_IN_CATEGORY',
            'COM_HOTSPOTS_MORE_HOTSPOTS', 'COM_HOTSPOTS_READ_MORE', 'COM_HOTSPOTS_CANCEL', 'COM_HOTSPOTS_COULDNT_FIND_LOCATION',
            'COM_HOTSPOTS_ZERO_RESULTS_LOCATION', 'COM_HOTSPOTS_PRINT', 'COM_HOTSPOTS_SOMETHING_IS_WRONG',
            'COM_HOTSPOTS_ENTER_FULL_DESCRIPTION', 'COM_HOTSPOTS_ENTER_SOBI2_ID', 'COM_HOTSPOTS_ENTER_ARTICLE_ID',
            'COM_HOTSPOTS_GEOLOCATION_NO_SUPPORT', 'COM_HOTSPOTS_DRAG_ME', 'COM_HOTSPOTS_THERE_ARE', 'COM_HOTSPOTS_THERE_IS',
            'COM_HOTSPOTS_EMAIL_THIS_MAP', 'COM_HOTSPOTS_CLEAR_ROUTE', 'COM_HOTSPOTS_SEND', 'COM_HOTSPOTS_CLOSE',
            'COM_HOTSPOTS_SEARCH_RETURNED_NO_RESULTS', 'COM_HOTSPOTS_POSTED_BY', 'COM_HOTSPOTS_ON',
            'COM_HOTSPOTS_IN_YOUR_CURRENT_VIEW_THERE_ARE', 'COM_HOTSPOTS_HOTSPOTS', 'COM_HOTSPOTS_SEARCH_RESULTS_AROUND_THE_WORLD',
            'COM_HOTSPOTS_SEARCH_RETURNED_NO_RESULTS_IN_THIS_VIEW', 'COM_HOTSPOTS_SEARCH_IN_YOUR_CURRENT_VIEW_RETURNED',
            'COM_HOTSPOTS_NO_LOCATIONS_IN_CURRENT_VIEW',
        ];

        foreach ($strings as $string) {
            Text::script($string);
        }
    }

    /**
     * Published Hotspots categories the visitor may see, with the bounding box of their markers.
     */
    public static function getCategoriesInfo(): array
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.catid'),
                'MIN(' . $db->quoteName('m.gmlat') . ') AS ' . $db->quoteName('south'),
                'MAX(' . $db->quoteName('m.gmlat') . ') AS ' . $db->quoteName('north'),
                'MAX(' . $db->quoteName('m.gmlng') . ') AS ' . $db->quoteName('east'),
                'MIN(' . $db->quoteName('m.gmlng') . ') AS ' . $db->quoteName('west'),
            ])
            ->from($db->quoteName('#__hotspots_marker', 'm'))
            ->join('INNER', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('m.catid'))
            ->where($db->quoteName('m.state') . ' = 1')
            ->where($db->quoteName('c.published') . ' = 1')
            ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_hotspots'))
            ->where('(' . $db->quoteName('m.publish_up') . ' IS NULL OR ' . $db->quoteName('m.publish_up') . ' <= :now1)')
            ->where('(' . $db->quoteName('m.publish_down') . ' IS NULL OR ' . $db->quoteName('m.publish_down') . ' >= :now2)')
            ->whereIn($db->quoteName('m.access'), self::viewLevels())
            ->bind(':now1', $now)
            ->bind(':now2', $now)
            ->group($db->quoteName('m.catid'));

        if ($app->isClient('site') && $app->getLanguageFilter()) {
            $query->whereIn($db->quoteName('m.language'), [$app->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        $boundaries = $db->setQuery($query)->loadObjectList('catid');
        $result     = [];

        foreach (self::getCategories() as $category) {
            $info = self::prepareCategory($category);

            if (isset($boundaries[$category->id])) {
                $info->boundaries = $boundaries[$category->id];
            }

            $result[] = $info;
        }

        return $result;
    }

    /**
     * All published Hotspots categories the visitor may see (flat, in tree order).
     *
     * @return  CategoryNode[]
     */
    public static function getCategories(): array
    {
        $root = Categories::getInstance('Hotspots')?->get('root');

        return $root ? $root->getChildren(true) : [];
    }

    /**
     * Ids of the categories from getCategories().
     *
     * @return  int[]
     */
    public static function getCategoryIds(): array
    {
        return array_map(static fn (CategoryNode $c): int => (int) $c->id, self::getCategories());
    }

    public static function prepareCategory(CategoryNode $category): \stdClass
    {
        $params = new Registry($category->params);

        $info                  = new \stdClass();
        $info->id              = (int) $category->id;
        $info->cat_name        = $category->title;
        $info->cat_description = $category->description;
        $info->cat_icon        = Uri::root() . $params->get('icon', '');
        $info->params          = $params;

        return $info;
    }

    /**
     * Nested <ul> with a checkbox per category (category filter of the map).
     *
     * @param   CategoryNode[]  $categories
     */
    public static function renderNestedCategories(array $categories, string $class = ''): string
    {
        $html = '<ul class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';

        foreach ($categories as $item) {
            $params      = new Registry($item->params);
            $description = trim(strip_tags(Text::_((string) $item->description)));
            $title       = $description !== '' ? ' title="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '"' : '';

            $html .= '<li id="hs-category-' . (int) $item->id . '" class="hs-parent-is-' . (int) $item->getParent()->id . '">';
            $html .= '<label class="checkbox hs-checkbox-filter-cats"' . $title . '>';
            $html .= '<input type="checkbox" value="' . (int) $item->id . '" name="cats" />';
            $html .= '<img src="' . htmlspecialchars((string) $params->get('icon', ''), ENT_QUOTES, 'UTF-8') . '" alt="cat-icon"/>';
            $html .= htmlspecialchars(Text::_($item->title), ENT_QUOTES, 'UTF-8');
            $html .= '</label>';

            if ($item->hasChildren()) {
                $html .= self::renderNestedCategories($item->getChildren(), 'hs-children-of-' . (int) $item->id);
            }

            $html .= '</li>';
        }

        return $html . '</ul>';
    }

    /**
     * Modules per Hotspots position.
     *
     * @return  array<string, object[]>
     */
    public static function getModules(): array
    {
        $modules = [];

        foreach (self::MODULE_POSITIONS as $position) {
            $modules[$position] = ModuleHelper::getModules($position);
        }

        return $modules;
    }

    public static function renderModules(array $modules): string
    {
        $html = '';

        foreach ($modules as $module) {
            $html .= ModuleHelper::renderModule($module);
        }

        return $html;
    }

    /**
     * The welcome tour (hopscotch) shown on the first visit of the map.
     */
    public static function getTour(): array
    {
        $s     = SettingsHelper::getAll();
        $steps = [[
            'title'       => Text::_('COM_HOTSPOTS_TOUR_WELCOME_LABEL'),
            'content'     => (string) $s->get('welcome_text', ''),
            'target'      => 'js-hs-main-app',
            'placement'   => 'top',
            'xOffset'     => 'center',
            'zindex'      => 9999,
            'arrowOffset' => 'center',
            'onShow'      => 'startEnd',
        ]];

        if ($s->get('show_tabs_hotspots', 1)) {
            if ($s->get('show_tabs_title', 1)) {
                $steps[] = [
                    'title'       => Text::_('COM_HOTSPOTS_TOUR_HOTSPOTS_TAB_LABEL'),
                    'content'     => Text::_('COM_HOTSPOTS_TOUR_HOTSPOTS_TAB_DESC'),
                    'target'      => "#js-tabs-region li[data-name='hotspots']",
                    'placement'   => 'bottom',
                    'zindex'      => 9999,
                    'xOffset'     => 'center',
                    'arrowOffset' => 'center',
                    'onShow'      => 'hotspotsTab',
                ];
            }

            $steps[] = [
                'title'          => Text::_('COM_HOTSPOTS_TOUR_FILTER_LABEL'),
                'content'        => Text::_('COM_HOTSPOTS_TOUR_FILTER_DESC'),
                'target'         => '#js-hs-tab-filter-region',
                'placement'      => 'bottom',
                'xOffset'        => 'center',
                'arrowOffset'    => 'center',
                'zindex'         => 9999,
                'showPrevButton' => true,
                'onShow'         => 'hotspotsTab',
            ];
        }

        $findPosition = (bool) $s->get('find_position', 1);
        $resize       = (bool) $s->get('resize_map', 1);

        if ($findPosition || $resize) {
            $titles = [];
            $texts  = [];

            if ($findPosition) {
                $titles[] = Text::_('COM_HOTSPOTS_CENTER');
                $texts[]  = Text::_('COM_HOTSPOTS_TOUR_CENTER_BUTTON_DESC');
            }

            if ($resize) {
                $titles[] = Text::_('COM_HOTSPOTS_FULLSCREEN');
                $texts[]  = Text::_('COM_HOTSPOTS_TOUR_FULLSCREEN_BUTTON_DESC');
            }

            $steps[] = [
                'title'          => implode(' & ', $titles),
                'content'        => implode('', $texts),
                'target'         => '.hs-buttons-map',
                'placement'      => 'left',
                'yOffset'        => -20,
                'zindex'         => 9999,
                'showPrevButton' => true,
            ];
        }

        if ($s->get('show_tabs_directions')) {
            $steps[] = [
                'title'             => Text::_('COM_HOTSPOTS_TOUR_ADDRESS_LABEL'),
                'content'           => Text::_('COM_HOTSPOTS_TOUR_ADDRESS_DESC'),
                'target'            => '#js-hs-search-address-form',
                'placement'         => 'bottom',
                'xOffset'           => 'center',
                'arrowOffset'       => 'center',
                'zindex'            => 9999,
                'showPrevButton'    => true,
                'nextOnTargetClick' => true,
                'onShow'            => 'directionsTab',
            ];
        }

        $steps[] = [
            'title'          => Text::_('COM_HOTSPOTS_TOUR_END_LABEL'),
            'content'        => Text::_('COM_HOTSPOTS_TOUR_END_DESC'),
            'target'         => '#js-hs-main-app',
            'placement'      => 'top',
            'zindex'         => 9999,
            'showPrevButton' => true,
            'xOffset'        => 'center',
            'arrowOffset'    => 'center',
            'onShow'         => 'startEnd',
        ];

        return [
            'id'      => 'hotspots-tour',
            'onEnd'   => 'cookie',
            'onClose' => 'cookie',
            'steps'   => $steps,
            'i18n'    => [
                'nextBtn' => Text::_('COM_HOTSPOTS_NEXT_BTN'),
                'prevBtn' => Text::_('COM_HOTSPOTS_PREV_BTN'),
                'doneBtn' => Text::_('COM_HOTSPOTS_DONE_BTN'),
            ],
        ];
    }

    /**
     * View access levels of the current visitor.
     *
     * @return  int[]
     */
    public static function viewLevels(): array
    {
        $user = Factory::getApplication()->getIdentity();

        return array_values(array_unique(array_map('intval', $user ? $user->getAuthorisedViewLevels() : [1])));
    }

    private static function getTabs(): array
    {
        $s    = SettingsHelper::getAll();
        $tabs = [];

        if ($s->get('show_tabs_directions', 1)) {
            $tabs[] = ['id' => 1, 'tabName' => '<span class="fa fa-location-arrow"></span> ' . Text::_('COM_HOTSPOTS_DIRECTIONS'), 'dataName' => 'directions', 'content' => ''];
        }

        if ($s->get('show_tabs_hotspots', 1)) {
            $tabs[] = ['id' => 2, 'tabName' => '<span class="fa fa-map-marker"></span> ' . Text::_('COM_HOTSPOTS_HOTSPOTS'), 'dataName' => 'hotspots', 'content' => ''];
        }

        if ($s->get('show_tabs_settings', 1)) {
            $tabs[] = ['id' => 3, 'tabName' => '<span class="fa fa-cog"></span> ' . Text::_('COM_HOTSPOTS_SETTINGS'), 'dataName' => 'settings', 'content' => ''];
        }

        return $tabs;
    }
}
