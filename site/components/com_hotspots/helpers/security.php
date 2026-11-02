<?php
/**
 * @package    Hotspots
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       05.11.2015
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperStats
 *
 * @since  4.0
 */
class HotspotsHelperSecurity
{
	/**
	 * Check if a user has the right to edit a hotspot
	 *
	 * @param   string       $action   - the action type (edit, delete)
	 * @param   object|null  $hotspot  - the hotspots that we are checking against
	 * @param   JUser|null   $user     - the user we want to check, if none provided we will use the currently logged in user
	 *
	 * @return bool  - true if the user can edit the item, false otherwise
	 */
	public static function authorise($action, $hotspot = null, $user = null)
	{
		if (is_null($user))
		{
			$user = JFactory::getUser();
		}

		// If we have the general edit permission, then we can edit every hotspot
		if ($user->authorise('core.' . $action, 'com_hotspots'))
		{
			return true;
		}

		if (!is_null($hotspot))
		{
			$catId = (int) $hotspot->catid;

			if ($catId)
			{
				if ($user->authorise('core.' . $action, 'com_hotspots.category.' . $catId))
				{
					return true;
				}

				// If we have core.edit.own, we need to check if the current hotspot belongs to the user
				if ($user->authorise('core.' . $action . '.own', 'com_hotspots.category.' . $catId))
				{
					// If we have a hotpot, check that the created_by flag equals the user id
					if ($user->id == $hotspot->created_by)
					{
						return true;
					}
				}
			}
		}

		// If we have core.edit.own, we need to check if the current hotspot belongs to the user
		if ($user->authorise('core.' . $action . '.own', 'com_hotspots'))
		{
			// If we have a hotpot, check that the created_by flag equals the user id
			if (!is_null($hotspot) && $user->id == $hotspot->created_by)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Function to check if a user has edit or edit.own permissions to an Item
	 *
	 * @param   object  $item  - the item in question
	 *
	 * @return boolean
	 *
	 * @since 5.3.5
	 */
	public static function canEdit($item)
	{
		if (self::authorise('edit', $item) && self::authorise('edit.own', $item))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the usergroup has access
	 *
	 * @param   array  $userGroups       - the userGroup array for the comment
	 * @param   array  $moderatorGroups  - the moderator groups
	 *
	 * @return bool
	 */
	public static function groupHasAccess($userGroups, $moderatorGroups)
	{
		if (!is_array($userGroups) || !is_array($moderatorGroups))
		{
			return false;
		}

		$groupIds = array();

		// Transform the array so that it contains only the group ids if necessary
		if (isset($userGroups[0]['group_id']))
		{
			foreach ($userGroups as $value)
			{
				$groupIds[] = $value['group_id'];
			}
		}
		else
		{
			$groupIds = $userGroups;
		}

		if (!array_intersect($moderatorGroups, $groupIds))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
