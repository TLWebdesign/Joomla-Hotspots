<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

/**
 * Non-SEF URLs for Hotspots pages, with the best matching menu item.
 */
abstract class RouteHelper
{
    /**
     * @param   int|string  $id     Marker id or "id:alias".
     * @param   int|string  $catid  Category id or "id:alias".
     */
    public static function getHotspotRoute(int|string $id, int|string $catid = 0): string
    {
        $link = 'index.php?option=com_hotspots&view=hotspot&catid=' . $catid . '&id=' . $id;
        $item = self::findItem((int) $id, (int) $catid);

        return $item ? $link . '&Itemid=' . $item : $link;
    }

    /**
     * Menu item for a marker: one linking to the marker itself, else a map whose start categories
     * include the marker's category, else any map menu item.
     */
    public static function findItem(int $id = 0, int $catid = 0): ?int
    {
        $component = ComponentHelper::getComponent('com_hotspots');
        $items     = Factory::getApplication()->getMenu('site')->getItems('component_id', $component->id);
        $map       = null;
        $catMap    = null;

        foreach ($items as $item) {
            $query = $item->query;

            if ($id && ($query['view'] ?? '') === 'hotspot' && (int) ($query['id'] ?? 0) === $id) {
                return (int) $item->id;
            }

            if (($query['view'] ?? '') === 'hotspots' && !isset($query['layout'])) {
                $map ??= (int) $item->id;

                if ($catid && $catMap === null && \in_array($catid, array_map('intval', (array) $item->getParams()->get('hs_startcat', [])), true)) {
                    $catMap = (int) $item->id;
                }
            }
        }

        return $catMap ?? $map;
    }
}
