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
		$model       = json_decode(JFactory::getApplication()->input->get('model', '', 'json'));
		$model->sent = false;
		$token       = JSession::getFormToken();
		$filter      = JFilterInput::getInstance();

		if (!isset($model->$token))
		{
			$model->status = "Couldn't send mail. Invalid form token.";

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

		$mailto   = $model->mailto;
		$subject  = $filter->clean($model->subject);
		$bodytext = $filter->clean($model->message);
		$url      = $filter->clean($model->url);

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

		if ($mailer->sendMail($from, $sender, $mailto, $subject, $message, true))
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
				$this->setRedirect($_SERVER['HTTP_REFERER'])->redirect();
				jexit('exit');
			}
		}

		$config = JFactory::getConfig();
		$id      = $input->getInt('hotspot_id');
		$model   = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
		$hotspot = $model->getItem($id);
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
			$this->setRedirect($_SERVER['HTTP_REFERER'])->redirect();
			jexit();
		}

		if (!$name)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_INVALID_NAME'));

			// Redirect back to the hotspot
			$this->setRedirect($_SERVER['HTTP_REFERER'])->redirect();
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
		$this->setRedirect($_SERVER['HTTP_REFERER']);
	}
}
