<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Image\ThumbnailImage;
use TLWebdesign\Component\Hotspots\Administrator\Table\MultimediaTable;

\defined('_JEXEC') or die;

/**
 * Images of markers. Uploads go to a temporary folder first (the marker may not exist yet) and are
 * moved to images/com_hotspots.hotspot/<marker id>/ with thumbnails when the marker is saved.
 */
class MultimediaModel extends BaseDatabaseModel
{
    public const TYPE_ALIAS = 'com_hotspots.hotspot';

    private const COMPONENT = 'com_hotspots';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * User state that holds the form data of a marker that failed validation (temp files are listed there).
     */
    private const FORM_CONTEXT = 'com_hotspots.edit.hotspot.data';

    private const FIELD_NAME = 'picture';

    private string $deleteUrl;

    public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->deleteUrl = $config['deleteUrl']
            ?? 'index.php?option=' . self::COMPONENT . '&task=multimedia.doIt&action=delete&' . Session::getFormToken() . '=1';
    }

    /**
     * Stores an uploaded file ($_FILES entry) in the temporary folder.
     *
     * @return  array|false  File info for the upload widget, false with a message in the queue on failure.
     */
    public function uploadTmp(array $file): array|false
    {
        $app = Factory::getApplication();

        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $postMaxSize   = self::toBytes((string) ini_get('post_max_size'));
        $memoryLimit   = self::toBytes((string) ini_get('memory_limit'));

        if (($postMaxSize > 0 && $contentLength > $postMaxSize) || ($memoryLimit > 0 && $contentLength > $memoryLimit)) {
            $app->enqueueMessage(Text::_('COM_HOTSPOTS_MULTIMEDIA_ERROR_UPLOAD_TOO_LARGE'), 'error');

            return false;
        }

        if (empty($file['name']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $app->enqueueMessage(Text::_('COM_HOTSPOTS_MULTIMEDIA_ERROR_NO_FILE'), 'error');

            return false;
        }

        if (!$this->user()->authorise('core.multimedia.create', self::COMPONENT)) {
            $app->enqueueMessage(Text::_('COM_HOTSPOTS_MULTIMEDIA_ERROR_NO_UPLOAD_PERMISSION'), 'error');

            return false;
        }

        // Spaces and accented characters in names break the upload.
        $name = str_replace(' ', '_', (string) $file['name']);
        $name = preg_replace('/&([a-z])[a-z]+;/i', '$1', htmlentities($name, ENT_QUOTES, 'UTF-8'));
        $name = File::makeSafe(basename($name));

        $file['name'] = $name;

        if (!$this->canUpload($file)) {
            return false;
        }

        // A random name, so uploads cannot be guessed or overwrite each other.
        $mangledName = bin2hex(random_bytes(20)) . '_' . $name;
        $filePath    = $this->getFilePath($mangledName);

        if (is_file($filePath)) {
            $app->enqueueMessage(Text::_('COM_HOTSPOTS_MULTIMEDIA_ERROR_NAME_CLASH'), 'error');

            return false;
        }

        if (!is_dir(\dirname($filePath))) {
            Folder::create(\dirname($filePath));
        }

        if (!File::upload($file['tmp_name'], $filePath)) {
            $app->enqueueMessage(Text::_('COM_HOTSPOTS_MULTIMEDIA_ERROR_CANT_UPLOAD'), 'error');

            return false;
        }

        $mime = (string) (ThumbnailImage::getImageFileProperties($filePath)->mime ?? 'application/octet-stream');

        return $this->fileArray(
            $mangledName,
            $this->previewDataUri($filePath, $mime),
            (int) $file['size'],
            $mime,
            '',
            $this->deleteUrl . '&file=' . rawurlencode($mangledName),
            'delete',
            File::stripExt($name)
        );
    }

    /**
     * Moves new temporary files to the marker and saves them; updates title, description and thumbnails
     * of the files the marker already has.
     *
     * @param   string[]  $files  Mangled file names (the "picture" form values).
     * @param   array     $meta   Mangled file name => ['title' => ..., 'description' => ...].
     */
    public function uploadPermanent(int $itemId, array $files, array $meta = [], string $tmpPath = ''): void
    {
        $meta    = self::cleanMeta($meta);
        $dbFiles = $this->getFilesFromDb($itemId, 'mangled_filename');

        $files = array_values(array_filter($files, static fn ($file) => !isset($dbFiles[(string) $file])));
        $moved = $this->permanentlyMoveFiles($itemId, $files, $tmpPath);

        foreach ($moved as $key => $value) {
            if (!empty($meta[$key])) {
                $moved[$key]['meta'] = $meta[$key];
            }
        }

        $this->saveInDb($itemId, $moved);
        $this->updateExistingFiles($itemId, $dbFiles, $meta);
    }

    /**
     * Regenerates the thumbnails of existing files and updates their title and description.
     *
     * @param   object[]  $files  Rows from getFilesFromDb().
     */
    public function updateExistingFiles(int $itemId, array $files, array $meta = []): void
    {
        $meta  = self::cleanMeta($meta);
        $sizes = $this->getSizes();

        foreach ($files as $file) {
            $filePath = $this->getFilePath($file->mangled_filename, $itemId);

            if (is_file($filePath)) {
                $image  = new ThumbnailImage($filePath);
                $params = ['thumbs' => []];

                foreach ($image->createNamedThumbs($sizes) as $key => $thumb) {
                    $params['thumbs'][$key] = ['name' => basename($thumb->getPath())];
                    $thumb->destroy();
                }

                $file->mime_type = (string) ThumbnailImage::getImageFileProperties($filePath)->mime;
                $file->params    = (new Registry($params))->toString();
                $image->destroy();
            }

            if (isset($meta[$file->mangled_filename])) {
                $file->title       = $meta[$file->mangled_filename]['title'];
                $file->description = $meta[$file->mangled_filename]['description'];
            }

            $table = $this->getMultimediaTable();
            $table->bind((array) $file);
            $table->check() && $table->store();
        }
    }

    /**
     * Moves temporary files to the marker's folder and creates the thumbnails.
     *
     * @return  array  File name => ['status' => bool, 'thumbs' => Image[], 'properties' => object].
     */
    public function permanentlyMoveFiles(int $itemId, array $files, string $tmpPath = ''): array
    {
        $status     = [];
        $destFolder = $this->getFilePath('', $itemId);

        if (!is_dir($destFolder)) {
            Folder::create($destFolder);
        }

        foreach ($files as $file) {
            // File names come from the request: plain names only, otherwise "../configuration.php" gets moved.
            $file = (string) $file;

            if (!self::isPlainName($file)) {
                continue;
            }

            $tmpLocation = $tmpPath !== ''
                ? Path::clean(rtrim($tmpPath, '\\/') . '/' . $file)
                : $this->getFilePath($file);

            if (!is_file($tmpLocation) || !self::isImageFile($tmpLocation, $file)) {
                continue;
            }

            $newLocation = Path::clean($destFolder . '/' . $file);

            $status[$file] = ['status' => File::move($tmpLocation, $newLocation), 'thumbs' => [], 'properties' => null];

            if ($status[$file]['status']) {
                $image = new ThumbnailImage($newLocation);

                $status[$file]['thumbs']     = $image->createNamedThumbs($this->getSizes());
                $status[$file]['properties'] = ThumbnailImage::getImageFileProperties($newLocation);
                $image->destroy();
            }
        }

        return $status;
    }

    /**
     * @param   int|int[]  $ids  Marker ids.
     * @param   string     $key  Column to key the result by ('' for a list).
     *
     * @return  object[]
     */
    public function getFilesFromDb(int|array $ids, string $key = ''): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids))));

        if (!$ids) {
            return [];
        }

        $db        = $this->getDatabase();
        $typeAlias = self::TYPE_ALIAS;
        $query     = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__hotspots_multimedia'))
            ->whereIn($db->quoteName('item_id'), $ids)
            ->where($db->quoteName('type_alias') . ' = :alias')
            ->order($db->quoteName('id') . ' ASC')
            ->bind(':alias', $typeAlias);

        return $db->setQuery($query)->loadObjectList($key) ?: [];
    }

    /**
     * Saves moved files (the result of permanentlyMoveFiles, optionally with 'meta') in the database.
     */
    public function saveInDb(int $itemId, array $files): void
    {
        $user = $this->user();
        $now  = Factory::getDate()->toSql();

        foreach ($files as $name => $value) {
            if (empty($value['status'])) {
                continue;
            }

            $params = ['thumbs' => []];

            foreach ($value['thumbs'] ?? [] as $key => $thumb) {
                $params['thumbs'][$key] = ['name' => basename($thumb->getPath())];
            }

            $table = $this->getMultimediaTable();
            $table->bind([
                'item_id'          => $itemId,
                'title'            => $value['meta']['title'] ?? File::stripExt(preg_replace('/^[0-9a-f]{32,64}_/', '', (string) $name)),
                'description'      => $value['meta']['description'] ?? '',
                'type_alias'       => self::TYPE_ALIAS,
                'mangled_filename' => (string) $name,
                'mime_type'        => (string) ($value['properties']->mime ?? ''),
                'origin'           => 'web',
                'created_on'       => $now,
                'created_by'       => (int) $user->id,
                'enabled'          => 1,
                'params'           => (new Registry($params))->toString(),
            ]);

            $table->check() && $table->store();
        }
    }

    /**
     * Deletes a file. With $id a saved file of that marker (with its thumbnails and database row), without
     * $id a temporary upload.
     */
    public function delete(string $file, int $id = 0): bool
    {
        // The name comes from the request: plain names only, otherwise "../" deletes any file on the server.
        if (!self::isPlainName($file)) {
            return false;
        }

        $user = $this->user();

        if (!$id) {
            // Temporary uploads: only users who may upload, and only names like uploadTmp() makes them.
            if (!$user->authorise('core.multimedia.create', self::COMPONENT) || !preg_match('/^[0-9a-f]{32,64}_/', $file)) {
                return false;
            }

            $path = $this->getFilePath($file);

            return is_file($path) && File::delete($path);
        }

        $canDeleteAll = $user->authorise('core.multimedia.delete', self::COMPONENT);
        $canDeleteOwn = $user->authorise('core.multimedia.delete.own', self::COMPONENT);

        if (!$canDeleteAll && !$canDeleteOwn) {
            return false;
        }

        $db        = $this->getDatabase();
        $typeAlias = self::TYPE_ALIAS;
        $query     = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__hotspots_multimedia'))
            ->where($db->quoteName('mangled_filename') . ' = :file')
            ->where($db->quoteName('item_id') . ' = :id')
            ->where($db->quoteName('type_alias') . ' = :alias')
            ->bind(':file', $file)
            ->bind(':id', $id, ParameterType::INTEGER)
            ->bind(':alias', $typeAlias);

        $image = $db->setQuery($query)->loadObject();

        if (!$image) {
            return false;
        }

        if (!$canDeleteAll && (int) $image->created_by !== (int) $user->id) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_HOTSPOTS_MULTIMEDIA_ERROR_NOT_AUTHORISED_TO_DELETE'), 'error');

            return false;
        }

        foreach ((array) (new Registry($image->params))->get('thumbs', []) as $thumb) {
            $thumbName = basename((string) ($thumb->name ?? ''));
            $thumbPath = $this->getFilePath($thumbName, $id, true);

            if ($thumbName !== '' && is_file($thumbPath)) {
                File::delete($thumbPath);
            }
        }

        $rowId = (int) $image->id;
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__hotspots_multimedia'))
            ->where($db->quoteName('id') . ' = :rowid')
            ->bind(':rowid', $rowId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        $path = $this->getFilePath($file, $id);

        return !is_file($path) || File::delete($path);
    }

    /**
     * Deletes all files of a marker (when the marker is deleted).
     */
    public function deleteAll(int $itemId): void
    {
        if ($itemId <= 0) {
            return;
        }

        $db        = $this->getDatabase();
        $typeAlias = self::TYPE_ALIAS;
        $query     = $db->getQuery(true)
            ->delete($db->quoteName('#__hotspots_multimedia'))
            ->where($db->quoteName('item_id') . ' = :id')
            ->where($db->quoteName('type_alias') . ' = :alias')
            ->bind(':id', $itemId, ParameterType::INTEGER)
            ->bind(':alias', $typeAlias);
        $db->setQuery($query)->execute();

        $folder = $this->getFilePath('', $itemId);

        if (is_dir($folder)) {
            Folder::delete($folder);
        }
    }

    /**
     * Path on disk. Without $id the temporary upload folder.
     */
    public function getFilePath(string $file = '', int $id = 0, bool $isThumb = false): string
    {
        $params = ComponentHelper::getParams(self::COMPONENT);

        if ($id) {
            $path = JPATH_ROOT . '/' . trim((string) $params->get('image_path', 'images'), '/') . '/' . self::TYPE_ALIAS . '/' . $id;

            if ($isThumb) {
                $path .= '/thumbs';
            }
        } else {
            $path = JPATH_ROOT . '/' . trim((string) $params->get('tmp_file_path', 'cache/com_hotspots.multimedia'), '/') . '/uploads';
        }

        return Path::clean($path . '/' . $file);
    }

    /**
     * Public URL of a saved file or thumbnail.
     */
    public function getWebFilePath(string $file, int $id, bool $isThumb = false): string
    {
        $params = ComponentHelper::getParams(self::COMPONENT);
        $path   = Uri::root() . trim((string) $params->get('image_path', 'images'), '/') . '/' . self::TYPE_ALIAS . '/' . $id;

        return $path . ($isThumb ? '/thumbs' : '') . '/' . rawurlencode($file);
    }

    /**
     * Files for the upload widget: the saved files of marker $id, or (no id) the temporary files of a
     * form that failed validation.
     */
    public function getFiles(int $id = 0): array
    {
        $files = [];

        if ($id) {
            foreach ($this->getFilesFromDb($id) as $file) {
                $thumb = (new Registry($file->params))->get('thumbs.small');

                $files[] = $this->fileArray(
                    $file->mangled_filename,
                    isset($thumb->name) ? $this->getWebFilePath($thumb->name, $id, true) : '',
                    (int) @filesize($this->getFilePath($file->mangled_filename, $id)),
                    $file->mime_type,
                    $this->getWebFilePath($file->mangled_filename, $id),
                    $this->deleteUrl . '&file=' . rawurlencode($file->mangled_filename) . '&id=' . $id,
                    'delete',
                    (string) $file->title,
                    (string) $file->description
                );
            }

            return $files;
        }

        $state = (array) Factory::getApplication()->getUserState(self::FORM_CONTEXT);
        $meta  = self::cleanMeta((array) ($state[self::FIELD_NAME . '_data'] ?? []));

        foreach ((array) ($state[self::FIELD_NAME] ?? []) as $file) {
            $file = (string) $file;
            $path = $this->getFilePath($file);

            if (!self::isPlainName($file) || !is_file($path)) {
                continue;
            }

            $mime = (string) (ThumbnailImage::getImageFileProperties($path)->mime ?? '');

            $files[] = $this->fileArray(
                $file,
                $this->previewDataUri($path, $mime),
                (int) filesize($path),
                $mime,
                '',
                $this->deleteUrl . '&file=' . rawurlencode($file),
                'delete',
                $meta[$file]['title'] ?? '',
                $meta[$file]['description'] ?? ''
            );
        }

        return $files;
    }

    /**
     * Thumbnail sizes from the options: name => "WxH" (small, medium, large, original).
     */
    public function getSizes(): array
    {
        $sizes = (array) ComponentHelper::getParams(self::COMPONENT)->get('thumbs', []);

        return array_filter(array_map('strval', $sizes), static fn ($size) => preg_match('/^\d+x\d+$/i', trim($size)));
    }

    /**
     * "10M" / "512K" / "1G" to bytes; plain numbers stay as they are, -1 stays -1.
     */
    public static function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g'     => $number * 1073741824,
            'm'     => $number * 1048576,
            'k'     => $number * 1024,
            default => $number,
        };
    }

    /**
     * The user set by the MVC factory, else the logged-in user (the model is also created with new).
     */
    private function user(): User
    {
        return $this->currentUser ?? Factory::getApplication()->getIdentity() ?? new User();
    }

    private function getMultimediaTable(): MultimediaTable
    {
        return new MultimediaTable($this->getDatabase());
    }

    /**
     * Checks of Joomla's media manager plus "images only".
     */
    private function canUpload(array $file): bool
    {
        $app    = Factory::getApplication();
        $params = ComponentHelper::getParams(self::COMPONENT);
        $name   = (string) $file['name'];
        $parts  = explode('.', strtolower($name));

        if ($name === '' || $name[0] === '.' || \count($parts) < 2) {
            $app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'error');

            return false;
        }

        $extension = array_pop($parts);
        $allowed   = array_map('strtolower', array_map('trim', explode(',', (string) $params->get('upload_extensions', 'gif,jpg,jpeg,png'))));

        // Images only (this is the marker gallery); no executable extension anywhere in the name.
        $executable = ['php', 'phtml', 'phar', 'pht', 'php3', 'php4', 'php5', 'php7', 'php8', 'js', 'exe', 'html', 'htm', 'svg', 'shtml', 'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py', 'sh'];

        if (!\in_array($extension, self::IMAGE_EXTENSIONS, true) || !\in_array($extension, $allowed, true) || array_intersect($parts, $executable)) {
            $app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETYPE'), 'error');

            return false;
        }

        $maxSize = (int) ((float) $params->get('upload_maxsize', 10) * 1048576);

        if ($maxSize > 0 && (int) $file['size'] > $maxSize) {
            $app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNFILETOOLARGE'), 'error');

            return false;
        }

        if (!self::isImageFile($file['tmp_name'], $name)) {
            $app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNINVALID_IMG'), 'error');

            return false;
        }

        // Same check as the media manager: no HTML in the first bytes (IE content sniffing).
        $head = (string) file_get_contents($file['tmp_name'], false, null, 0, 256);

        if (preg_match('#<(script|html|body|head|iframe|object|embed|svg|\?php)[\s>]#i', $head)) {
            $app->enqueueMessage(Text::_('JLIB_MEDIA_ERROR_WARNIEXSS'), 'error');

            return false;
        }

        return true;
    }

    private static function isPlainName(string $file): bool
    {
        return $file !== '' && $file === basename($file) && $file[0] !== '.' && !str_contains($file, '..')
            && !str_contains($file, '/') && !str_contains($file, '\\');
    }

    /**
     * True for a real image with an image extension.
     */
    private static function isImageFile(string $path, string $name): bool
    {
        return \in_array(strtolower(File::getExt($name)), self::IMAGE_EXTENSIONS, true) && @getimagesize($path) !== false;
    }

    /**
     * Title and description are shown on the site: plain text only.
     */
    private static function cleanMeta(array $meta): array
    {
        $filter = InputFilter::getInstance();
        $clean  = [];

        foreach ($meta as $file => $value) {
            $value        = \is_array($value) ? $value : [];
            $clean[$file] = [
                'title'       => trim((string) $filter->clean((string) ($value['title'] ?? ''), 'string')),
                'description' => trim((string) $filter->clean((string) ($value['description'] ?? ''), 'string')),
            ];
        }

        return $clean;
    }

    /**
     * Small preview of a temporary file as a data: URI (the temp folder is not meant to be linked to).
     */
    private function previewDataUri(string $path, string $mime): string
    {
        $sizes = $this->getSizes();
        $tmp   = \dirname($path) . '/preview';

        try {
            $image  = new ThumbnailImage($path);
            $thumbs = $image->createNamedThumbs(['small' => $sizes['small'] ?? '50x50'], ThumbnailImage::SCALE_INSIDE, $tmp);
            $image->destroy();
        } catch (\Throwable) {
            return '';
        }

        if (empty($thumbs['small'])) {
            return '';
        }

        $thumbPath = $thumbs['small']->getPath();
        $data      = base64_encode((string) file_get_contents($thumbPath));
        File::delete($thumbPath);

        return 'data:' . $mime . ';base64,' . $data;
    }

    private function fileArray(
        string $fileName,
        string $thumbnailUrl,
        int $size,
        string $type,
        string $url,
        string $deleteUrl,
        string $deleteType = 'delete',
        string $title = '',
        string $description = ''
    ): array {
        return [
            'name'         => $fileName,
            'title'        => $title,
            'description'  => $description,
            'thumbnailUrl' => $thumbnailUrl,
            'size'         => $size,
            'type'         => $type,
            'deleteType'   => $deleteType,
            'url'          => $url,
            'deleteUrl'    => $deleteUrl,
        ];
    }
}
