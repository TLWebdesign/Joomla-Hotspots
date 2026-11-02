<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       05.11.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

/**
 * Class hotspotsModelHotspot
 *
 * @since  3
 */
class HotspotsModelHotspot extends JModelAdmin
{
	private $hotspot = null;

	private $id = null;

	private $hotspots = null;

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 */
	protected function canDelete($record)
	{
		return HotspotsHelperSecurity::authorise('delete', $record);
	}

	/**
	 * Constructor
	 *
	 * @param   array  $config  - the config
	 */
	public function __construct($config = array())
	{
		$config['event_before_save'] = 'onBeforeHotspotSave';
		$config['event_after_save'] = 'onAfterHotspotSave';

		JPluginHelper::importPlugin('hotspots');

		parent::__construct($config);

		$input = JFactory::getApplication()->input;
		$id = $input->getInt('id', 0);
		$this->_catid = $input->getInt('cat', 1);

		if ($id != 0)
		{
			$this->id = $id;
		}
	}

	/**
	 * Gets a single hotspot
	 *
	 * @return mixed|null
	 */
	public function getHotspot()
	{
		if (!$this->hotspot)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select(' m.id as hotspots_id, m.*, u.name AS user_name')
				->from($db->qn('#__hotspots_marker') . ' AS m')
				->leftJoin('#__users AS u ON u.id = m.created_by')
				->where('m.id = ' . $db->Quote($this->id));

			// TLWebdesign patch 2026-11-02: respect the access level (the owner always sees his own marker).
			$user   = JFactory::getUser();
			$levels = implode(',', \Joomla\Utilities\ArrayHelper::toInteger($user->getAuthorisedViewLevels()));
			$query->where('(m.access IN (' . $levels . ')' . ($user->id ? ' OR m.created_by = ' . (int) $user->id : '') . ')');

			$db->setQuery($query, 0, 1);
			$this->hotspot = $db->loadObject();
		}

		return $this->hotspot;
	}

	/**
	 * populating the state
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		if ($app->isClient("site"))
		{
			$this->setState('filter.language', $app->getLanguageFilter());
		}

	}

	/**
	 * method to get an instance of a JTable class if it can be found in
	 * the table include paths.
	 *
	 * @param   string  $type    The type (name) of the JTable class to get an instance of.
	 * @param   string  $prefix  An optional prefix for the table class name.
	 * @param   array   $config  An optional array of configuration values for the JTable object.
	 *
	 * @return  mixed    A JTable object if found or boolean false if one could not be found.
	 */
	public function getTable($type = 'Marker', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      - An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  - True if the form is to load its own data (default case), false if not.
	 *
	 * @return   JForm  - A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_hotspots.marker', 'marker', array('control' => 'jform', 'load_data' => $loadData));
		$user = JFactory::getUser();

		// Make sure that the custom fields are part of the form
		$customFieldsModel = $this->getInstance('Customfields', 'HotspotsModel');

		$form->load(CompojoomFormCustom::generateFormXML($customFieldsModel->getFields('com_hotspots.hotspot', $data['catid'])));

		if (empty($form))
		{
			return false;
		}

		$userRecaptcha = HotspotsHelperUtils::isUserInGroups(HotspotsHelperSettings::get('captcha_usergroup', array(1)));

		// If recaptcha is on but the user isn't supposed to be checked for recaptcha, then remove the captcha field
		if ($data['id'] || (HotspotsHelperSettings::get('captcha', 0) && !$userRecaptcha))
		{
			$form->removeField('captcha');
		}

		// Determine correct permissions to check.
		if ($this->getState('hotspot.id'))
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Those fields are not required when we are dealing with logged in users
		if (!$user->guest)
		{
			$form->setFieldAttribute('created_by_alias', 'required', false);
			$form->setFieldAttribute('email', 'required', false);
		}

		return $form;
	}

	/**
	 * Validates the data
	 *
	 * @param   JForm  $form   - the form
	 * @param   array  $data   - the data
	 * @param   null   $group  - the group
	 *
	 * @return mixed
	 */
	public function validate($form, $data, $group = null)
	{
		$user = JFactory::getUser();
		$jform = JFactory::getApplication()->input->get('jform', array(), 'ARRAY');

		// Do those checks only if we don't have userId
		if (!$user->id)
		{
			$data['created_by_alias'] = $jform['created_by_alias'];
			$data['email'] = $jform['email'];
		}

		return parent::validate($form, $data);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$input = JFactory::getApplication()->input;

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0))
		{
			if (!isset($data['alias']) || $data['alias'] == null)
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['title']);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($data['title']);
				}

				list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
				$data['alias'] = $alias;
			}
		}

		return parent::save($data);
	}
}
