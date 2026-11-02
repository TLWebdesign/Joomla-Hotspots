<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       02.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controllerform');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/recaptcha/hotspotsRecaptcha.php';

/**
 * Class HotspotsControllerHotspot
 *
 * @since  3.0
 */
class HotspotsControllerHotspot extends JControllerForm
{
	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;

		// Need to do a lookup from the model.
		$record = $this->getModel()->getItem($recordId);

		if (empty($record))
		{
			return false;
		}

		return HotspotsHelperSecurity::authorise('edit', $record);
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   11.1
	 */
	public function save($key = null, $urlVar = "a_id")
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = JFactory::getApplication();
		$input = $app->input;
		$lang = JFactory::getLanguage();
		$model = $this->getModel();
		$table = $model->getTable();
		$user = JFactory::getUser();
		$data = $input->get('jform', '', 'array');
		$context = "$this->option.edit.$this->context";

		$itemId = '&Itemid=' . HotspotsHelperUtils::getItemid('com_hotspots', 'hotspots');

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = JFactory::getApplication()->input->getInt($urlVar);

		if (!$this->checkEditId($context, $recordId))
		{
			// Somehow the person just went to the form and tried to save it. We don't allow that.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// Populate the row id from the session.
		$data[$key] = $recordId;


		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=com_hotspots&view=hotspots' . $itemId
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// If we have a logged in user, we don't have the email field -> so we need to trick the validate function
		if ($user->get('id'))
		{
			$data['email'] = $user->get('email');
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(
					JRoute::_(
							'index.php?option=' . $this->option . '&view=form'
							. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
			);

			return false;
		}

		// If the user can't edit the state of the hotspot set the published value to the autopublish value
		if (!$user->authorise('core.edit.state', 'com_hotspots'))
		{
			$validData['state'] = HotspotsHelperSettings::get('addhs_autopublish', 1) ? 1 : 0;

			// TLWebdesign patch 2026-11-02: the form only shows these fields to users with core.edit.state.
			unset($validData['publish_up'], $validData['publish_down'], $validData['access']);
		}

		/*
		 * TLWebdesign patch 2026-11-02: never take the owner, dates or permissions from the frontend form
		 * (users could save markers in someone else's name). The table sets created_by for new markers.
		 */
		if (!$user->authorise('core.admin', 'com_hotspots'))
		{
			unset($validData['created_by'], $validData['created'], $validData['modified'], $validData['modified_by'],
				$validData['rules'], $validData['asset_id']);
		}

		// TLWebdesign patch 2026-11-02: new markers only in categories where the user may create.
		if (empty($validData['id'])
			&& !HotspotsHelperSecurity::authorise('create', (object) array('catid' => (int) ($validData['catid'] ?? 0), 'created_by' => $user->id)))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=form' . $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return false;
		}


		// Attempt to save the data.
		if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

			$this->setRedirect(
				JRoute::_(
						'index.php?option=' . $this->option . '&view=form'
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		$this->setMessage(
			JText::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isClient("site") ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isClient("site") ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		// Redirect the user and adjust session state.
		// Clear the record id and data from the session.
		$this->releaseEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		if (HotspotsHelperSettings::get('addhs_autopublish', 1) || ($user->authorise('core.edit.state', 'com_hotspots') && $validData['state']))
		{
			$cat = JCategories::getInstance('Hotspots')->get($validData['catid']);

			if ($cat)
			{
				$urlcat = $validData['catid'] . ':' . JFilterOutput::stringURLSafe($cat->alias);
			}

			$urlid = $model->getState('hotspot.id') . ':' . JFilterOutput::stringURLSafe($validData['title']);
			$redirect = HotspotsHelperRoute::getHotspotRoute($urlid, $urlcat);
		}
		else
		{
			$redirect = 'index.php?option=com_hotspots&view=hotspots' . $itemId;
		}

		if ($validData['state'])
		{
			// Redirect to the single view for that item.
			$this->setRedirect(JRoute::_($redirect, false), JText::_('COM_HOTSPOTS_HOTSPOT_' . ($data['id'] ? 'EDITED' : 'ADDED')));
		}
		else
		{
			// If the user can't edit the state of the hotspot let him know that the hotspot will be published by someone else
			if (!$user->authorise('core.edit.state', 'com_hotspots'))
			{
				$app->enqueueMessage(JText::_('COM_HOTSPOTS_HOTSPOT_SAVED_BUT_NEEDS_MODERATION'));
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_HOTSPOTS_HOTSPOT_SAVED_BUT_IT_IS_SET_TO_UNPUBLISHED'));
			}
			// Redirect to the map for unpublished items
			$this->setRedirect(
				JRoute::_('index.php?option=com_hotspots&view=hotspots' . $itemId, false)
			);
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
	}



	/**
	 * Save images if necessary
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return void
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$input = JFactory::getApplication()->input;
		$formData = new JInput($input->get('jform', '', 'array'));
		$this->addModelPath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
		$multimediaModel = JModelLegacy::getInstance('Multimedia', 'HotspotsModel');
		$files = $formData->get('picture', array(), 'array');
		$meta = $formData->get('picture_data', array(), 'array');

		// TLWebdesign patch 2026-11-02: image titles and descriptions are shown on the site; plain text only (XSS).
		$textFilter = JFilterInput::getInstance();

		foreach ($meta as $metaKey => $metaValue)
		{
			$meta[$metaKey] = array(
				'title'       => $textFilter->clean(is_array($metaValue) ? ($metaValue['title'] ?? '') : '', 'string'),
				'description' => $textFilter->clean(is_array($metaValue) ? ($metaValue['description'] ?? '') : '', 'string'),
			);
		}

		$item = $model->getItem();

		if ($item->get('id') && count($files))
		{
			// Import the appropriate plugin group.
			JPluginHelper::importPlugin('hotspots');

			$appl = JFactory::getApplication();

			// Do whatever you want with the images before they are saved on the server
			$appl->triggerEvent('onMultimediaBeforeUpload', array($item->get('id'), $files, $meta));

			// Save the images
			$multimediaModel->uploadPermanent($item->get('id'), $files, $meta);

			// Manipulate the images after they are uploaded
			$appl->triggerEvent('onMultimediaAfterUpload', array($item->get('id'), $files, $meta));
		}
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return  string	The return URL.
	 *
	 * @since   1.6
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base();
		}
		else
		{
			return base64_decode($return);
		}
	}
}
