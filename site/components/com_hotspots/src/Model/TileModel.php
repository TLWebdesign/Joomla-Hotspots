<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\TileHelper;

\defined('_JEXEC') or die;

/**
 * Markers inside one map tile (x, y, z), for the marker tiles of large maps (option custom_tiles).
 *
 * Request: x, y, z, cats (ids separated by ";"), search, hs-language.
 */
class TileModel extends BaseDatabaseModel
{
    protected function populateState()
    {
        $input = Factory::getApplication()->getInput();
        $cats  = $input->get->getString('cats', '');
        $asked = array_filter(array_map('intval', explode(';', $cats)));

        $this->setState('tile.x', $input->get->getInt('x', 0));
        $this->setState('tile.y', $input->get->getInt('y', 0));
        $this->setState('tile.z', max(0, min(22, $input->get->getInt('z', 0))));
        $this->setState('filter.cats', trim($cats) === '' ? MapHelper::getCategoryIds() : array_values(array_intersect($asked, MapHelper::getCategoryIds())));
        $this->setState('filter.search', trim($input->get->getString('search', '')));
        $this->setState('filter.language', (string) $input->get->getString('hs-language', ''));
    }

    /**
     * Cache key of a tile: everything that changes its content.
     */
    public function getCacheKey(): string
    {
        return implode('_', [(int) $this->getState('tile.x'), (int) $this->getState('tile.y'), (int) $this->getState('tile.z')]) . '_'
            . md5(implode('|', [
                implode(',', MapHelper::viewLevels()),
                implode(',', $this->getState('filter.cats')),
                $this->getState('filter.search'),
                $this->language(),
            ]));
    }

    /**
     * @param   bool  $withCategory  Include catid (needed for the colour of the dots).
     *
     * @return  array[]  id, lng, lat, title (and catid).
     */
    public function getItems(bool $withCategory = true): array
    {
        $cats = $this->getState('filter.cats');

        if (!$cats) {
            return [];
        }

        $z    = (int) $this->getState('tile.z');
        $rect = TileHelper::getTileRect((int) $this->getState('tile.x'), (int) $this->getState('tile.y'), $z);
        $db   = $this->getDatabase();
        $now  = Factory::getDate()->toSql();
        $lat  = $db->quoteName('m.gmlat');
        $lng  = $db->quoteName('m.gmlng');

        $query = $db->getQuery(true)
            ->select([$db->quoteName('m.id'), $db->quoteName('m.gmlng', 'lng'), $db->quoteName('m.gmlat', 'lat'), $db->quoteName('m.title')])
            ->from($db->quoteName('#__hotspots_marker', 'm'))
            ->where(sprintf('%1$s > %2$F AND %1$s < %3$F', $lng, $rect['lng'], $rect['lng'] + $rect['width']))
            ->where(sprintf('%1$s >= %2$F AND %1$s <= %3$F', $lat, $rect['lat'], $rect['lat'] + $rect['height']))
            ->whereIn($db->quoteName('m.catid'), $cats)
            ->where($db->quoteName('m.state') . ' = 1')
            ->where('(' . $db->quoteName('m.publish_up') . ' IS NULL OR ' . $db->quoteName('m.publish_up') . ' <= :pubup)')
            ->where('(' . $db->quoteName('m.publish_down') . ' IS NULL OR ' . $db->quoteName('m.publish_down') . ' >= :pubdown)')
            ->whereIn($db->quoteName('m.access'), MapHelper::viewLevels())
            ->bind(':pubup', $now)
            ->bind(':pubdown', $now)
            ->order('RAND(1)');

        if ($withCategory) {
            $query->select($db->quoteName('m.catid'));
        }

        $language = $this->language();

        if ($language !== '') {
            $query->whereIn($db->quoteName('m.language'), [$language, '*'], ParameterType::STRING);
        }

        $search = (string) $this->getState('filter.search');

        if ($search !== '') {
            $query->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('m.catid'));
            $this->whereSearch($query, $search);
        }

        // Far out the map gets cluttered anyway; no need for thousands of dots.
        $query->setLimit($z < 6 ? 100 : 500);

        return $db->setQuery($query)->loadAssocList();
    }

    private function whereSearch($query, string $sentence): void
    {
        $db      = $this->getDatabase();
        $columns = ['m.title', 'm.description', 'm.description_small', 'm.plz', 'c.title', 'm.street', 'm.country', 'm.town'];
        $groups  = [];

        if (preg_match('/"([^"]+)"/', $sentence, $match)) {
            $groups[] = [trim($match[1])];
            $rest     = trim(str_replace('"' . $match[1] . '"', '', $sentence));

            if ($rest !== '') {
                $groups[] = [$rest];
            }
        } else {
            $groups[] = \array_slice(array_values(array_filter(preg_split('/\s+/', $sentence))), 0, 10);
        }

        $n = 0;

        foreach ($groups as $words) {
            $or = [];

            foreach ($words as $word) {
                $name  = ':tsearch' . (++$n);
                $value = '%' . $db->escape($word, true) . '%';
                $query->bind($name, $value);
                unset($value);

                foreach ($columns as $column) {
                    $or[] = $db->quoteName($column) . ' LIKE ' . $name;
                }
            }

            if ($or) {
                $query->where('(' . implode(' OR ', $or) . ')');
            }
        }
    }

    private function language(): string
    {
        $app = Factory::getApplication();

        if (!$app->getLanguageFilter()) {
            return '';
        }

        $requested = (string) $this->getState('filter.language');

        return preg_match('/^[a-z]{2,3}-[A-Z]{2}$/', $requested) ? $requested : $app->getLanguage()->getTag();
    }
}
