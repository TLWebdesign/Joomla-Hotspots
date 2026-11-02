<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       17.09.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The updates provisioning Model
 *
 * @since  4.0
 */
class HotspotsModelUpdates extends CompojoomModelUpdate
{
	private $isPro = HOTSPOTS_PRO;

	/**
	 * Public constructor. Initialises the protected members as well.
	 *
	 * @param   array  $config  - the config object
	 */
	public function __construct($config = array())
	{
		// If a valid Download ID is found, add it to extra_query (Needed for Joomla! 3.2+)
		$extraQuery = null;
		$dlid = $this->getDownloadId('com_hotspots');

		if (!empty($dlid))
		{
			$extraQuery = 'dlid=' . $dlid;
		}

		$updateURL = 'https://compojoom.com/index.php?option=com_ars&view=update&task=stream&format=xml&id=18&dummy=extension.xml';

		// If we are dealing with a PRO user, then we need a different URL
		if ($this->isPro)
		{
			$updateURL = 'https://compojoom.com/index.php?option=com_ars&view=update&task=stream&format=xml&id=4&dummy=extension.xml';
		}

		$baseConfig = array(
			'update_site'		=> $updateURL,
			'update_extraquery'	=> $extraQuery,
			'update_sitename'	=> 'Hotspots ' . (HOTSPOTS_PRO == 1 ? 'Professional' : 'Core')
		);

		$config = array_merge($config, $baseConfig);

		parent::__construct($config);
	}

	/**
	 * Checks the database for missing / outdated tables and installs or
	 * updates the database using the SQL xml file if necessary.
	 *
	 * @return	void
	 */
	public function checkAndFixDatabase()
	{
		// Makes sure that the compojoom library tables are created
		$libraryInstaller = new CompojoomDatabaseInstaller(
			array(
				'dbinstaller_directory' => JPATH_LIBRARIES . '/compojoom/sql/xml'
			)
		);

		$hotspotsInstaller = new CompojoomDatabaseInstaller(
			array(
				'dbinstaller_directory' => JPATH_ADMINISTRATOR . '/components/' . $this->component . '/sql/xml'
			)
		);

		$libraryInstaller->updateSchema();
		$hotspotsInstaller->updateSchema();

		// Fix everything related to the new categories in Hotspots 5
		HotspotsHelperUpdate::fixCategories();
	}


	/**
	 * Checks if the multimedia needs any updating
	 *
	 * @return mixed
	 */
	public function needsMultimediaUpdate()
	{
		$multimedia = JModelLegacy::getInstance('Multimedia', 'HotspotsModel');

		return $multimedia->healthCheck();
	}

	/**
	 * Does the user need to enter a Download ID in the component's Options page?
	 *
	 * @return bool
	 */
	public function needsDownloadID()
	{
		// Do I need a Download ID?
		$ret = true;
		$isPro = $this->isPro;

		if (!$isPro)
		{
			$ret = false;
		}
		else
		{
			$params = CompojoomUtilsComponent::getInstance($this->component);
			$dlid = $params->get('downloadid', '');

			if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
			{
				$ret = false;
			}
		}

		return $ret;
	}

	/**
	 * Let's find out if we need to warn the user that he cannot upgrade from core to PRO
	 * just by entering the download id
	 *
	 * @return bool
	 */
	public function mustWarnAboutDownloadIDInCore()
	{
		$ret = false;
		$isPro = $this->isPro;

		if ($isPro)
		{
			return $ret;
		}

		$params = CompojoomUtilsComponent::getInstance($this->component);
		$dlid = $params->get('downloadid', '');

		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$ret = true;
		}

		return $ret;
	}

	/**
	 * First we call the parent updateConfiguration() function in order to be sure that
	 * we have component parameters. After that we make sure that Hotspots is not running
	 * with wrong parameters (left from older versions) and we store the correct parameters in the
	 * database
	 *
	 * @return array - the parameters
	 */
	public function updateConfiguration()
	{
		$params = parent::updateConfiguration();

		if (isset($params['hotspots_order']) && strstr($params['hotspots_order'], 'name'))
		{
			$params['hotspots_order'] = str_replace('name', 'title', $params['hotspots_order']);
			$this->updateParams($params);
		}

		return $params;
	}
}
