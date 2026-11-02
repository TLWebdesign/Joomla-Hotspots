<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Module\HotspotsList\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;

\defined('_JEXEC') or die;

/**
 * The latest markers the visitor may see.
 */
class HotspotsListHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Published markers (state, publish dates, published category) of the visitor's access levels and language,
     * with link (MarkerHelper::link) and author name.
     *
     * @return  object[]
     */
    public function getMarkers(Registry $params, SiteApplication $app): array
    {
        $db     = $this->getDatabase();
        $user   = $app->getIdentity();
        $levels = ArrayHelper::toInteger($user ? $user->getAuthorisedViewLevels() : [1]);
        $now    = Factory::getDate()->toSql();
        $limit  = max(1, (int) $params->get('limit', 10));
        $order  = $params->get('ordering', 'created') === 'title' ? 'a.title' : 'a.created';
        $dir    = strtolower((string) $params->get('direction', 'desc')) === 'asc' ? 'ASC' : 'DESC';

        $query = $db->getQuery(true)
            ->select([
                'a.id', 'a.catid', 'a.title', 'a.alias', 'a.description_small', 'a.street', 'a.plz', 'a.town',
                'a.administrative_area_level_1', 'a.country', 'a.created', 'a.created_by', 'a.created_by_alias', 'a.params',
                $db->quoteName('a.id', 'hotspots_id'),
                $db->quoteName('u.name', 'user_name'),
            ])
            ->from($db->quoteName('#__hotspots_marker', 'a'))
            ->join('INNER', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->where($db->quoteName('c.published') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $levels)
            ->whereIn($db->quoteName('c.access'), $levels)
            ->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :now1)')
            ->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= :now2)')
            ->bind(':now1', $now)
            ->bind(':now2', $now)
            ->order($db->quoteName($order) . ' ' . $dir)
            ->setLimit($limit);

        if ($catids = array_filter(ArrayHelper::toInteger((array) $params->get('catid', [])))) {
            $query->whereIn($db->quoteName('a.catid'), $catids);
        }

        if ($app->getLanguageFilter()) {
            $query->whereIn($db->quoteName('a.language'), [$app->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        $markers = $db->setQuery($query)->loadObjectList() ?: [];

        foreach ($markers as $marker) {
            $marker->params = new Registry($marker->params);
            $marker->link   = MarkerHelper::link($marker);
            $marker->author = (string) ($marker->created_by_alias ?: $marker->user_name);
        }

        return $markers;
    }
}
