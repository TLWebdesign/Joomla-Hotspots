<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use TLWebdesign\Component\Hotspots\Administrator\Model\MultimediaModel;

\defined('_JEXEC') or die;

/**
 * Endpoint of the image upload widget: task=multimedia.doIt with the form token.
 * GET lists the files of marker "id", POST uploads to the temporary folder, DELETE (or action=delete)
 * deletes "file" (of marker "id", or a temporary file without id).
 */
class MultimediaController extends BaseController
{
    public function doIt(): void
    {
        if (!Session::checkToken('request')) {
            $this->sendJson(['files' => [['error' => Text::_('JINVALID_TOKEN'), 'name' => '', 'size' => '']]], 403);
        }

        $model  = $this->getMultimediaModel();
        $method = strtoupper((string) $this->input->server->getCmd('REQUEST_METHOD', 'GET'));

        if ($method === 'DELETE' || $this->input->getCmd('action') === 'delete') {
            $file = $this->input->getString('file', '');

            $this->sendJson([$file => $model->delete($file, $this->input->getInt('id', 0))]);
        }

        if ($method === 'POST') {
            $files = $this->input->files->get('files', [], 'raw');
            $file  = \is_array($files) && isset($files[0]) && \is_array($files[0]) ? $files[0] : [];

            $uploaded = $file ? $model->uploadTmp($file) : false;

            if ($uploaded) {
                $this->sendJson(['files' => [$uploaded]]);
            }

            $messages = $this->app->getMessageQueue(true);
            $message  = $messages ? (string) end($messages)['message'] : Text::_('COM_HOTSPOTS_MULTIMEDIA_ERROR_NO_FILE');

            $this->sendJson(['files' => [['error' => $message, 'name' => (string) ($file['name'] ?? ''), 'size' => (int) ($file['size'] ?? 0)]]]);
        }

        $this->sendJson(['files' => $model->getFiles($this->input->getInt('id', 0))]);
    }

    protected function getMultimediaModel(): MultimediaModel
    {
        return $this->factory->createModel('Multimedia', 'Administrator', ['ignore_request' => true]);
    }

    /**
     * Sends JSON and ends the request.
     */
    protected function sendJson(array $data, int $status = 200): never
    {
        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8', true);

        if ($status !== 200) {
            $this->app->setHeader('Status', (string) $status, true);
        }

        $this->app->sendHeaders();
        echo json_encode($data);
        $this->app->close();
    }
}
