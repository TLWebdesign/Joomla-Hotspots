<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

\defined('_JEXEC') or die;

/**
 * Markers for the map (view=jsonv4): the markers in the visible part of the map, filtered by category and
 * search words, a page at a time.
 *
 * Request: cat (ids separated by ";"), level (0 and 1 = no bounding box), ne / sw ("lat,lng"), search,
 * fs (when nothing is found in view: search around c ("lat,lng") and then the whole world), offset, hs-language.
 */
class JsonModel extends BaseDatabaseModel
{
    /**
     * Columns the option hotspots_order may sort on.
     */
    private const ORDER_COLUMNS = ['title', 'created', 'modified', 'id', 'catid', 'town', 'plz', 'country', 'street', 'publish_up'];

    /**
     * Columns searched by the search words (m = marker, c = category).
     */
    private const SEARCH_COLUMNS = ['m.title', 'm.description', 'm.description_small', 'm.plz', 'm.administrative_area_level_1',
        'm.street', 'm.country', 'm.town'];

    private ?array $result = null;

    private int $bindCount = 0;

    protected function populateState()
    {
        $input = Factory::getApplication()->getInput();

        $this->setState('filter.cats', $this->requestedCategories($input->getString('cat', '')));
        $this->setState('filter.search', trim($input->getString('search', '')));
        $this->setState('filter.level', $input->getInt('level', 0));
        $this->setState('filter.ne', $input->getString('ne', ''));
        $this->setState('filter.sw', $input->getString('sw', ''));
        $this->setState('filter.center', $input->getString('c', ''));
        $this->setState('filter.findSomething', $input->getBool('fs', false));
        $this->setState('filter.language', $this->language($input->getString('hs-language', '')));
        $this->setState('list.start', max(0, $input->getInt('offset', 0)));
        $this->setState('list.limit', max(1, (int) SettingsHelper::get('marker_list_length', 20)));
    }

    /**
     * ['hotspots' => rows, 'viewCount' => total, 'newboundaries' => true when the map should move to the results].
     */
    public function getHotspots(): array
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $cats   = $this->getState('filter.cats');
        $search = (string) $this->getState('filter.search');

        if (!$cats) {
            return $this->result = ['hotspots' => [], 'viewCount' => 0];
        }

        $query = $this->baseQuery();
        $this->whereBoundaries($query);

        if ($search !== '') {
            $this->whereSearch($query, $search);
        }

        $result = ['hotspots' => $this->load($query), 'viewCount' => $this->count($query)];

        // Nothing in view: look around the center of the map, then in the whole world.
        if ($search !== '' && $result['viewCount'] === 0 && $this->getState('filter.findSomething')) {
            $world = $this->baseQuery();
            $this->whereSearch($world, $search);

            if ($this->count($world) > 0) {
                $result['newboundaries'] = true;

                $near = $this->radiusQuery($search);

                if ($near) {
                    $result['hotspots']  = $this->load($near);
                    $result['viewCount'] = $this->count($near);
                }

                if (!$result['viewCount']) {
                    $result['hotspots']  = $this->load($world);
                    $result['viewCount'] = $this->count($world);
                }
            }
        }

