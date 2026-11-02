<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Hotspots;

use Joomla\CMS\Document\Feed\FeedImage;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\UserHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

\defined('_JEXEC') or die;

/**
 * The newest markers as RSS or Atom (index.php?option=com_hotspots&view=hotspots&format=feed&type=rss|atom).
 * Replaces the feedcreator feed of Hotspots 6 (task=hotspots.rss now redirects here).
 */
class FeedView extends AbstractView
{
    public function display($tpl = null)
    {
        $app      = Factory::getApplication();
        $settings = SettingsHelper::getAll();

        if (!$settings->get('rss_enable', 0)) {
            throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        $document = $this->getDocument();
        $base     = Uri::base();

        $document->setTitle($app->get('sitename') . ' - ' . Text::_('COM_HOTSPOTS_FEED'));
        $document->setDescription(Text::sprintf('COM_HOTSPOTS_FEED_DESCRIPTION', $base));
        $document->link = Route::_('index.php?option=com_hotspots&view=hotspots');

        $logo = (string) $settings->get('rss_logopath', '');

        if ($logo !== '') {
            $image        = new FeedImage();
            $image->title = $app->get('sitename');
            $image->url   = preg_match('#^https?://#', $logo) ? $logo : Uri::root() . ltrim($logo, '/');
            $image->link  = $base;

            $document->image = $image;
        }

        $markers = $this->getMarkers((int) $settings->get('rss_limit', 50));
        $users   = UserHelper::getUsers(array_map(static fn ($m): int => (int) $m->created_by, $markers));
        $host    = Uri::getInstance()->toString(['scheme', 'host', 'port']);

        foreach ($markers as $marker) {
            $marker = MarkerHelper::prepare($marker);
            $item   = new FeedItem();

            $item->title = (string) $marker->title;
            $item->link  = $marker->link !== '' ? $marker->link : MarkerHelper::markerUrl($marker);

            if (!preg_match('#^https?://#', $item->link)) {
                $item->link = $host . $item->link;
            }

            $description = '';

            $address = $settings->get('show_address', 1) ? MarkerHelper::formattedAddress($marker) : '';

            if ($address !== '') {
                $description = '<p>' . $address . '</p>';
            }

            $item->description = $description . $marker->description_small;
            $item->date        = $marker->created;

            if ($settings->get('show_author', 1)) {
                $user = $users[(int) $marker->created_by] ?? null;

                if (trim((string) $marker->created_by_alias) !== '') {
                    $item->author = $marker->created_by_alias;
                } elseif ($user) {
                    $item->author = $settings->get('use_name', 1) ? $user->name : $user->username;
                }
            }

            $document->addItem($item);
        }
    }

    /**
     * Newest published markers the visitor may see.
     */
    private function getMarkers(int $limit): array
    {
        $categories = MapHelper::getCategoryIds();

        if (!$categories) {
            return [];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->select(['m.*', $db->quoteName('m.id', 'hotspots_id')])
            ->from($db->quoteName('#__hotspots_marker', 'm'))
            ->where($db->quoteName('m.state') . ' = 1')
            ->whereIn($db->quoteName('m.catid'), $categories)
            ->whereIn($db->quoteName('m.access'), MapHelper::viewLevels())
            ->where('(' . $db->quoteName('m.publish_up') . ' IS NULL OR ' . $db->quoteName('m.publish_up') . ' <= :pubup)')
            ->where('(' . $db->quoteName('m.publish_down') . ' IS NULL OR ' . $db->quoteName('m.publish_down') . ' >= :pubdown)')
            ->bind(':pubup', $now)
            ->bind(':pubdown', $now)
            ->order($db->quoteName('m.created') . ' DESC')
            ->setLimit(max(1, min(500, $limit)));

        $app = Factory::getApplication();

        if ($app->getLanguageFilter()) {
            $query->whereIn($db->quoteName('m.language'), [$app->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        return $db->setQuery($query)->loadObjectList();
    }
}
