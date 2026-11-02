<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\TileHelper;
use TLWebdesign\Component\Hotspots\Site\Model\TileModel;

\defined('_JEXEC') or die;

/**
 * Marker tiles: transparent 256x256 PNGs with a coloured dot per marker
 * (index.php?option=com_hotspots&task=tiles.create&format=png&x=&y=&z=&cats=&search=).
 */
class TilesController extends BaseController
{
    public function create(): void
    {
        /** @var TileModel $model */
        $model = $this->getModel('Tile', 'Site', ['ignore_request' => false]);
        $file  = TileHelper::cacheFolder() . '/' . $model->getCacheKey() . '.png';

        if (!is_file($file)) {
            $this->render($model->getItems(), $file);
        }

        $this->app->clearHeaders();
        $this->app->allowCache(true);
        $this->app->setHeader('Content-Type', 'image/png', true);
        $this->app->setHeader('Cache-Control', 'public, max-age=3600', true);
        $this->app->sendHeaders();

        readfile($file);

        $this->app->close();
    }

    private function render(array $rows, string $file): void
    {
        $colors = [];

        foreach (MapHelper::getCategories() as $category) {
            $rgb = explode(',', (string) (new Registry($category->params))->get('tile_marker_color', '0,0,0'));

            $colors[(int) $category->id] = array_map('intval', $rgb + [0, 0, 0]);
        }

        $size  = TileHelper::TILE_SIZE;
        $image = imagecreate($size, $size);
        imagecolortransparent($image, imagecolorallocate($image, 0, 0, 255));

        $dots   = [];
        $filled = [];

        foreach ($rows as $row) {
            [$x, $y] = TileHelper::getPixelOffsetInTile((float) $row['lat'], (float) $row['lng'], (int) $this->input->get->getInt('z', 0));
            $catId   = (int) $row['catid'];

            if (isset($filled[$x . ',' . $y])) {
                continue;
            }

            $dots[$catId] ??= $this->dot($colors[$catId] ?? [0, 0, 0]);

            imagecopy($image, $dots[$catId], min($x, $size - 5), min($y, $size - 5), 0, 0, 5, 5);
            $filled[$x . ',' . $y] = true;
        }

        if (!is_dir(\dirname($file))) {
            mkdir(\dirname($file), 0755, true);
            file_put_contents(\dirname($file) . '/index.html', '<!DOCTYPE html><title></title>');
        }

        imagepng($image, $file);
    }

    /**
     * A 5x5 dot in a category colour.
     *
     * @param   int[]  $rgb
     */
    private function dot(array $rgb): \GdImage
    {
        $dot    = imagecreate(5, 5);
        $source = imagecreatefrompng(JPATH_SITE . '/media/com_hotspots/images/utils/dot.png');

        imagecopy($dot, $source, 0, 0, 0, 0, 5, 5);
        imagefilter($dot, IMG_FILTER_COLORIZE, $rgb[0], $rgb[1], $rgb[2]);

        return $dot;
    }
}
