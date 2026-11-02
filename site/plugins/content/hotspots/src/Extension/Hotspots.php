<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Plugin\Content\Hotspots\Extension;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Shows the map of one marker in an article: {hotspots hotspot=12}
 */
final class Hotspots extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    private const CONTEXTS = ['mod_custom.content', 'com_content.article', 'com_content.featured', 'com_content.category'];

    public static function getSubscribedEvents(): array
    {
        return ['onContentPrepare' => 'onContentPrepare'];
    }

    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        if (!\in_array($event->getContext(), self::CONTEXTS, true) || !$this->getApplication()->isClient('site')) {
            return;
        }

        $item = $event->getItem();

        if (!isset($item->text) || !\is_string($item->text) || stripos($item->text, '{hotspots') === false) {
            return;
        }

        if (!preg_match_all('/{hotspots\s+([^}]*)}/i', $item->text, $matches, PREG_SET_ORDER)) {
            return;
        }

        $this->getApplication()->getLanguage()->load('com_hotspots', JPATH_SITE);

        foreach ($matches as $match) {
            $settings = [];

            foreach (preg_split('/\s+/', trim(html_entity_decode($match[1]))) as $option) {
                if (str_contains($option, '=')) {
                    [$key, $value]          = explode('=', $option, 2);
                    $settings[trim($key)] = trim($value, " \"'");
                }
            }

            $marker = isset($settings['hotspot']) ? $this->getMarker((int) $settings['hotspot']) : null;
            $output = $marker ? $this->renderMap($marker) : '';

            $position = strpos($item->text, $match[0]);

            if ($position !== false) {
                $item->text = substr_replace($item->text, $output, $position, \strlen($match[0]));
            }
        }
    }

    /**
     * A published marker the current user may see, with the icon of its category.
     */
    private function getMarker(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        $db     = $this->getDatabase();
        $user   = $this->getApplication()->getIdentity();
        $levels = $user ? $user->getAuthorisedViewLevels() : [1];
        $now    = \Joomla\CMS\Factory::getDate()->toSql();
        $query  = $db->getQuery(true)
            ->select(['m.*', $db->quoteName('m.id', 'hotspots_id')])
            ->from($db->quoteName('#__hotspots_marker', 'm'))
            ->where($db->quoteName('m.id') . ' = :id')
            ->where($db->quoteName('m.state') . ' = 1')
            ->whereIn($db->quoteName('m.access'), $levels)
            ->where('(' . $db->quoteName('m.publish_up') . ' IS NULL OR ' . $db->quoteName('m.publish_up') . ' <= :now1)')
            ->where('(' . $db->quoteName('m.publish_down') . ' IS NULL OR ' . $db->quoteName('m.publish_down') . ' >= :now2)')
            ->bind(':id', $id, ParameterType::INTEGER)
            ->bind(':now1', $now)
            ->bind(':now2', $now);

        $marker = $db->setQuery($query)->loadObject();

        if (!$marker) {
            return null;
        }

        $category         = Categories::getInstance('Hotspots')?->get((int) $marker->catid);
        $marker->cat_icon = $category ? (string) (new Registry($category->params))->get('icon', '') : '';

        return $marker;
    }

    /**
     * Renders the single marker map layout of the component.
     */
    private function renderMap(object $marker): string
    {
        $frontend = JPATH_SITE . '/components/com_hotspots';

        // TODO phase 6: use the namespaced site component instead of the old helpers.
        require_once JPATH_LIBRARIES . '/compojoom/include.php';
        require_once $frontend . '/includes/defines.php';
        \JLoader::discover('HotspotsHelper', $frontend . '/helpers/');

        $marker = \HotspotsHelperUtils::prepareHotspot($marker);
        $icon   = $marker->params->get('markerimage') ?: $marker->cat_icon;

        $address = array_values(array_filter([$marker->street, $marker->town, $marker->plz, $marker->country]));

        $hotspot = [
            'id'        => (int) $marker->id,
            'latitude'  => $marker->gmlat,
            'longitude' => $marker->gmlng,
            'title'     => htmlspecialchars((string) $marker->title, ENT_QUOTES, 'UTF-8'),
            'icon'      => $icon ? Uri::root() . $icon : '',
        ];

        $layout = new \CompojoomLayoutFile('hotspots.single-map', JPATH_ROOT . '/components/com_hotspots/layouts');

        return $layout->render([
            'hotspot'      => $hotspot,
            'addressArray' => array_map(static fn ($part) => htmlspecialchars((string) $part, ENT_QUOTES, 'UTF-8'), $address),
            'params'       => $this->params,
            'frontend'     => $frontend,
        ]);
    }
}
