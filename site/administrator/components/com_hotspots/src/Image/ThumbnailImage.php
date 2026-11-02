<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Image;

use Joomla\CMS\Image\Image;
use Joomla\Filesystem\Folder;

\defined('_JEXEC') or die;

/**
 * Image with named thumbnails (small, medium, large, ...), turned upright from the EXIF orientation.
 * Joomla's own thumbnail methods number the thumbs; Hotspots stores them by size name.
 */
class ThumbnailImage extends Image
{
    /**
     * Writes one thumbnail per size to $thumbsFolder (default: "thumbs" next to the image) as
     * <name>_<w>x<h>.<ext>.
     *
     * @param   array<string, string>  $sizes  Size name => "WIDTHxHEIGHT".
     *
     * @return  array<string, Image>  Size name => thumbnail, getPath() is the written file.
     */
    public function createNamedThumbs(array $sizes, int $creationMethod = self::SCALE_INSIDE, ?string $thumbsFolder = null): array
    {
        if (!$this->isLoaded()) {
            throw new \LogicException('No valid image was loaded.');
        }

        $thumbsFolder ??= \dirname($this->getPath()) . '/thumbs';

        if (!is_dir($thumbsFolder) && !Folder::create($thumbsFolder)) {
            throw new \InvalidArgumentException('Folder does not exist and cannot be created: ' . $thumbsFolder);
        }

        $properties = self::getImageFileProperties($this->getPath());
        $orientation = $this->exifOrientation();
        $filename   = pathinfo($this->getPath(), PATHINFO_FILENAME);
        $extension  = pathinfo($this->getPath(), PATHINFO_EXTENSION);
        $created    = [];

        foreach ($sizes as $key => $size) {
            $size = explode('x', strtolower(trim((string) $size)));

            if (\count($size) !== 2 || (int) $size[0] <= 0 || (int) $size[1] <= 0) {
                continue;
            }

            [$width, $height] = [(int) $size[0], (int) $size[1]];

            // Never make a thumbnail bigger than the image itself.
            if ($creationMethod === self::SCALE_INSIDE && $this->getWidth() <= $width && $this->getHeight() <= $height) {
                [$width, $height] = [$this->getWidth(), $this->getHeight()];
            }

            $thumb = match ($creationMethod) {
                self::CROP        => $this->crop($width, $height, null, null, true),
                self::CROP_RESIZE => $this->cropResize($width, $height, true),
                default           => $this->resize($width, $height, true, $creationMethod),
            };

            if ($orientation !== 0) {
                $thumb->rotate($orientation, 0, false);
            }

            $file    = $thumbsFolder . '/' . $filename . '_' . $thumb->getWidth() . 'x' . $thumb->getHeight() . '.' . $extension;
            $options = ['quality' => $properties->type === IMAGETYPE_PNG ? 6 : 60];

            if ($thumb->toFile($file, $properties->type, $options)) {
                $thumb->path   = $file;
                $created[$key] = $thumb;
            }
        }

        return $created;
    }

    /**
     * Rotation in degrees that makes the image upright, 0 when unknown.
     */
    private function exifOrientation(): int
    {
        if (!\function_exists('exif_read_data')) {
            return 0;
        }

        $exif = @exif_read_data($this->getPath());

        return match ((int) ($exif['Orientation'] ?? 0)) {
            8       => 90,
            3       => 180,
            6       => -90,
            default => 0,
        };
    }
}
