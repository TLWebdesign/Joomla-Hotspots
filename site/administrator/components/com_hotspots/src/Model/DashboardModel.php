<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Numbers and latest markers for the dashboard.
 */
class DashboardModel extends BaseDatabaseModel
{
    /**
     * @return  array{all: int, published: int, unpublished: int}
     */
    public function getStats(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['COUNT(*) AS ' . $db->quoteName('total'), 'SUM(' . $db->quoteName('state') . ' = 1) AS ' . $db->quoteName('published')])
            ->from($db->quoteName('#__hotspots_marker'));
        $row = $db->setQuery($query)->loadObject();

        $all       = (int) ($row->total ?? 0);
        $published = (int) ($row->published ?? 0);

        return ['all' => $all, 'published' => $published, 'unpublished' => $all - $published];
    }

    /**
     * The latest markers for the dashboard map, in the shape the layout hotspots.map.map expects.
     * The link opens the marker in the editor.
     */
    public function getLatestMarkers(int $limit = 25): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['m.id', 'm.title', 'm.gmlat', 'm.gmlng', 'm.params', 'c.params'], ['id', 'title', 'latitude', 'longitude', 'params', 'cat_params']))
            ->from($db->quoteName('#__hotspots_marker', 'm'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('m.catid'))
            ->order($db->quoteName('m.created') . ' DESC');

        $markers = $db->setQuery($query, 0, $limit)->loadObjectList();

        foreach ($markers as $marker) {
            $params     = new Registry($marker->params);
            $catParams  = new Registry($marker->cat_params);
            $icon       = (string) ($params->get('markerimage') ?: $catParams->get('icon', ''));

            $marker->hotspots_id       = (int) $marker->id;
            $marker->icon              = $icon !== '' ? Uri::root() . $icon : '';
            $marker->link              = 'index.php?option=com_hotspots&task=hotspot.edit&id=' . (int) $marker->id;
            $marker->description_small = '';
            unset($marker->params, $marker->cat_params);
        }

        return $markers;
    }

    /**
     * Version and date from the component manifest.
     *
     * @return  array{version: string, date: string}
     */
    public function getVersion(): array
    {
        $data = Installer::parseXMLInstallFile(JPATH_ADMINISTRATOR . '/components/com_hotspots/hotspots.xml') ?: [];

        return ['version' => (string) ($data['version'] ?? ''), 'date' => (string) ($data['creationDate'] ?? '')];
    }
}
