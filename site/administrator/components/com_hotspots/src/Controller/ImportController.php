<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2014 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use TLWebdesign\Component\Hotspots\Administrator\Model\ImportModel;

\defined('_JEXEC') or die;

/**
 * KML import: upload, then import with a style to category mapping.
 */
class ImportController extends BaseController
{
    public function upload(): void
    {
        $this->checkToken();
        $this->checkCreate();

        /** @var ImportModel $model */
        $model = $this->getModel('Import', 'Administrator');
        $files = $this->input->files->get('kml_files', [], 'raw');

        // A single file input gives one array, a "multiple" input a list of them.
        $files = isset($files['name']) ? [$files] : (array) $files;
        $count = $model->storeUploads($files);

        foreach ($model->getErrors() as $error) {
            $this->app->enqueueMessage((string) $error, 'warning');
        }

        if (!$count) {
            $this->setRedirect(Route::_('index.php?option=com_hotspots&view=import', false), Text::_('COM_HOTSPOTS_IMPORT_ERR_NOFILES'), 'error');

            return;
        }

        $this->setRedirect(Route::_('index.php?option=com_hotspots&view=import&layout=mapping', false));
    }

    public function import(): void
    {
        $this->checkToken();
        $this->checkCreate();

        /** @var ImportModel $model */
        $model   = $this->getModel('Import', 'Administrator');
        $mapping = (array) $this->input->post->get('styles', [], 'array');
        $count   = $model->import($mapping);

        if ($count === false) {
            $this->setRedirect(Route::_('index.php?option=com_hotspots&view=import&layout=mapping', false), (string) $model->getError(), 'error');

            return;
        }

        $this->setRedirect(Route::_('index.php?option=com_hotspots&view=hotspots', false), Text::plural('COM_HOTSPOTS_IMPORT_N_IMPORTED', $count));
    }

    public function cancel(): void
    {
        $this->checkToken();

        $this->getModel('Import', 'Administrator')->clear();
        $this->setRedirect(Route::_('index.php?option=com_hotspots&view=import', false));
    }

    private function checkCreate(): void
    {
        if (!$this->app->getIdentity()->authorise('core.create', 'com_hotspots')) {
            throw new \Joomla\CMS\Access\Exception\NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
