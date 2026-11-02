<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       08.01.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

/**
 * Class plgHotspotsEmail
 *
 * @since  3.0
 */
class PlgHotspotsEmail extends JPlugin
{
	/**
	 * Overriding the constructor to make sure that the correct language files are loaded
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $params    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $params)
	{
		$jlang = JFactory::getLanguage();
		$jlang->load('com_hotspots', JPATH_SITE, 'en-GB', true);
		$jlang->load('com_hotspots', JPATH_SITE, $jlang->getDefault(), true);
		$jlang->load('com_hotspots', JPATH_SITE, null, true);

		parent::__construct($subject, $params);
	}

	/**
	 * Function called after a hotspot is saved into the db
	 *
	 * @param   string  $context  - the context
	 * @param   object  $data     - the data
	 * @param   int     $isNew    - a flag that determines if data is new
	 *
	 * @return bool
	 */
	public function onAfterHotspotSave($context, $data, $isNew = 0)
	{
		// Return if we are not dealing with a new hotspot
		if (!$isNew)
		{
			return true;
		}

		$jform = JFactory::getApplication()->input->get('jform', array(), 'array');

		// If we don't have a created_by value, then we are not dealing with a logged in user
		if (!$data->created_by)
		{
			$mail = $jform['email'];
		}
		else
		{
			$user = JFactory::getUser($data->created_by);
			$mail = $user->email;

			if (!$data->created_by_alias)
			{
				$data->created_by_alias = $user->name;
			}
		}

		$data->email = $mail;

		$this->sendMail($data);

		return true;
	}

	/**
	 * Send the mail to all moderators
	 *
	 * @param   object  $row  - the hotspot item
	 *
	 * @return void
	 */
	public function sendMail($row)
	{
		$mailList = $this->getModerators();

		if ($mailList)
		{
			if ($row->state)
			{
				$url = JURI::root() . JRoute::_('index.php?option=com_hotspots&view=hotspot&id=' . $row->id);
			}
			else
			{
				$url = JURI::base();
			}

			$subject = JText::_('COM_HOTSPOTS_MAIL_SUBJECT') . ': ' . $row->title;
			$subject .= '[' . JText::_('COM_HOTSPOTS_MAIL_FROM') . ':' . $row->created_by_alias . "]";
			$message = '<p>' . JText::_('COM_HOTSPOTS_MAIL_A_NEW_MARKER') . ' ' . $url . ' :</p>';
			$message .= '<p><b>' . JText::_('COM_HOTSPOTS_MAIL_AUTHOR') . ': </b>' . $row->created_by_alias . ' (' . $row->created_by . ') <br />';
			$message .= '<b>' . JText::_('COM_HOTSPOTS_MAIL_AUTHOR_MAIL') . ': </b>' . $row->email . '<br />';
			$message .= '<b>' . JText::_('COM_HOTSPOTS_MAIL_HOTSPOT_TITLE') . ': </b>' . $row->title . '<br />';
			$message .= '<b>' . JText::_('COM_HOTSPOTS_MAIL_HOTSPOT_ADDRESS') . ': </b>' . $row->street . ' ' . $row->plz . ' ' . $row->town . '<br />';
			$message .= '<b>' . JText::_('COM_HOTSPOTS_MAIL_HOTSPOT_SHORT_DESCRIPTION') . ': </b>' . $row->description_small . '</p>';
			$message .= '<p>' . JText::_('COM_HOTSPOTS_MAIL_NOTICE') . '</p>';

			$mailer = JFactory::getMailer();

			foreach ($mailList as $mail)
			{
				$mailer->ClearAddresses();
				$mailer->sendMail($mailer->From, $mailer->FromName, $mail, $subject, $message, true);
			}
		}
	}

	/**
	 * Get all moderators in the selected group in the params
	 *
	 * @return array|mixed
	 */
	public function getModerators()
	{
		$db = JFactory::getDBO();
		$moderators = array();

		$moderatorGroups = $this->params->get('email_notification', array());

		if (count($moderatorGroups))
		{
			$query = $db->getQuery(true);
			$query->select('DISTINCT u.email')
				->from('#__users AS u')
				->leftJoin('#__user_usergroup_map AS m ON u.id = m.user_id')
				->where('m.group_id IN (' . implode(',', $moderatorGroups) . ')')
				->where($db->qn('u.block') . '=' . $db->q(0));
			$db->setQuery($query);
			$moderators = $db->loadRowList();
		}

		return $moderators;
	}
}
