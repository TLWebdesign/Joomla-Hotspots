<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Kmls;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

\defined('_JEXEC') or die;

/**
 * KML files of the shown categories: [{cat, file}] (index.php?option=com_hotspots&view=kmls&format=json&cat=1;2).
 */
class JsonView extends AbstractView
{
    public function display($tpl = null)
    {
        $visible = MapHelper::getCategoryIds();
        $cat     = Factory::getApplication()->getInput()->getString('cat', '');
        $asked   = array_filter(array_map('intval', explode(';', $cat)));
        $cats    = trim($cat) === '' ? $visible : array_values(array_intersect($asked, $visible));
        $json    = [];

        if ($cats) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['catid', 'mangled_filename']))
                ->from($db->quoteName('#__hotspots_kmls'))
                ->whereIn($db->quoteName('catid'), $cats)
                ->where($db->quoteName('state') . ' = 1');

            foreach ($db->setQuery($query)->loadObjectList() as $kml) {
                $json[] = [
                    'cat'  => (int) $kml->catid,
                    'file' => Uri::root() . 'media/com_hotspots/kmls/' . rawurlencode(basename((string) $kml->mangled_filename)),
                ];
            }
        }

        echo json_encode($json);
    }
}
