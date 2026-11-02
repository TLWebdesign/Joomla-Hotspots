<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Model\MultimediaModel;

\defined('_JEXEC') or die;

/**
 * Image data of markers for the gallery (Galleria), the map and the list module.
 */
abstract class GalleryHelper
{
    /**
     * Images per marker: ['big' => url, 'title', 'description', 'thumb' => url of $small, 'image' => url of $normal].
     *
     * With an array of ids: [markerId => [image, ...]]; with one id: [image, ...]. False when there are no images.
     * With $json the same as a JSON string.
     *
     * @param   int|int[]  $itemIds
     */
    public static function getData(int|array $itemIds, bool $json = false, string $small = 'small', string $normal = 'large'): array|string|false
    {
        if (\is_array($itemIds) && !$itemIds) {
            return false;
        }

        $model = new MultimediaModel();
        $data  = [];

        foreach ($model->getFilesFromDb($itemIds) as $row) {
            $params = new Registry($row->params);
            $id     = (int) $row->item_id;
            $image  = [
                'big'         => $model->getWebFilePath($row->mangled_filename, $id),
                'title'       => (string) $row->title,
                'description' => (string) $row->description,
            ];

            if ($small !== '' && ($thumb = $params->get('thumbs.' . $small))) {
                $image['thumb'] = $model->getWebFilePath((string) $thumb->name, $id, true);
            }

            if ($normal !== '' && ($thumb = $params->get('thumbs.' . $normal))) {
                $image['image'] = $model->getWebFilePath((string) $thumb->name, $id, true);
            }

            $data[$id][] = $image;
        }

        if (!$data) {
            return false;
        }

        if (!\is_array($itemIds)) {
            $data = $data[(int) $itemIds] ?? [];
        }

        return $json ? json_encode($data) : $data;
    }

    /**
     * Loads Galleria with the Hotspots theme. Used by the layout "gallery.galleria"; or call
     * Galleria.run() yourself.
     */
    public static function loadAssets(): void
    {
        /** @var WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        $wa->useScript('jquery')
            ->useStyle('fontawesome');

        if (!$wa->assetExists('script', 'com_hotspots.galleria')) {
            $wa->registerScript('com_hotspots.galleria', 'media/com_hotspots/vendor/galleria/galleria.min.js', [], ['defer' => true], ['jquery']);
        }

        if (!$wa->assetExists('script', 'com_hotspots.galleria.theme')) {
            $wa->registerScript('com_hotspots.galleria.theme', 'media/com_hotspots/vendor/galleria/themes/compojoom/galleria.compojoom.js', [], ['defer' => true], ['com_hotspots.galleria']);
        }

        if (!$wa->assetExists('style', 'com_hotspots.galleria.theme')) {
            $wa->registerStyle('com_hotspots.galleria.theme', 'media/com_hotspots/vendor/galleria/themes/compojoom/galleria.compojoom.css');
        }

        $wa->useScript('com_hotspots.galleria.theme')->useStyle('com_hotspots.galleria.theme');
    }
}