        return $this->result = $result;
    }

    /**
     * Published markers of the requested categories that the visitor may see, in the visitor's language.
     */
    private function baseQuery(): DatabaseQuery
    {
        $db    = $this->getDatabase();
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->select(['m.*', $db->quoteName('m.id', 'hotspots_id'), $db->quoteName('c.title', 'cat_title')])
            ->from($db->quoteName('#__hotspots_marker', 'm'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('m.catid'))
            ->whereIn($db->quoteName('m.catid'), $this->getState('filter.cats'))
            ->where($db->quoteName('m.state') . ' = 1')
            ->where('(' . $db->quoteName('m.publish_up') . ' IS NULL OR ' . $db->quoteName('m.publish_up') . ' <= :pubup)')
            ->where('(' . $db->quoteName('m.publish_down') . ' IS NULL OR ' . $db->quoteName('m.publish_down') . ' >= :pubdown)')
            ->whereIn($db->quoteName('m.access'), MapHelper::viewLevels())
            ->bind(':pubup', $now)
            ->bind(':pubdown', $now);

        $language = $this->getState('filter.language');

        if ($language !== '') {
            $query->whereIn($db->quoteName('m.language'), [$language, '*'], ParameterType::STRING);
        }

        [$column, $direction] = $this->ordering();
        $query->order($db->quoteName('m.' . $column) . ' ' . $direction);

        return $query;
    }

    private function whereBoundaries(DatabaseQuery $query): void
    {
        if (\in_array((int) $this->getState('filter.level'), [0, 1], true)) {
            return;
        }

        [$neLat, $neLng] = self::point((string) $this->getState('filter.ne'));
        [$swLat, $swLng] = self::point((string) $this->getState('filter.sw'));
        $db              = $this->getDatabase();
        $lat             = $db->quoteName('m.gmlat');
        $lng             = $db->quoteName('m.gmlng');

        $query->where($lat . ' <= ' . $neLat . ' AND ' . $lat . ' >= ' . $swLat);

        // When the view crosses the date line, east is smaller than west.
        if ($neLng > $swLng) {
            $query->where($lng . ' > ' . $swLng . ' AND ' . $lng . ' < ' . $neLng);
        } else {
            $query->where('(' . $lng . ' >= ' . $swLng . ' OR ' . $lng . ' <= ' . $neLng . ')');
        }
    }

    /**
     * As in Hotspots 6: any word may match any column. With a "quoted phrase" the phrase must match, and the
     * rest of the sentence too.
     */
    private function whereSearch(DatabaseQuery $query, string $sentence): void
    {
        if (preg_match('/"([^"]+)"/', $sentence, $match)) {
            $query->where($this->searchCondition($query, [trim($match[1])]));

            $rest = trim(str_replace('"' . $match[1] . '"', '', $sentence));

            if ($rest !== '') {
                $query->where($this->searchCondition($query, [$rest]));
            }

            return;
        }

        $words = array_slice(array_values(array_filter(preg_split('/\s+/', $sentence))), 0, 10);

        if ($words) {
            $query->where($this->searchCondition($query, $words));
        }
    }

    /**
     * "(col LIKE :w1 OR col LIKE :w1 ... OR col LIKE :w2 ...)" with bound values.
     */
    private function searchCondition(DatabaseQuery $query, array $words): string
    {
        $db      = $this->getDatabase();
        $columns = array_merge(self::SEARCH_COLUMNS, ['c.title']);
        $or      = [];

        foreach ($words as $word) {
            $name  = ':search' . (++$this->bindCount);
            $value = '%' . $db->escape($word, true) . '%';
            $query->bind($name, $value);

            foreach ($columns as $column) {
                $or[] = $db->quoteName($column) . ' LIKE ' . $name;
            }

            // bind() keeps a reference: the next word needs a new variable.
            unset($value);
        }

        return '(' . implode(' OR ', $or) . ')';
    }

    /**
     * Search results within about 160 km of the map center (c), nearest first. Null without a center.
     */
    private function radiusQuery(string $search): ?DatabaseQuery
    {
        $center = (string) $this->getState('filter.center');

        if (!str_contains($center, ',')) {
            return null;
        }

        [$lat, $lng] = self::point($center);
        $db          = $this->getDatabase();
        $mLat        = $db->quoteName('m.gmlat');
        $mLng        = $db->quoteName('m.gmlng');
        $query       = $this->baseQuery();

        $this->whereSearch($query, $search);

        $distance = sprintf(
            '3956 * 2 * ASIN(SQRT(POWER(SIN((%1$F - ABS(%3$s)) * PI() / 180 / 2), 2) + COS(%1$F * PI() / 180) * COS(ABS(%3$s) * PI() / 180) * POWER(SIN((%2$F - %4$s) * PI() / 180 / 2), 2)))',
            $lat,
            $lng,
            $mLat,
            $mLng
        );
        $lngRange = 100 / max(0.01, abs(cos(deg2rad($lat)) * 69));
        $latRange = 100 / 69;

        $query->select($distance . ' AS ' . $db->quoteName('distance'))
            ->where(sprintf('%s BETWEEN %F AND %F', $mLng, $lng - $lngRange, $lng + $lngRange))
            ->where(sprintf('%s BETWEEN %F AND %F', $mLat, $lat - $latRange, $lat + $latRange))
            ->clear('order')
            ->order($db->quoteName('distance'));

        return $query;
    }

    private function load(DatabaseQuery $query): array
    {
        $query->setLimit((int) $this->getState('list.limit'), (int) $this->getState('list.start'));

        return $this->getDatabase()->setQuery($query)->loadObjectList();
    }

    private function count(DatabaseQuery $query): int
    {
        $count = clone $query;
        $count->clear('select')->clear('order')->clear('limit')->clear('offset')->select('COUNT(*)');

        return (int) $this->getDatabase()->setQuery($count)->loadResult();
    }

    /**
     * Requested category ids that are published and visible; all visible categories when none are asked.
     *
     * @return  int[]
     */
    private function requestedCategories(string $cat): array
    {
        $visible = MapHelper::getCategoryIds();
        $asked   = array_filter(array_map('intval', explode(';', $cat)), static fn (int $id): bool => $id > 0);

        if (trim($cat) === '') {
            return $visible;
        }

        return array_values(array_intersect($asked, $visible));
    }

    private function language(string $requested): string
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site') || !$app->getLanguageFilter()) {
            return '';
        }

        return preg_match('/^[a-z]{2,3}-[A-Z]{2}$/', $requested) ? $requested : $app->getLanguage()->getTag();
    }

    /**
     * [column, direction] from the option hotspots_order ("title ASC").
     */
    private function ordering(): array
    {
        $parts     = preg_split('/\s+/', trim((string) SettingsHelper::get('hotspots_order', 'title ASC')));
        $column    = \in_array($parts[0] ?? '', self::ORDER_COLUMNS, true) ? $parts[0] : 'title';
        $direction = strtoupper($parts[1] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        return [$column, $direction];
    }

    /**
     * "lat,lng" as two floats.
     */
    private static function point(string $value): array
    {
        $parts = explode(',', $value) + ['', ''];

        return [(float) $parts[0], (float) $parts[1]];
    }
}
