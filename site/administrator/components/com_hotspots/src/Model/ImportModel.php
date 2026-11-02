<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2014
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

\defined('_JEXEC') or die;

/**
 * Imports the placemarks (points) of KML and KMZ files as markers.
 *
 * Step 1 stores the uploads in Joomla's tmp folder and remembers them in the session,
 * step 2 maps the KML styles to categories and creates the markers.
 */
class ImportModel extends BaseDatabaseModel
{
    private const SESSION_KEY = 'com_hotspots.import.files';

    /**
     * Key for placemarks without a style.
     */
    public const DEFAULT_STYLE = '*';

    /**
     * Stores uploaded files from $_FILES (multiple). Returns the number of stored files.
     */
    public function storeUploads(array $files): int
    {
        $stored = $this->getFiles();
        $folder = $this->getFolder();

        foreach ($files as $file) {
            $name = basename((string) ($file['name'] ?? ''));

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
                $this->setError(Text::sprintf('COM_HOTSPOTS_IMPORT_ERR_UPLOAD', $name));

                continue;
            }

            if (!\in_array(strtolower(File::getExt($name)), ['kml', 'kmz'], true)) {
                $this->setError(Text::sprintf('COM_HOTSPOTS_IMPORT_ERR_FILETYPE', $name));

                continue;
            }

            $target = $folder . '/' . bin2hex(random_bytes(16));

            if (!File::upload($file['tmp_name'], $target) || $this->loadKml($target) === null) {
                if (is_file($target)) {
                    File::delete($target);
                }

                $this->setError(Text::sprintf('COM_HOTSPOTS_IMPORT_ERR_NOT_KML', $name));

                continue;
            }

            $stored[basename($target)] = $name;
        }

        $this->setFiles($stored);

