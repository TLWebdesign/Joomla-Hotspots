<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;

\defined('_JEXEC') or die;

/**
 * Permission checks for markers: component, category and "own" permissions.
 */
abstract class SecurityHelper
{
    /**
     * @param   string       $action  create, edit, edit.state, delete, ... (without "core.")
     * @param   object|null  $marker  Needs catid and created_by.
     */
    public static function authorise(string $action, ?object $marker = null, ?User $user = null): bool
    {
        $user ??= Factory::getApplication()->getIdentity() ?? new User();

        if ($user->authorise('core.' . $action, 'com_hotspots')) {
            return true;
        }

        if ($marker === null) {
            return false;
        }

        $catid = (int) ($marker->catid ?? 0);
        $isOwn = $user->id && (int) $user->id === (int) ($marker->created_by ?? 0);

        if ($catid && $user->authorise('core.' . $action, 'com_hotspots.category.' . $catid)) {
            return true;
        }

        if (!$isOwn) {
            return false;
        }

        return ($catid && $user->authorise('core.' . $action . '.own', 'com_hotspots.category.' . $catid))
            || $user->authorise('core.' . $action . '.own', 'com_hotspots');
    }

    /**
     * True when one of the user groups is in the allowed groups.
     */
    public static function groupHasAccess(array $userGroups, array $allowedGroups): bool
    {
        return (bool) array_intersect(array_map('intval', $allowedGroups), array_map('intval', $userGroups));
    }
}
