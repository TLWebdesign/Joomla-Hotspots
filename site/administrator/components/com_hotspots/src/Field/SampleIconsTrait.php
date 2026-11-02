<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

/**
 * The sample marker icons in media/com_hotspots/images/categories/sample, as a clickable grid.
 */
trait SampleIconsTrait
{
    private function sampleIconsHtml(string $id): string
    {
        $folder = JPATH_ROOT . '/media/com_hotspots/images/categories/sample';
        $base   = Uri::root(true) . '/media/com_hotspots/images/categories/sample/';
        $icons  = [];

        foreach (is_dir($folder) ? scandir($folder) : [] as $file) {
            if (preg_match('/\.(png|gif|jpe?g|svg|webp)$/i', $file) && $file !== 'blank.gif' && !str_contains($file, '_shadow')) {
                $icons[] = $file;
            }
        }

        $html = '<div class="hs-sample-icons d-none border rounded p-2 mt-2" id="' . $id . '_samples" style="max-height:20rem;overflow:auto">';

        foreach ($icons as $icon) {
            $name  = htmlspecialchars(pathinfo($icon, PATHINFO_FILENAME), ENT_QUOTES, 'UTF-8');
            $file  = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
            $html .= '<button type="button" class="btn btn-light btn-sm m-1 hs-sample-icon" data-icon="' . $file . '" title="' . $name . '">'
                . '<img src="' . $base . rawurlencode($icon) . '" alt="' . $name . '" loading="lazy" width="32" height="37" style="object-fit:contain"></button>';
        }

        return $html . '</div>';
    }

    private function useIconPickerScript(): void
    {
        Factory::getApplication()->getDocument()->getWebAssetManager()
            ->registerAndUseScript('com_hotspots.admin-icon-picker', 'com_hotspots/admin/icon-picker.js', [], ['defer' => true], ['core']);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
