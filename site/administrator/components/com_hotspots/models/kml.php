<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.file');

/**
 * Class HotspotsModelKml
 *
 * @since  3.0
 */
class HotspotsModelKml extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_HOTSPOTS';

	/**
	 * Saves the KML file
	 *
	 * @param   array  $data  - the kml data
	 *
	 * @return bool
	 */
	public function save($data)
	{
        $input = JFactory::getApplication()->input;
        $file = $input->files->get('jform');
		$emptyFile = true;

		if (!empty($file))
		{
			if (!empty($file['kml_file']))
			{
				$newFile = $file['kml_file'];

				$emptyFile = false;
			}
		}

		$filedef = false;

		if (!$emptyFile)
		{
			$filedef = $this->uploadFile($newFile);
		}

		if ($filedef !== false)
		{
			$data['original_filename'] = $filedef['original_filename'];
			$data['mangled_filename'] = $filedef['mangled_filename'];
			$data['mime_type'] = $filedef['mime_type'];
		}

		return parent::save($data);
	}

	/**
	 * Moves an uploaded file to the media://com_ats/attachments directory
	 * under a random name and returns a full file definition array, or false if
	 * the upload failed for any reason.
	 *
	 * @param   array  $file  The file descriptor returned by PHP
	 *
	 * @return array|bool
	 */
	public function uploadFile($file)
	{
		if (isset($file['name']))
		{
			// TODO: check if user is allowed to upload this file

			// Get a (very!) randomised name
			$serverkey = JFactory::getConfig()->get('secret', '');
			$sig = $file['name'] . microtime() . $serverkey;

			if (function_exists('sha256'))
			{
				$mangledname = sha256($sig);
			}
			elseif (function_exists('sha1'))
			{
				$mangledname = sha1($sig);
			}
			else
			{
				$mangledname = md5($sig);
			}

			// ...and its full path
			$filepath = JPath::clean(JPATH_ROOT . '/media/com_hotspots/kmls/' . $mangledname);

			// If we have a name clash, abort the upload
			if (JFile::exists($filepath))
			{
				$this->setError(JText::_('COM_HOTSPOTS_ATTACHMENTS_ERR_NAMECLASH'));

				return false;
			}

			// Do the upload
			jimport('joomla.filesystem.file');

			if (!JFile::upload($file['tmp_name'], $filepath))
			{
				$this->setError(JText::_('COM_HOTSPOTS_ATTACHMENTS_ERR_CANTJFILEUPLOAD'));

				return false;
			}

			// Get the MIME type
			if (function_exists('mime_content_type'))
			{
				$mime = mime_content_type($filepath);
			}
			elseif (function_exists('finfo_open'))
			{
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $filepath);
			}
			else
			{
				$mime = 'application/octet-stream';
			}

			// Return the file info
			return array(
				'original_filename' => $file['name'],
				'mangled_filename' => $mangledname,
				'mime_type' => $mime
			);
		}
		else
		{
			$this->setError(JText::_('COM_HOTSPOTS_ATTACHMENTS_ERR_NOFILE'));

			return false;
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    the type
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @internal param \The $type table type to instantiate
	 * @return    JTable    A database object
	 */
	public function getTable($type = 'Kmls', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return   JForm    A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_hotspots.kmls', 'kml', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_hotspots.edit.kml.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
