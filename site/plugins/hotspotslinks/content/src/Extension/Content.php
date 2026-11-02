<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2012 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Plugin\Hotspotslinks\Content\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

/**
 * Links a marker to a Joomla article ("Read more" goes to the article).
 */
final class Content extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return ['onCreateLink' => 'onCreateLink'];
    }

    /**
     * Arguments: linkId (the article id) and type (the plugin name). Old callers pass [articleId, markerId].
     */
    public function onCreateLink(Event $event): void
    {
        $type = $event->getArgument('type');

        if ($type !== null && $type !== $this->_name) {
            return;
        }

        $id = (int) $event->getArgument('linkId', $event->getArgument(0));

        if ($id <= 0) {
            return;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['catid', 'language']))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $article = $db->setQuery($query)->loadObject();

        if (!$article) {
            return;
        }

        $link = Route::link('site', RouteHelper::getArticleRoute($id, (int) $article->catid, $article->language), false);

        $result   = $event->getArgument('result') ?: [];
        $result[] = $link;
        $event->setArgument('result', $result);
    }
}
