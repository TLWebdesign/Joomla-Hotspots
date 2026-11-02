<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Module\HotspotsList\Site\Dispatcher;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use TLWebdesign\Component\Hotspots\Administrator\Helper\GalleryHelper;

\defined('_JEXEC') or die;

/**
 * Latest markers.
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    public function dispatch()
    {
        // The module needs the component (its helpers and language).
        if (!ComponentHelper::isEnabled('com_hotspots')) {
            return;
        }

        parent::dispatch();
    }

    protected function getLayoutData()
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];
        $list   = $this->getHelperFactory()->getHelper('HotspotsListHelper')->getMarkers($params, $this->getApplication());

        $data['list']   = $list;
        $data['images'] = ($list && $params->get('image', 0))
            ? (GalleryHelper::getData(array_map(static fn ($marker) => (int) $marker->id, $list), false, 'small', 'medium') ?: [])
            : [];

        $this->getApplication()->getLanguage()->load('com_hotspots', JPATH_SITE);

        return $data;
    }
}
