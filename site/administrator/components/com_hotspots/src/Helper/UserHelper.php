<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

\defined('_JEXEC') or die;

/**
 * Names, avatars and profile links of marker authors. Avatars and profiles come from Community Builder
 * when the options support_avatars / profile_link are set to "comprofiler".
 */
abstract class UserHelper
{
    public const PROVIDER_CB = 'comprofiler';

    /**
     * @param   int[]  $ids  User ids.
     *
     * @return  array<int, object>  Id => object with id, name, username, avatar ('' when none) and profile ('' when none).
     */
    public static function getUsers(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (!$ids) {
            return [];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'name', 'username']))
            ->from($db->quoteName('#__users'))
            ->whereIn($db->quoteName('id'), $ids);

        $users = $db->setQuery($query)->loadObjectList('id') ?: [];

        foreach ($users as $user) {
            $user->id      = (int) $user->id;
            $user->avatar  = '';
            $user->profile = '';
        }

        if (!$users) {
            return [];
        }

        $avatars = self::isCb((string) SettingsHelper::get('support_avatars', '')) ? self::cbAvatars(array_keys($users)) : [];
        $profile = self::isCb((string) SettingsHelper::get('profile_link', ''));

        foreach ($users as $id => $user) {
            $user->avatar = $avatars[$id] ?? '';

            if ($profile) {
                $user->profile = self::cbProfileLink($id);
            }
        }

        return $users;
    }

    /**
     * Whether Community Builder is installed and enabled.
     */
    public static function cbAvailable(): bool
    {
        // isInstalled first: getComponent() on a missing component logs a warning.
        return ComponentHelper::isInstalled('com_comprofiler') && ComponentHelper::isEnabled('com_comprofiler');
    }

    private static function isCb(string $provider): bool
    {
        return $provider === self::PROVIDER_CB && self::cbAvailable();
    }

    /**
     * @return  array<int, string>  User id => avatar URL.
     */
    private static function cbAvatars(array $ids): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['user_id', 'avatar']))
            ->from($db->quoteName('#__comprofiler'))
            ->whereIn($db->quoteName('user_id'), $ids)
            ->where($db->quoteName('avatar') . ' != ' . $db->quote(''))
            ->where($db->quoteName('avatarapproved') . ' = 1');

        try {
            $rows = $db->setQuery($query)->loadObjectList();
        } catch (\RuntimeException) {
            return [];
        }

        $avatars = [];

        foreach ($rows as $row) {
            $avatar = (string) $row->avatar;

            // Gallery avatars have no thumbnail; uploaded ones have "tn" + name.
            $avatars[(int) $row->user_id] = Uri::root() . 'images/comprofiler/'
                . (str_contains($avatar, 'gallery/') ? $avatar : 'tn' . $avatar);
        }

        return $avatars;
    }

    private static function cbProfileLink(int $id): string
    {
        $link   = 'index.php?option=com_comprofiler&task=userProfile&user=' . $id;
        $menu   = Factory::getApplication()->getMenu('site');
        $itemId = 0;

        if ($menu) {
            $component = ComponentHelper::getComponent('com_comprofiler');

            foreach ($menu->getItems('component_id', $component->id) as $item) {
                $itemId = (int) $item->id;
                break;
            }
        }

        return Route::link('site', $link . ($itemId ? '&Itemid=' . $itemId : ''), false);
    }
}
