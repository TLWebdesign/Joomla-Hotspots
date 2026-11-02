<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Plugin\Content\Hotspotscategory\Extension;

use Joomla\CMS\Event\Model\BeforeSaveEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

\defined('_JEXEC') or die;

/**
 * Prepares the parameters of a Hotspots category before it is saved: the marker icon (uploaded or copied
 * from the sample icons) and the tile marker colour, which the tiles need as "r,g,b".
 */
final class Hotspotscategory extends CMSPlugin implements SubscriberInterface
{
    private const SAMPLE_FOLDER = '/media/com_hotspots/images/categories/';

    public static function getSubscribedEvents(): array
    {
        return ['onContentBeforeSave' => 'onContentBeforeSave'];
    }

    public function onContentBeforeSave(BeforeSaveEvent $event): void
    {
        $table = $event->getItem();

        if ($event->getContext() !== 'com_categories.category' || ($table->extension ?? '') !== 'com_hotspots') {
            return;
        }

        $params = new Registry($table->params);
        $icon   = (string) $params->get('icon', '');

        if (str_contains($icon, '[upload]')) {
            $params->set('icon', str_replace('[upload]', '', $icon));
        }

        if (str_contains($icon, '[sample]')) {
            $params->set('icon', $this->copySampleImage(str_replace('[sample]', '', $icon)));
        }

        $color = (string) $params->get('tile_marker_color', '');

        if ($color !== '' && !str_contains($color, ',')) {
            $params->set('tile_marker_color', self::hex2rgb($color));
        }

        $table->params = $params->toString();
    }

    /**
     * Copies a sample icon, so the category gets its own file. Returns the new path or '' on failure.
     */
    private function copySampleImage(string $image): string
    {
        // Only sample images from our own folder, no "../".
        $image = 'sample/' . basename($image);

        if (!\in_array(strtolower(File::getExt($image)), ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'], true)) {
            return '';
        }

        $user    = $this->getApplication()->getIdentity();
        $initial = $user ? StringHelper::substr((string) $user->name, 0, 1) : '';
        $newName = time() . '_' . $initial . '_' . preg_replace('#(sample|_shadow|/)#', '', $image);
        $source  = JPATH_ROOT . self::SAMPLE_FOLDER . $image;
        $target  = JPATH_ROOT . self::SAMPLE_FOLDER . $newName;

        if (is_file($source) && File::copy($source, $target)) {
            // On some servers the copy gets the wrong permissions.
            @chmod($target, 0644);

            return ltrim(self::SAMPLE_FOLDER, '/') . $newName;
        }

        $this->getApplication()->getLanguage()->load('com_hotspots', JPATH_SITE);
        $this->getApplication()->enqueueMessage(Text::sprintf('COM_HOTSPOTS_SAMPLE_IMAGED_COPY_FAILED', $image), 'warning');

        return '';
    }

    /**
     * "#ff8800" or "f80" to "255,136,0".
     */
    private static function hex2rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (\strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return '';
        }

        return implode(',', array_map('hexdec', str_split($hex, 2)));
    }
}
