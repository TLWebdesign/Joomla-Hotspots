<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2012
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Plugin\Hotspotslinks\External\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

/**
 * Links a marker to any URL ("Read more" goes to that URL).
 */
final class External extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onCreateLink' => 'onCreateLink'];
    }

    /**
     * Arguments: linkId (the URL) and type (the plugin name). Old callers pass [url, markerId].
     */
    public function onCreateLink(Event $event): void
    {
        $type = $event->getArgument('type');

        if ($type !== null && $type !== $this->_name) {
            return;
        }

        $url = trim((string) $event->getArgument('linkId', $event->getArgument(0)));

        if ($url === '') {
            return;
        }

        // Frontend users can enter this URL, so allow only http(s), relative and Joomla URLs (no javascript: etc.).
        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url) && !preg_match('#^https?://#i', $url)) {
            return;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        $link = str_starts_with($url, 'index.php') ? Route::link('site', $url, false) : $url;

        $result   = $event->getArgument('result') ?: [];
        $result[] = $link;
        $event->setArgument('result', $result);
    }
}
