<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Hotspot;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\GalleryHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\UserHelper;
use TLWebdesign\Component\Hotspots\Site\Model\HotspotModel;

\defined('_JEXEC') or die;

/**
 * The marker page.
 */
class HtmlView extends BaseHtmlView
{
    protected object $item;

    protected Registry $params;

    /** Icon URL of the marker (its own icon or the category icon). */
    protected string $icon = '';

    /** Author name as shown on the page ('' when the author is hidden). */
    protected string $author = '';

    /** Profile link of the author ('' without a profile system). */
    protected string $profile = '';

    /** Gallery images: list of arrays with big, image, thumb, title, description. */
    protected array $gallery = [];

    /** Custom field definitions of the marker's category, by slug. */
    protected array $customFields = [];

    /** Custom field values of the marker, by slug. */
    protected array $customValues = [];

    /** May the current user use the contact-the-author form, or see the "log in to contact" note. */
    protected bool $canContact = false;

    protected bool $showContactNote = false;

    protected bool $canEdit = false;

    protected bool $canDelete = false;

    public function display($tpl = null)
    {
        /** @var HotspotModel $model */
        $model = $this->getModel();
        $item  = $model->getItem();

        if (!$item) {
            throw new GenericDataException(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        $app          = Factory::getApplication();
        $user         = $this->getCurrentUser();
        $this->params = SettingsHelper::getAll();
        $this->item   = $item;

        if ((int) $this->params->get('marker_allow_plugin', 0) === 1) {
            $item->description_small = HTMLHelper::_('content.prepare', $item->description_small, '', 'com_hotspots.marker');
            $item->description       = HTMLHelper::_('content.prepare', $item->description, '', 'com_hotspots.marker');
        }

        $item->link     = MarkerHelper::markerUrl($item);
        $item->postdate = MarkerHelper::localDate($item->created);

        $category = Categories::getInstance('Hotspots')?->get((int) $item->catid);
        $catIcon  = $category ? (string) (new Registry($category->params))->get('icon', '') : '';

        $this->icon = $item->params->get('markerimage')
            ? MarkerHelper::iconBaseUrl() . $item->params->get('markerimage')
            : ($catIcon !== '' ? Uri::root() . $catIcon : '');

        $this->prepareAuthor();

        $this->gallery = GalleryHelper::getData((int) $item->id, false) ?: [];

        $this->customFields = CustomfieldsHelper::getFields((int) $item->catid);
        $this->customValues = (new Registry($item->customfields ?? ''))->toArray();

        $this->canEdit   = SecurityHelper::authorise('edit', $item, $user);
        $this->canDelete = SecurityHelper::authorise('delete', $item, $user);

        if ($item->created_by && $this->params->get('show_contact_author', 0)) {
            $this->canContact      = SecurityHelper::groupHasAccess($user->getAuthorisedGroups(), (array) $this->params->get('authorized_contact_author_groups', []));
            $this->showContactNote = !$this->canContact && (bool) $this->params->get('show_contact_author_message_authorized', 0);
        }

        if (HotspotModel::isHidden($item)) {
            $app->enqueueMessage(Text::_('COM_HOTSPOTS_UNPUBLISHED_BUT_VISIBLE_TO_MODERATOR'), 'notice');
        }

        $this->prepareDocument($category);

        parent::display($tpl);
    }

    /**
     * The single marker map (layout of the site component).
     */
    public function renderMap(): string
    {
        $item = $this->item;

        $hotspot = [
            'id'          => (int) $item->id,
            'latitude'    => (float) $item->gmlat,
            'longitude'   => (float) $item->gmlng,
            'title'       => $this->escape($item->title),
            'icon'        => $this->icon,
            'description' => '<strong>' . $this->escape($item->title) . '</strong><br>'
                . ($this->params->get('show_address', 1) ? MarkerHelper::formattedAddress($item) : ''),
        ];

        $mapParams = new Registry([
            'map_height' => (int) $this->params->get('map_height_single', 300),
            'zoom'       => (int) $this->params->get('map_startzoom_single', 15),
            'map_width'  => 0,
        ]);

        return LayoutHelper::render('hotspots.single-map', [
            'hotspot'      => $hotspot,
            'addressArray' => [],
            'params'       => $mapParams,
        ], JPATH_SITE . '/components/com_hotspots/layouts');
    }

    private function prepareAuthor(): void
    {
        $item = $this->item;

        if ($item->created_by) {
            $users = UserHelper::getUsers([(int) $item->created_by]);
            $info  = $users[(int) $item->created_by] ?? null;

            $this->profile = (string) ($info->profile ?? '');
        }

        if ($item->created_by_alias) {
            $this->author = (string) $item->created_by_alias;
        } elseif ($item->created_by) {
            $this->author = (string) ($this->params->get('use_name', 1) ? $item->user_name : $item->user_username);
        }
    }

    private function prepareDocument(?object $category): void
    {
        $app      = Factory::getApplication();
        $item     = $this->item;
        $document = $this->getDocument();
        $menu     = $app->getMenu()->getActive();
        $isMenu   = $menu && ($menu->query['view'] ?? '') === 'hotspot' && (int) ($menu->query['id'] ?? 0) === (int) $item->id;

        if (!$isMenu) {
            if ($category) {
                $app->getPathway()->addItem($category->title, '');
            }

            $app->getPathway()->addItem($item->title, '');
        }

        $this->setDocumentTitle($item->title);

        $summary = trim(preg_replace('/\s+/', ' ', strip_tags((string) $item->description_small)));

        if ($summary !== '') {
            $document->setDescription(HTMLHelper::_('string.truncate', $summary, 160, true, false));
        }

        if (!$this->params->get('ogp', 0)) {
            return;
        }

        // Open Graph: what social sites show when the page is shared.
        $image = $this->gallery[0]['image'] ?? $this->gallery[0]['big'] ?? '';

        $document->setMetaData('og:title', $item->title, 'property');
        $document->setMetaData('og:type', 'place', 'property');
        $document->setMetaData('og:url', Uri::getInstance()->toString(['scheme', 'host', 'port']) . $item->link, 'property');
        $document->setMetaData('og:description', HTMLHelper::_('string.truncate', $summary, 300, true, false), 'property');
        $document->setMetaData('place:location:latitude', (string) $item->gmlat, 'property');
        $document->setMetaData('place:location:longitude', (string) $item->gmlng, 'property');

        if ($image !== '') {
            $document->setMetaData('og:image', $image, 'property');
        }
    }
}
