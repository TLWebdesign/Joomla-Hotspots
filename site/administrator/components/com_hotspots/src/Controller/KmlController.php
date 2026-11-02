<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2011
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;

\defined('_JEXEC') or die;

/**
 * Edit one KML file.
 */
class KmlController extends FormController
{
    /**
     * @var  string
     */
    protected $view_list = 'kmls';

    /**
     * @var  string
     */
    protected $text_prefix = 'COM_HOTSPOTS';

    protected function allowAdd($data = [])
    {
        return $this->app->getIdentity()->authorise('core.create', 'com_hotspots');
    }

    protected function allowEdit($data = [], $key = 'hotspots_kml_id')
    {
        return $this->app->getIdentity()->authorise('core.edit', 'com_hotspots');
    }
}
