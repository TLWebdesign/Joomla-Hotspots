<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       01.05.14
 *
 * @copyright  Copyright (C) 2008 - 2014 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * Class HotspotsModelUsers
 *
 * @since  4.0
 */
class HotspotsModelUsers extends JModelLegacy
{
	/**
	 * Gets the user names from the database
	 *
	 * @param   array  $ids  - array with user ids
	 *
	 * @return mixed
	 */
	public function getUsers($ids)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, name, username')->from('#__users')->where('id IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		$users = $db->loadObjectList('id');

		// Load avatars
		if (count($users) && HotspotsHelperSettings::get('support_avatars', ''))
		{
			$avatarSystem = CompojoomAvatars::getInstance(HotspotsHelperSettings::get('support_avatars', ''));

			$avatars = $avatarSystem->getAvatars(array_keys($users));

			if ($avatars)
			{
				foreach ($users as $key => $user)
				{
					$users[$key]->avatar = $avatars[$key];
				}
			}
		}

		// Load profiles
		if (count($users) && HotspotsHelperSettings::get('profile_link', ''))
		{
			$profileSystem = CompojoomProfiles::getInstance(HotspotsHelperSettings::get('profile_link', ''));

			foreach ($users as $key => $user)
			{
				$users[$key]->profile = $profileSystem->getLink($user->id);
			}
		}

		return $users;
	}
}
