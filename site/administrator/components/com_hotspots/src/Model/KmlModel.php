<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2011 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

\defined('_JEXEC') or die;

/**
 * One KML file. The uploaded file is stored under a random name in media/com_hotspots/kmls.
 */
class KmlModel extends AdminModel
{
    /**
     * Folder of the uploaded KML files, relative to the site root.
     */
    public const FOLDER = 'media/com_hotspots/kmls';

    private const EXTENSIONS = ['kml', 'kmz', 'xml'];

    /**
     * @var  string
     */
    protected $text_prefix = 'COM_HOTSPOTS';

    public function getForm($data = [], $loadData = true): Form|false
    {
        $form = $this->loadForm('com_hotspots.kml', 'kml', ['control' => 'jform', 'load_data' => $loadData]);

        return $form ?: false;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_hotspots.edit.kml.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_hotspots.kml', $data);

        return $data;
    }

    public function save($data)
    {
        $files = Factory::getApplication()->getInput()->files->get('jform', [], 'raw');
        $file  = $files['kml_file'] ?? null;
        $id    = (int) ($data['hotspots_kml_id'] ?? 0);
        $old   = $id ? $this->getItem($id) : null;

        unset($data['kml_file'], $data['original_filename'], $data['mangled_filename'], $data['mime_type']);

        if (\is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = $this->uploadFile($file);

            if ($upload === false) {
                return false;
            }

            $data = array_merge($data, $upload);
        } elseif (!$old || empty($old->mangled_filename)) {
            $this->setError(Text::_('COM_HOTSPOTS_ATTACHMENTS_ERR_NOFILE'));

            return false;
        }

        if (!parent::save($data)) {
            if (isset($upload)) {
                $this->deleteFile($upload['mangled_filename']);
            }

            return false;
        }

        // A new file replaces the old one.
        if (isset($upload) && $old && !empty($old->mangled_filename)) {
            $this->deleteFile($old->mangled_filename);
        }

        return true;
    }

    public function delete(&$pks)
    {
        $pks   = array_map('intval', (array) $pks);
        $files = [];

        foreach ($pks as $pk) {
            $item = $this->getItem($pk);

            if ($item && !empty($item->mangled_filename)) {
                $files[$pk] = $item->mangled_filename;
            }
        }

        if (!parent::delete($pks)) {
            return false;
        }

        // parent::delete() removes the ids it could not delete from $pks.
        foreach ($pks as $pk) {
            if (isset($files[$pk])) {
                $this->deleteFile($files[$pk]);
            }
        }

        return true;
    }

    /**
     * Stores an upload from $_FILES under a random name.
     *
     * @return  array|false  original_filename, mangled_filename and mime_type
     */
    private function uploadFile(array $file): array|false
    {
        $name = basename((string) ($file['name'] ?? ''));

        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            $this->setError(Text::_('COM_HOTSPOTS_ATTACHMENTS_ERR_CANTJFILEUPLOAD'));

            return false;
        }

        if (!\in_array(strtolower(File::getExt($name)), self::EXTENSIONS, true)) {
            $this->setError(Text::_('COM_HOTSPOTS_KML_ERR_FILETYPE'));

            return false;
        }

        // A KML file is XML, a KMZ file is a zip.
        $start = (string) file_get_contents($file['tmp_name'], false, null, 0, 512);

        if (!str_starts_with($start, 'PK') && !preg_match('/^\s*(\xEF\xBB\xBF)?\s*</', $start)) {
            $this->setError(Text::_('COM_HOTSPOTS_KML_ERR_FILETYPE'));

            return false;
        }

        $folder = JPATH_ROOT . '/' . self::FOLDER;

        if (!is_dir($folder)) {
            Folder::create($folder);
        }

        $mangled = hash('sha256', $name . microtime() . random_bytes(16) . Factory::getApplication()->get('secret'));

        if (!File::upload($file['tmp_name'], $folder . '/' . $mangled)) {
            $this->setError(Text::_('COM_HOTSPOTS_ATTACHMENTS_ERR_CANTJFILEUPLOAD'));

            return false;
        }

        return [
            'original_filename' => $name,
            'mangled_filename'  => $mangled,
            'mime_type'         => str_starts_with($start, 'PK') ? 'application/vnd.google-earth.kmz' : 'application/vnd.google-earth.kml+xml',
        ];
    }

    private function deleteFile(string $mangled): void
    {
        $mangled = basename($mangled);
        $path    = JPATH_ROOT . '/' . self::FOLDER . '/' . $mangled;

        if ($mangled !== '' && $mangled[0] !== '.' && is_file($path)) {
            File::delete($path);
        }
    }
}
