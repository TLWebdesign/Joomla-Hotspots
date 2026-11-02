<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

\defined('_JEXEC') or die;

/**
 * Site display controller. Unknown tasks (the map JS sends task=gethotspots) fall back to display().
 */
class DisplayController extends BaseController
{
    protected $default_view = 'hotspots';

    public function display($cachable = false, $urlparams = [])
    {
        $view = $this->input->getCmd('view', $this->default_view);

        // The map and the JSON endpoints depend on the visitor (access levels, language), so no page cache.
        $cachable = $cachable && \in_array($view, ['hotspots'], true) && $this->app->getIdentity()->guest;

        return parent::display($cachable, ['catid' => 'INT', 'id' => 'INT', 'cat' => 'STRING', 'Itemid' => 'INT', 'lang' => 'CMD']);
    }
}
