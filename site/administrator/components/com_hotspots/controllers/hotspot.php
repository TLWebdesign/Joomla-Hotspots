<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       18.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controllerform');

/**
 * Class HotspotsControllerHotspot
 *
 * @since  5.0
 */
class HotspotsControllerHotspot extends JControllerForm
{
	/**
	 * The constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   11.1
	 */
	public function getModel($name = 'Hotspot', $prefix = 'HotspotsModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 */
	public function edit($key = null, $urlVar = null)
	{
		// Check if we already have categories
		if (!count(JCategories::getInstance('Hotspots')->get()->getChildren()))
		{
			$message = JText::_('COM_HOTSPOTS_CREATE_CATEGORIES_FIRST');
			$this->setRedirect('index.php?option=com_categories&extension=com_hotspots', $message, 'notice');

			return false;
		}

		return parent::edit();
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Hotspot', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_hotspots&view=hotspots' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}


	/**
	 * Save images if necessary
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return void
	 */
	protected function postSaveHook($model, $validData = array())
	{
		$input = JFactory::getApplication()->input;
		$formData = new JInput($input->get('jform', '', 'array'));

		/** @var HotspotsModelMultimedia $multimediaModel */
		$multimediaModel = JModelLegacy::getInstance('Multimedia', 'HotspotsModel');
		$files           = $formData->get('picture', array(), 'array');
		$meta            = $formData->get('picture_data', array(), 'array');

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
}