        return \count($stored);
    }

    /**
     * Uploaded files waiting for import: stored name => original name.
     */
    public function getFiles(): array
    {
        $files  = (array) Factory::getApplication()->getSession()->get(self::SESSION_KEY, []);
        $folder = $this->getFolder();

        return array_filter($files, static fn ($stored) => is_file($folder . '/' . basename((string) $stored)), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Deletes the uploaded files and forgets them.
     */
    public function clear(): void
    {
        $folder = $this->getFolder();

        foreach (array_keys($this->getFiles()) as $stored) {
            File::delete($folder . '/' . basename($stored));
        }

        $this->setFiles([]);
    }

    /**
     * The styles used by the placemarks: id => icon URL ('' when the style has no icon).
     * The DEFAULT_STYLE entry is for placemarks without a (known) style.
     */
    public function getStyles(): array
    {
        $styles = [self::DEFAULT_STYLE => ''];

        foreach ($this->loadAll() as $kml) {
            $styleMaps = [];

            foreach ($kml->xpath('//*[local-name()="StyleMap"]') ?: [] as $styleMap) {
                foreach ($styleMap->children() as $pair) {
                    if ($pair->getName() === 'Pair' && isset($pair->styleUrl)) {
                        $styleMaps[ltrim((string) $pair->styleUrl, '#')] = (string) $styleMap['id'];
                    }
                }
            }

            foreach ($kml->xpath('//*[local-name()="Style"]') ?: [] as $style) {
                $id = (string) $style['id'];

                if ($id === '' || !isset($style->IconStyle)) {
                    continue;
                }

                $styles[$styleMaps[$id] ?? $id] = (string) ($style->IconStyle->Icon->href ?? '');
            }
        }

        return $styles;
    }

    /**
     * Creates a marker for every placemark with a point.
     *
     * @param   array  $mapping  style id => category id; DEFAULT_STYLE is used for unmapped placemarks.
     *
     * @return  int|false  number of imported markers
     */
    public function import(array $mapping): int|false
    {
        $mapping = array_map('intval', $mapping);
        $default = $mapping[self::DEFAULT_STYLE] ?? 0;

        if (!$default || !$this->categoriesExist($mapping)) {
            $this->setError(Text::_('COM_HOTSPOTS_IMPORT_ERR_CATEGORY'));

            return false;
        }

        $db    = $this->getDatabase();
        $user  = $this->getCurrentUser();
        $now   = Factory::getDate()->toSql();
        $count = 0;

        $db->transactionStart();

        try {
            foreach ($this->loadAll() as $kml) {
                foreach ($kml->xpath('//*[local-name()="Placemark"]') ?: [] as $placemark) {
                    $point = $placemark->xpath('.//*[local-name()="Point"]/*[local-name()="coordinates"]');

                    if (!$point) {
                        continue;
                    }

                    $coordinates = array_map('trim', explode(',', trim((string) $point[0])));

                    if (\count($coordinates) < 2 || !is_numeric($coordinates[0]) || !is_numeric($coordinates[1])) {
                        continue;
                    }

                    $style = ltrim((string) ($placemark->styleUrl ?? ''), '#');
                    $title = trim((string) ($placemark->name ?? '')) ?: Text::_('COM_HOTSPOTS_IMPORT_NO_TITLE');

                    $marker = (object) [
                        'title'                       => $title,
                        'alias'                       => random_int(0, 10000000) . '-' . OutputFilter::stringURLSafe($title),
                        'catid'                       => $mapping[$style] ?? $default,
                        'description'                 => '',
                        'description_small'           => trim((string) ($placemark->description ?? '')),
                        'street'                      => '',
                        'plz'                         => '',
                        'town'                        => '',
                        'country'                     => '',
                        'administrative_area_level_1' => '',
                        'created'                     => $now,
                        'access'                      => 1,
                        'asset_id'                    => 0,
                        'language'                    => '*',
                        'state'                       => 1,
                        'gmlat'                       => (float) $coordinates[1],
                        'gmlng'                       => (float) $coordinates[0],
                        'created_by'                  => (int) $user->id,
                        'modified_by'                 => (int) $user->id,
                        'created_by_alias'            => '',
                        'params'                      => '',
                        'customfields'                => '',
                        'import_table'                => 'kml',
                    ];

                    $db->insertObject('#__hotspots_marker', $marker);
                    $count++;
                }
            }

            $db->transactionCommit();
        } catch (\RuntimeException $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }

        $this->clear();

        return $count;
    }

    private function categoriesExist(array $mapping): bool
    {
        $ids = array_values(array_unique(array_filter($mapping)));

        if (!$ids) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('extension') . ' = ' . $db->quote('com_hotspots'))
            ->whereIn($db->quoteName('id'), $ids);

        return (int) $db->setQuery($query)->loadResult() === \count($ids);
    }

    /**
     * @return  \SimpleXMLElement[]
     */
    private function loadAll(): array
    {
        $folder = $this->getFolder();
        $kmls   = [];

        foreach (array_keys($this->getFiles()) as $stored) {
            $kml = $this->loadKml($folder . '/' . basename($stored));

            if ($kml !== null) {
                $kmls[] = $kml;
            }
        }

        return $kmls;
    }

    /**
     * Reads a KML file, or the doc.kml (or first .kml) inside a KMZ file.
     */
    private function loadKml(string $path): ?\SimpleXMLElement
    {
        $content = (string) file_get_contents($path);

        if (str_starts_with($content, 'PK')) {
            $zip = new \ZipArchive();

            if ($zip->open($path) !== true) {
                return null;
            }

            $content = $zip->getFromName('doc.kml');

            for ($i = 0; $content === false && $i < $zip->numFiles; $i++) {
                if (strtolower(File::getExt((string) $zip->getNameIndex($i))) === 'kml') {
                    $content = $zip->getFromIndex($i);
                }
            }

            $zip->close();

            if ($content === false) {
                return null;
            }
        }

        $previous = libxml_use_internal_errors(true);
        // No LIBXML_NOENT / LIBXML_DTDLOAD: external entities are not loaded.
        $xml = simplexml_load_string($content, \SimpleXMLElement::class, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return ($xml !== false && strtolower($xml->getName()) === 'kml') ? $xml : null;
    }

    private function getFolder(): string
    {
        $folder = rtrim((string) Factory::getApplication()->get('tmp_path', JPATH_ROOT . '/tmp'), '/\\') . '/com_hotspots_import';

        if (!is_dir($folder)) {
            Folder::create($folder);
        }

        return $folder;
    }

    private function setFiles(array $files): void
    {
        Factory::getApplication()->getSession()->set(self::SESSION_KEY, $files);
    }
}
