<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       27.03.15
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

use \Joomla\CMS\Factory;
require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/recaptcha/hotspotsRecaptcha.php';

/**
 * Class HotspotsViewForm
 *
 * @since  3.0
 */
class HotspotsViewForm extends HotspotsView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$user            = Factory::getUser();
		$this->recaptcha = null;

		// Get model data.
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');


		$this->returnPage = $this->get('ReturnPage');

		if (empty($this->item->id))
		{
			$authorised = $user->authorise('core.create', 'com_hotspots');
		}
		else
		{
			$authorised = $user->authorise('core.edit.own', 'com_hotspots');
		}


		if ($authorised !== true)
		{
			Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'warning');

			return false;
		}

		// Create a shortcut to the parameters.
		$params = &$this->state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->params = $params;
		$this->user   = $user;

		$captchaGroups = HotspotsHelperUtils::isUserInGroups(HotspotsHelperSettings::get('captcha_usergroup', array()));
		$captcha       = HotspotsHelperSettings::get('captcha');

		$this->showCaptcha = false;

		if ($captcha === 0 || $captcha === '0')
		{
			$this->showCaptcha = false;
		}
		elseif ($captcha && $captchaGroups)
		{
			$this->showCaptcha = true;
		}

		$this->setLayout('form');
		parent::display($tpl);
	}
}
