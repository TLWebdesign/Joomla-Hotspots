<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Helper;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\UserHelper;

\defined('_JEXEC') or die;

/**
 * The marker objects of the map JSON (same keys as Hotspots 6).
 */
abstract class JsonHelper
{
    /**
     * @param   object  $marker        A marker row after MarkerHelper::prepare(); multimedia may be set (gallery data).
     * @param   array   $users         Id => user from UserHelper::getUsers(); loaded when empty and needed.
     * @param   array   $customFields  Slug => field from CustomfieldsHelper::getFields(); none when empty.
     */
    public static function marker(object $marker, array $users = [], array $customFields = []): array
    {
        $settings    = SettingsHelper::getAll();
        $description = preg_replace('@[\r\n\t]+@', '', (string) $marker->description_small);
        $createdBy   = (int) ($marker->created_by ?? 0);

        // Plain text: the Handlebars templates of the map escape {{ title }} etc. themselves.
        $json = [
            'id'             => (int) ($marker->hotspots_id ?? $marker->id),
            'catid'          => (int) $marker->catid,
            'lat'            => (float) $marker->gmlat,
            'lng'            => (float) $marker->gmlng,
            'title'          => (string) $marker->title,
            'cutDescription' => HTMLHelper::_('string.truncateComplex', $description, 100),
            'description'    => $description,
            'street'         => (string) $marker->street,
            'city'           => (string) $marker->town,
            'zip'            => (string) $marker->plz,
            'country'        => (string) $marker->country,
            'state'          => (string) $marker->administrative_area_level_1,
            'address'        => html_entity_decode(MarkerHelper::formattedAddress($marker), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'date'           => $marker->created,
            'readmore'       => (string) ($marker->link ?? ''),
            'multimedia'     => $marker->multimedia ?? '',
        ];

        if ($customFields) {
            $json['customfields'] = preg_replace('@[\r\n\t]+@', '', self::renderCustomFields($marker, $customFields));
        }

        $needsUsers = $createdBy && ($settings->get('show_author', 1) || $settings->get('support_avatars', '') || $settings->get('profile_link', ''));

        if ($needsUsers && !isset($users[$createdBy])) {
            $users += UserHelper::getUsers([$createdBy]);
        }

        $user = $users[$createdBy] ?? null;

        if ($settings->get('show_author', 1)) {
            if (trim((string) $marker->created_by_alias) !== '') {
                $author = (string) $marker->created_by_alias;
            } elseif ($user) {
                $author = $settings->get('use_name', 1) ? (string) $user->name : (string) $user->username;
            } else {
                $author = 'Anonymous';
            }

            $json['created_by'] = $author;
        }

        if ($user && $settings->get('support_avatars', '')) {
            $json['avatar'] = $user->avatar !== '' ? $user->avatar : null;
        }

        if ($user && $settings->get('profile_link', '')) {
            $json['profile'] = $user->profile;
        }

        if ($settings->get('show_date', 1)) {
            $json['date'] = MarkerHelper::localDate($marker->created);
        }

        $params = $marker->params instanceof Registry ? $marker->params : new Registry($marker->params ?? '');

        if ($params->get('markerimage')) {
            $json['icon'] = MarkerHelper::iconBaseUrl() . $params->get('markerimage');
        }

        return $json;
    }

    /**
     * Custom field config for these categories (slug => field), or [] when there are none.
     *
     * @param   int[]  $catids
     */
    public static function customFields(array $catids): array
    {
        return $catids ? CustomfieldsHelper::getFields($catids) : [];
    }

    private static function renderCustomFields(object $marker, array $fields): string
    {
        $values = json_decode((string) ($marker->customfields ?? ''), true);

        if (!\is_array($values) || !$values) {
            return '';
        }

        $html = '';

        foreach ($values as $slug => $value) {
            if (!isset($fields[$slug]) || $value === '' || $value === null || $value === []) {
                continue;
            }

            $html .= '<dl class="dl-horizontal"><dt>' . htmlspecialchars(Text::_((string) $fields[$slug]->title), ENT_QUOTES, 'UTF-8')
                . '</dt><dd>' . CustomfieldsHelper::render($fields[$slug], $value) . '</dd></dl>';
        }

        return $html;
    }
}
