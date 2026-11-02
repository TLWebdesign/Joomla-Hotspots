<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

\defined('_JEXEC') or die;

/**
 * The generated map tiles (site cache folder com_hotspots.tiles).
 */
class TilesController extends BaseController
{
    /**
     * Deletes all generated tiles; they are created again when the map asks for them.
     */
    public function delete(): void
    {
        $this->checkToken('request');

        $redirect = Route::_('index.php?option=com_hotspots&view=dashboard', false);

        if (!$this->app->getIdentity()->authorise('core.admin', 'com_hotspots')) {
            $this->setRedirect($redirect, Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return;
        }

        $path    = \TLWebdesign\Component\Hotspots\Site\Helper\TileHelper::cacheFolder();
        $files   = is_dir($path) ? Folder::files($path, '.', true, true, ['index.html']) : [];
        $message = Text::_('COM_HOTSPOTS_TILES_SUCCESSFULLY_DELETED');
        $type    = 'message';

        foreach ($files as $file) {
            if (!File::delete($file)) {
                $message = Text::_('COM_HOTSPOTS_TILES_DELETE_UNSUCCESSFUL');
                $type    = 'error';
            }
        }

        $this->setRedirect($redirect, $message, $type);
    }
}
