<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Service;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use TLWebdesign\Component\Hotspots\Site\Helper\RouteHelper;

\defined('_JEXEC') or die;

/**
 * SEF URLs, the same as Hotspots 6:
 *
 * - marker under a map menu item:   <menu route>/<catid>:<category alias>/<id>:<marker alias>
 * - marker under its own menu item: <menu route>
 * - frontend edit form:             <menu route>/edit/<id>
 * - without a menu item:            component/hotspots/<view>[/<catid>:<alias>]/<id>:<alias>
 */
class Router extends RouterBase
{
    /**
     * Views that can appear as a URL segment.
     */
    private const VIEWS = ['hotspots', 'hotspot', 'form', 'userhotspots'];

    public function __construct(
        SiteApplication $app,
        AbstractMenu $menu,
        ?CategoryFactoryInterface $categoryFactory = null,
        ?DatabaseInterface $db = null
    ) {
        parent::__construct($app, $menu);
    }

    public function preprocess($query)
    {
        if (!empty($query['Itemid'])) {
            return $query;
        }

        $view = $query['view'] ?? '';

        if ($view === 'hotspot') {
            $item = RouteHelper::findItem((int) ($query['id'] ?? 0), (int) ($query['catid'] ?? 0));
        } elseif ($view === 'hotspots' || $view === '') {
            $item = RouteHelper::findItem();
        } else {
            $item = $this->findViewItem($view);
        }

        if ($item) {
            $query['Itemid'] = $item;
        }

        return $query;
    }

    public function build(&$query)
    {
        $segments = [];
        $menuItem = empty($query['Itemid']) ? null : $this->menu->getItem($query['Itemid']);

        if ($menuItem && $menuItem->component !== 'com_hotspots') {
            $menuItem = null;
        }

        $mView = $menuItem->query['view'] ?? null;
        $mId   = (int) ($menuItem->query['id'] ?? 0);
        $view  = $query['view'] ?? null;

        // Tasks without a view and the JSON, tile and feed endpoints keep their query string.
        if ($view === null || !\in_array($view, self::VIEWS, true)) {
            return $segments;
        }

        if ($view !== null) {
            unset($query['view']);

            if ($menuItem === null || ($view !== $mView && !\in_array($view, ['hotspot', 'form'], true))) {
                $segments[] = $view;
            }
        }

        if ($view === 'hotspot') {
            if ($mView === 'hotspot' && isset($query['id']) && $mId === (int) $query['id']) {
                unset($query['catid'], $query['id']);

                return $segments;
            }

            if (isset($query['catid'])) {
                if ((int) $query['catid'] > 0) {
                    $segments[] = (string) $query['catid'];
                }

                unset($query['catid']);
            }
        }

        if (isset($query['layout'])) {
            if ($query['layout'] !== 'userhotspots' && $query['layout'] !== 'default') {
                $segments[] = $query['layout'];
            }

            unset($query['layout']);
        }

        if ($view === 'form' && !\in_array('edit', $segments, true)) {
            $segments[] = 'edit';
        }

        if (isset($query['id'])) {
            if (!($mId && $mId === (int) $query['id'])) {
                $segments[] = (string) $query['id'];
            }

            unset($query['id']);
        }

        return $segments;
    }

    public function parse(&$segments)
    {
        $vars     = [];
        $segments = array_values(array_filter($segments, static fn ($s) => $s !== ''));

        if (!$segments) {
            return $vars;
        }

        if ($segments[0] === 'edit' || $segments[0] === 'form') {
            array_shift($segments);
            $vars['view']   = 'form';
            $vars['layout'] = 'edit';
        } elseif (\in_array($segments[0], self::VIEWS, true)) {
            $vars['view'] = array_shift($segments);
        }

        // What is left are ids: "<catid>:<alias>/<id>:<alias>" or "<id>:<alias>".
        $ids = [];

        foreach ($segments as $segment) {
            if (!preg_match('/^(\d+)(?:[:\-].*)?$/', $segment, $match)) {
                // Leave unknown segments, so Joomla gives a 404.
                return $vars;
            }

            $ids[] = (int) $match[1];
        }

        if (\count($ids) > 2) {
            return $vars;
        }

        $segments = [];

        if (!$ids) {
            return $vars;
        }

        if (!isset($vars['view'])) {
            $vars['view'] = 'hotspot';
        }

        if (\count($ids) === 2) {
            $vars['catid'] = $ids[0];
            $vars['id']    = $ids[1];
        } else {
            $vars['id'] = $ids[0];
        }

        return $vars;
    }

    /**
     * First Hotspots menu item for a view (form, userhotspots).
     */
    private function findViewItem(string $view): ?int
    {
        foreach ($this->menu->getItems('component', 'com_hotspots') as $item) {
            if (($item->query['view'] ?? '') === $view) {
                return (int) $item->id;
            }
        }

        return null;
    }
}
