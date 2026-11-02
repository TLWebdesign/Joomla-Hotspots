<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2014
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Hotspots;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\GalleryHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

\defined('_JEXEC') or die;

/**
 * The map page. The page itself is a Backbone/Marionette app (media/com_hotspots/js/v4); the templates
 * hold its Handlebars templates.
 */
class HtmlView extends BaseHtmlView
{
    public Registry $settings;

    public User $user;

    /**
     * Modules per Hotspots position.
     *
     * @var  array<string, object[]>
     */
    public array $modules = [];

    /**
     * Top level categories of the category filter.
     *
     * @var  CategoryNode[]
     */
    public array $categories = [];

    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        $this->settings   = SettingsHelper::getAll();
        $this->user       = $app->getIdentity() ?? new User();
        $this->modules    = MapHelper::getModules();
        $this->categories = $this->getFilterCategories();

        $this->prepareDocument();
        $this->loadAssets();

        parent::display($tpl);
    }

    /**
     * App modules after the core (com_hotspots.app), in the order Hotspots 6 concatenated them.
     *
     * @return  string[]  Paths below media/com_hotspots/js/v4/.
     */
    public function getAppScripts(): array
    {
        $s       = $this->settings;
        $scripts = [
            'entities/map.js', 'entities/hotspot.js', 'entities/tab.js', 'entities/category.js', 'entities/direction.js',
            'entities/filter.js', 'entities/marker.js', 'entities/mail.js',
            'common/views.js', 'common/map/show/show_view.js',
            'apps/app/app_app.js', 'apps/app/main/main_view.js',
            'entities/kml.js',
        ];

        if ($s->get('custom_tiles', 0)) {
            array_push($scripts, 'entities/tile.js', 'apps/map/tiles/tiles_controller.js', 'apps/map/tiles/tiles_view.js');
        }

        array_push(
            $scripts,
            'apps/map/map_app.js',
            'apps/map/show/show_controller.js',
            'apps/map/show/show_view.js',
            'apps/map/weather/weather_controller.js',
            'apps/map/markers/markers_controller.js',
            'apps/map/markers/markers_view.js',
            'apps/share/share_app.js',
            'apps/share/mail/mail_controller.js',
            'apps/share/mail/mail_view.js',
            'apps/filter/show/show_view.js'
        );

        $hotspotsTab   = (bool) $s->get('show_tabs_hotspots', 1);
        $directionsTab = (bool) $s->get('show_tabs_directions', 1);
        $settingsTab   = (bool) $s->get('show_tabs_settings', 1);

        if ($hotspotsTab || $directionsTab || $settingsTab) {
            array_push(
                $scripts,
                'apps/menu/menu_app.js',
                'apps/menu/show/show_controller.js',
                'apps/menu/show/show_view.js',
                'apps/tabs/tabs_app.js',
                'apps/tabs/list/list_controller.js',
                'apps/tabs/list/list_view.js',
                'apps/tabs/tabitem/tabitem_view.js',
                'apps/tabs/tabcontentitem/tabcontentitem_view.js',
                'apps/tabs/save/save_controller.js',
                'apps/tabs/remove/remove_controller.js'
            );
        }

        if ($hotspotsTab) {
            array_push(
                $scripts,
                'apps/hotspots/hotspots_app.js',
                'apps/hotspots/list/list_controller.js',
                'apps/hotspots/list/list_view.js',
                'apps/hotspots/tab/tab_controller.js'
            );
        } elseif (!$directionsTab && !$settingsTab) {
            // Without a menu the marker opens in an info window.
            array_push($scripts, 'apps/infowindow/infowindow_app.js', 'apps/infowindow/show/show_controller.js', 'apps/infowindow/show/show_view.js');
        }

        if ($directionsTab) {
            array_push($scripts, 'apps/directions/directions_app.js', 'apps/directions/show/show_controller.js', 'apps/directions/show/show_view.js');
        }

        if ($settingsTab) {
            array_push($scripts, 'apps/settings/settings_app.js', 'apps/settings/show/show_controller.js', 'apps/settings/show/show_view.js');
        }

        return $scripts;
    }

    private function loadAssets(): void
    {
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_hotspots');

        MapHelper::registerGoogleMaps($wa);
        MapHelper::loadJsStrings();
        MapHelper::addConfig($wa);
        GalleryHelper::loadAssets();

        if ($this->settings->get('emulate_bootstrap', 1)) {
            $wa->useStyle('com_hotspots.bootstrap2');
        }

        $wa->usePreset('com_hotspots.map');

        // Each module after the previous one: the app expects them in this order.
        $previous = 'com_hotspots.app';

        foreach ($this->getAppScripts() as $script) {
            $name = 'com_hotspots.v4.' . str_replace(['/', '.js'], ['.', ''], $script);

            if (!$wa->assetExists('script', $name)) {
                $wa->registerScript($name, 'media/com_hotspots/js/v4/' . $script, [], [], [$previous, 'com_hotspots.onimpression', 'com_hotspots.tooltipster', 'com_hotspots.smoothscroll']);
            }

            $wa->useScript($name);
            $previous = $name;
        }

        if ($this->settings->get('show_welcome_text', 1)) {
            $this->getDocument()->addScriptOptions('com_hotspots.tour', MapHelper::getTour());
            $wa->useStyle('com_hotspots.hopscotch');
            $wa->registerAndUseScript('com_hotspots.tour.run', 'media/com_hotspots/js/tour/tour.js', [], [], [$previous, 'com_hotspots.hopscotch']);
        }
    }

    /**
     * Categories of the category filter, without the excluded ones (option exclude_categories).
     *
     * @return  CategoryNode[]
     */
    private function getFilterCategories(): array
    {
        $root = \Joomla\CMS\Categories\Categories::getInstance('Hotspots')?->get('root');

        if (!$root) {
            return [];
        }

        $exclude = array_map('intval', (array) $this->settings->get('exclude_categories', []));

        return array_values(array_filter($root->getChildren(), static fn (CategoryNode $c): bool => !\in_array((int) $c->id, $exclude, true)));
    }

    private function prepareDocument(): void
    {
        $app  = Factory::getApplication();
        $menu = $app->getMenu()->getActive();

        if ($menu) {
            $params = $menu->getParams();

            $this->setDocumentTitle($params->get('page_title', $menu->title));

            if ($params->get('menu-meta_description')) {
                $this->getDocument()->setDescription($params->get('menu-meta_description'));
            }

            if ($params->get('robots')) {
                $this->getDocument()->setMetaData('robots', $params->get('robots'));
            }
        }

        if ($this->settings->get('rss_enable', 0)) {
            $this->getDocument()->addHeadLink(
                \Joomla\CMS\Router\Route::_('index.php?option=com_hotspots&view=hotspots&format=feed&type=rss'),
                'alternate',
                'rel',
                ['type' => 'application/rss+xml', 'title' => 'RSS 2.0']
            );
        }
    }
}
