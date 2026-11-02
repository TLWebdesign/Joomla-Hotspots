<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Jsonv4;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\AbstractView;
use TLWebdesign\Component\Hotspots\Administrator\Helper\GalleryHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\UserHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\JsonHelper;
use TLWebdesign\Component\Hotspots\Site\Model\JsonModel;

\defined('_JEXEC') or die;

/**
 * Markers for the map as JSON: {total_entries, items[, boundaries]} (index.php?option=com_hotspots&view=jsonv4&format=raw).
 */
class RawView extends AbstractView
{
    public function display($tpl = null)
    {
        /** @var JsonModel $model */
        $model = Factory::getApplication()->bootComponent('com_hotspots')->getMVCFactory()->createModel('Json', 'Site');
        $list  = $model->getHotspots();

        $markers = $list['hotspots'];
        $ids     = array_map(static fn ($m): int => (int) $m->id, $markers);
        $catids  = array_values(array_unique(array_map(static fn ($m): int => (int) $m->catid, $markers)));
        $userIds = array_filter(array_map(static fn ($m): int => (int) $m->created_by, $markers));
        $gallery = $ids ? (GalleryHelper::getData($ids, false, 'small', 'medium') ?: []) : [];
        $users   = $userIds ? UserHelper::getUsers($userIds) : [];
        $fields  = JsonHelper::customFields($catids);
        $items   = [];
        $lats    = [];
        $lngs    = [];

        foreach ($markers as $marker) {
            $marker->multimedia = $gallery[(int) $marker->id] ?? '';
            $items[]            = JsonHelper::marker(MarkerHelper::prepare($marker), $users, $fields);
            $lats[]             = (float) $marker->gmlat;
            $lngs[]             = (float) $marker->gmlng;
        }

        $output = [];

        // The search found markers outside the view: tell the map where they are.
        if (!empty($list['newboundaries']) && $items) {
            $output['boundaries'] = ['n' => max($lats), 'e' => max($lngs), 's' => min($lats), 'w' => min($lngs)];
        }

        $output['total_entries'] = (int) $list['viewCount'];
        $output['items']         = $items;

        $this->getDocument()->setMimeEncoding('application/json');

        echo json_encode($output);
    }
}
