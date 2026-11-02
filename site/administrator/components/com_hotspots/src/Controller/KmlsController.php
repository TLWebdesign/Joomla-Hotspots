<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2011
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;

\defined('_JEXEC') or die;

/**
 * Publish, unpublish and delete KML files. The parent tasks check the CSRF token, the model the permissions.
 */
class KmlsController extends AdminController
{
    /**
     * @var  string
     */
    protected $text_prefix = 'COM_HOTSPOTS';

    public function getModel($name = 'Kml', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
