<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 * Image gallery (Galleria) of one marker:
 * LayoutHelper::render('gallery.galleria', ['images' => GalleryHelper::getData($id)], JPATH_ADMINISTRATOR . '/components/com_hotspots/layouts')
 *
 * @var  array  $displayData  images: list of images from GalleryHelper::getData() (array, JSON string or false)
 */

use Joomla\CMS\Factory;
use TLWebdesign\Component\Hotspots\Administrator\Helper\GalleryHelper;

\defined('_JEXEC') or die;

$images = $displayData['images'] ?? ($displayData['data'] ?? []);

if (\is_string($images)) {
    $images = json_decode($images, true) ?: [];
}

if (!$images) {
    return;
}

static $count = 0;
$id = 'hs-galleria-' . ++$count;

GalleryHelper::loadAssets();

$data = json_encode(array_values((array) $images), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript(
    "document.addEventListener('DOMContentLoaded', function () { Galleria.run('#" . $id . "', { dataSource: " . $data . ", imageCrop: true }); });",
    [],
    [],
    ['com_hotspots.galleria.theme']
);
?>
<div id="<?php echo $id; ?>" class="galleria"></div>
