<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       01.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
jimport('joomla.mail.helper');

/**
 * Class HotspotsControllerMail
 *
 * @since  3.5
 */
class HotspotsControllerMail extends JControllerLegacy
{
	public function send()
	{
		/*
		 * TLWebdesign patch 2026-11-02: this was an open mail relay. Anyone could send any text to any
		 * address with any From address. Now the feature has to be enabled, the mail comes from the site's
		 * own address (visitor as Reply-To), the link must point to this site and a session can send at
		 * most 5 mails per hour.
		 */
		$model = json_decode(JFactory::getApplication()->input->get('model', '', 'raw'));

		if (!is_object($model))
		{
			jexit();
		}

		$model->sent = false;
		$token       = JSession::getFormToken();
		$filter      = JFilterInput::getInstance();

		if (!isset($model->$token))
		{
			$model->status = "Couldn't send mail. Invalid form token.";

			echo json_encode($model);
			jexit();
		}

		if (!HotspotsHelperSettings::get('mail_map', 0))
		{
			$model->status = "Couldn't send mail.";

			echo json_encode($model);
			jexit();
		}

		$session = JFactory::getSession();
		$sentAt  = array_filter(
			(array) $session->get('com_hotspots.mail.sent', array()),
			function ($time) {
				return $time > time() - 3600;
			}
		);

		if (count($sentAt) >= 5)
		{
			$model->status = "Couldn't send mail. Please try again later.";

			echo json_encode($model);
			jexit();
		}

		$input  = JFactory::getApplication()->input;
		$user   = JFactory::getUser();
		$config = JFactory::getConfig();


		// If the user is logged get his username/email from the user object
		if ($user->id)
		{
			$from   = $user->get('email');
			$sender = $user->get('name');
		}
		else
		{
			$from   = $filter->clean($model->{'sender-email'});
			$sender = $filter->clean($model->sender, null);
		}

		$mailto   = (string) ($model->mailto ?? '');
		$subject  = $filter->clean($model->subject ?? '');
		$bodytext = $filter->clean($model->message ?? '');
		$url      = $filter->clean($model->url ?? '');

		if (!\Joomla\CMS\Uri\Uri::isInternal($url))
		{
			$url = \Joomla\CMS\Uri\Uri::root();
		}

		// Check the validity of the mails
		if (!JMailHelper::isEmailAddress($mailto) || !JMailHelper::isEmailAddress($from))
		{
			$model->status = "Wrong email address provided.";
			echo json_encode($model);
			jexit();
		}

		$message = '<p>' . JText::sprintf('COM_HOTSPOTS_MAP_EXCERPT_SENT',
				$from,
				$sender,
				$url
			) . '</p>';

		$message .= '<p>' . JText::_('COM_HOTSPOTS_MESSAGE') . ':</p>';
		$message .= '<p>' . $bodytext . '</p>';

		$mailer = JFactory::getMailer();
		$sentAt[] = time();
		$session->set('com_hotspots.mail.sent', $sentAt);

		if ($mailer->sendMail($config->get('mailfrom'), $config->get('fromname'), $mailto, $subject, $message, true, null, null, null, $from, $sender))
		{
			$model->sent   = true;
			$model->status = 'Sent successfully';

			echo json_encode($model);
			jexit();
		}
		else
		{
			$model->sent = true;

			echo json_encode($model);
			jexit();
		}
	}

	/**
	 * This function sends a mail to the owner of a Hotspot
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function contact()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$input  = JFactory::getApplication()->input;
		$filter = JFilterInput::getInstance();
		$user = JFactory::getUser();

		/*
		 * TLWebdesign patch 2026-11-02: the settings "show contact author" and "authorized groups" were only
		 * checked in the template, so anyone could mail the author of any marker (also unpublished ones).
		 * Redirect back to the marker instead of to the unchecked HTTP referer.
		 */
		$hsParams = JComponentHelper::getParams('com_hotspots');

		if (!HotspotsHelperSettings::get('show_contact_author', 0)
			|| !HotspotsHelperSecurity::groupHasAccess($user->getAuthorisedGroups(), $hsParams->get('authorized_contact_author_groups')))
		{
			throw new \Joomla\CMS\Access\Exception\NotAllowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$back = JRoute::_(HotspotsHelperRoute::getHotspotRoute($input->getInt('hotspot_id')), false);

		// Check if we have a captcha and if it is valid
		if ((JPluginHelper::isEnabled('captcha', HotspotsHelperSettings::get('captcha')) && HotspotsHelperSettings::get('captcha')))
		{
			JPluginHelper::importPlugin('captcha', HotspotsHelperSettings::get('captcha'));
			$appl = JFactory::getApplication();

			$res = $appl->triggerEvent('onCheckAnswer', array($input->post->get('recaptcha_response_field')));

			if (!$res[0])
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_HOTSPOTS_INVALID_CAPTCHA'), 'error');

				// Redirect back to the hotspot
				$this->setRedirect($back)->redirect();
				jexit('exit');
			}
		}

		$config = JFactory::getConfig();
		$id      = $input->getInt('hotspot_id');
		$model   = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
		$hotspot = $model->getItem($id);

		if (!$hotspot || empty($hotspot->id) || (int) $hotspot->state !== 1 || empty($hotspot->created_by))
		{
			throw new \Exception(JText::_('JERROR_PAGE_NOT_FOUND'), 404);
		}
		$name    = $filter->clean($input->get('name'));
		$from    = $filter->clean($input->getString('email'));
		$messageContent = $filter->clean($input->getString('message'));

		$creatorEmail = JFactory::getUser($hotspot->created_by)->get('email');

		// If the user is logged in, get the name and email from his profile
		if (!$user->guest)
		{
			$name = $user->get('name');
			$from = $user->get('email');
		}

		// Check the validity of the mails
		if (!JMailHelper::isEmailAddress($from))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_WRONG_MAIL_ADDRESS'));

			// Redirect back to the hotspot
			$this->setRedirect($back)->redirect();
			jexit();
		}

		if (!$name)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_INVALID_NAME'));

			// Redirect back to the hotspot
			$this->setRedirect($back)->redirect();
			jexit();
		}

		$layout = new CompojoomLayoutFile('mail.contact');

		$subject = JText::sprintf('COM_HOTSPOTS_CONTACT_AUTHOR_EMAIL_SUBJECT', '"' . $hotspot->title . '"', $name);
		$body    = $layout->render(
			array('hotspot' => $hotspot,
				  'author'  => JFactory::getUser($hotspot->created_by),
				  'name'    => $name,
				  'from'    => $from,
				  'message' => $messageContent)
		);

		$mailer = JFactory::getMailer();

		$message = JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_MAIL_SENT_UNSUCCESSFUL');

		$mailer->addReplyTo($from, $name);
		if ($mailer->sendMail($config->get('mailfrom'), $config->get('sitename'), $creatorEmail, $subject, $body, true))
		{
			$message = JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_MAIL_SENT');
		}

		JFactory::getApplication()->enqueueMessage($message);

		// Redirect back to the hotspot
		$this->setRedirect($back);
	}
}
