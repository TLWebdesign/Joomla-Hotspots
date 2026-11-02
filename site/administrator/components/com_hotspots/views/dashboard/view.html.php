<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Class HotspotsViewHotspots
 *
 * @since  2.0
 */
class HotspotsViewDashboard extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// TLWebdesign patch 2026-11-02: no more compojoom.com update site, download ID or stats checks.
		$updateModel = JModelLegacy::getInstance('Updates', 'HotspotsModel');

		$this->currentVersion = $updateModel->getVersion();

		// Make sure that the user has all config values set properly
		$updateModel->updateConfiguration();

		// Calculate release date and pass it to template
		JLoader::import('joomla.utilities.date');
		$jDate = new JDate(HOTSPOTS_DATE);
		$day	= $jDate->format('d');
		$month	= $jDate->format('F');
		$year	= $jDate->format('Y');
		$this->releaseDate = "$day $month $year";

		$this->needsMultimediaUpdate = $updateModel->needsMultimediaUpdate();

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds a toolbar
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		JToolBarHelper::preferences('com_hotspots');
	}

	/**
	 * Sets the mootools locale
	 *
	 * @return void
	 */
	public function setMootoolsLocale()
	{
		$document = JFactory::getDocument();
		$language = JFactory::getLanguage();

		$mootoolsLocale = "Locale.use('" . $language->getTag() . "')";

		$locale = "window.addEvent('domready', function() {
			$mootoolsLocale
		});";

		$document->addScriptDeclaration($locale);
	}
}
