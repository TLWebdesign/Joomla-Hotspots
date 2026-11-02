<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use TLWebdesign\Component\Hotspots\Administrator\Helper\GalleryHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\JsonHelper;
use TLWebdesign\Component\Hotspots\Site\Model\HotspotModel;

\defined('_JEXEC') or die;

/**
 * Map tasks: one marker as JSON (the tile layer and the single marker map) and the old RSS URL.
 */
class HotspotsController extends BaseController
{
    /**
     * index.php?option=com_hotspots&task=hotspots.hotspot&format=json&id=N
     */
    public function hotspot(): void
    {
        /** @var HotspotModel $model */
        $model  = $this->getModel('Hotspot', 'Site', ['ignore_request' => false]);
        $marker = $model->getItem($this->input->getInt('id'));

        if (!$marker) {
            throw new \Exception('The resource doesn\'t exist', 404);
        }

        $marker->multimedia = GalleryHelper::getData((int) $marker->id, false, 'small', 'medium') ?: '';
        $marker             = MarkerHelper::prepare($marker);

        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $this->app->sendHeaders();

        echo json_encode(JsonHelper::marker($marker));

        $this->app->close();
    }

    /**
     * Hotspots 6 RSS URL (task=hotspots.rss): the feed is now view=hotspots&format=feed.
     */
    public function rss(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_hotspots&view=hotspots&format=feed&type=rss', false));
    }
}
