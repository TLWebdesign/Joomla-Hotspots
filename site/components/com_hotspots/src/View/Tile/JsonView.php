<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Tile;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\AbstractView;

\defined('_JEXEC') or die;

/**
 * Markers in one map tile, so the tile layer can make them clickable: [{id, lng, lat, title}]
 * (index.php?option=com_hotspots&view=tile&format=json&x=&y=&z=).
 */
class JsonView extends AbstractView
{
    public function display($tpl = null)
    {
        $model = Factory::getApplication()->bootComponent('com_hotspots')->getMVCFactory()->createModel('Tile', 'Site');

        echo json_encode($model->getItems(false));
    }
}
