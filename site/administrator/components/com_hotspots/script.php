<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       10.06.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class com_hotspotsInstallerScript
 *
 * @since  3.0
 */
class Com_HotspotsInstallerScript
{
	public $extension = 'com_hotspots';

	public $droppedTables = false;

	/**
	 * @var CompojoomInstaller
	 */
	private $installer;

	private $status;

	protected $removeFilesAllVersions = array(
		'files'   => array(
			'administrator/components/com_hotspots/admin.hotspots.html.php',
			'administrator/components/com_hotspots/admin.hotspots.php',
			'administrator/components/com_hotspots/install.mysql.sql',
			'administrator/components/com_hotspots/uninstall.mysql.sql',
			'administrator/components/com_hotspots/controllers/categories.php',
			'administrator/components/com_hotspots/controllers/category.php',
			'administrator/components/com_hotspots/models/categories.php',
			'administrator/components/com_hotspots/models/category.php',
			'administrator/components/com_hotspots/models/forms/categorie.xml',
			'administrator/components/com_hotspots/tables/categorie.php',
			'administrator/components/com_hotspots/tables/settings.php',
			'administrator/components/com_hotspots/views/categories/tmpl/default.php',
			'administrator/components/com_hotspots/views/categories/view.html.php',
			'administrator/components/com_hotspots/views/category/tmpl/default.php',
			'administrator/components/com_hotspots/views/category/tmpl/default_params.php',
			'administrator/components/com_hotspots/views/category/view.html.php',
			'administrator/components/com_hotspots/helpers/basic.php',
			'administrator/components/com_hotspots/helpers/feed.php',
			'administrator/components/com_hotspots/helpers/menu.php',
			'administrator/components/com_hotspots/helpers/color.php',
			'administrator/components/com_hotspots/helpers/stats.php',
			'administrator/components/com_hotspots/tables/customfield.php',
			'administrator/components/com_hotspots/sql/unistall.mysql.sql',
			'administrator/components/com_hotspots/sql/install.mysql.sql',
			'administrator/components/com_hotspots/sql/install.sqlsrv.sql',
			'administrator/components/com_hotspots/admin.utils.php',
			'administrator/components/com_hotspots/install.hotspots.php',
			'administrator/components/com_hotspots/uninstall.hotspots.php',
			'administrator/components/com_hotspots/mootools.php',
			'components/com_hotspots/toolbar.hotspots.html.php',
			'components/com_hotspots/toolbar.hotspots.php',
			'components/com_hotspots/hotspots.css',
			'components/com_hotspots/hotspots.html.php',
			'components/com_hotspots/helpers/customfields.php',
			'components/com_hotspots/views/all/tmpl/default.css',
			'components/com_hotspots/views/all/tmpl/default_old.css',
			'components/com_hotspots/views/all/tmpl/default_old.php',
			'components/com_hotspots/views/all/tmpl/default_slider_old.php',
			'components/com_hotspots/views/all/tmpl/slider.css',
			'components/com_hotspots/views/all/tmpl/slider_old.css',
			'components/com_hotspots/views/all/tmpl/default_slider.php',
			'components/com_hotspots/views/all/tmpl/border_watcher.css',
			'components/com_hotspots/views/hotspot/view.raw.php',
			'components/com_hotspots/views/hotspots/tmpl/userhotspots.php',
			'components/com_hotspots/views/hotspots/tmpl/userhotspots.xml',
			'components/com_hotspots/models/all.php',
			'components/com_hotspots/models/category.php',
			'components/com_hotspots/models/getcords.php',
			'components/com_hotspots/models/gethotspots.php',
			'components/com_hotspots/models/popupmail.php',
			'components/com_hotspots/models/showaddhotspot.php',
			'components/com_hotspots/models/mailsent.php',
			'components/com_hotspots/models/getold.php',
			'components/com_hotspots/views/json/tmpl/address.php',
			'components/com_hotspots/views/json/tmpl/default_description.php',
			'components/com_hotspots/views/json/tmpl/list.php',
			'media/com_hotspots/css/border_watcher.css',
			'media/com_hotspots/js/borderwatcher.js',
			'media/com_hotspots/js/progressbarcontrol_packed.js',
			'media/com_hotspots/js/hsslider.js',
			'media/com_hotspots/images/utils/bg-foot.gif',
			'media/com_hotspots/images/utils/gps.png',
			'media/com_hotspots/images/utils/hr-space.png',
			'media/com_hotspots/images/utils/map_overlay_black.png',
			'media/com_hotspots/images/utils/map_overlay_blue.png',
			'media/com_hotspots/images/utils/map_overlay_close.png',
			'media/com_hotspots/images/utils/map_overlay_red.png',
			'media/com_hotspots/images/utils/map_overlay_white.png',
			'media/com_hotspots/images/utils/map_overlay_yellow.png',
			'media/com_hotspots/images/utils/open.png',
			'media/com_hotspots/images/utils/satellite.png',
			'media/com_hotspots/images/utils/thumb_up_icon.gif',
			'media/com_hotspots/images/utils/arrow-up.png',
			'media/com_hotspots/images/utils/categories.png',
			'media/com_hotspots/images/utils/city-48x48.png',
			'media/com_hotspots/images/utils/city.png',
			'media/com_hotspots/images/utils/dialog_close.png',
			'media/com_hotspots/images/utils/hybrid.png',
			'media/com_hotspots/images/utils/info_off.gif',
			'media/com_hotspots/images/utils/left.gif',
			'media/com_hotspots/images/utils/right.gif',
			'media/com_hotspots/images/utils/terrain.png',
			'media/com_hotspots/images/utils/map.png',
			'media/com_hotspots/images/utils/menu.gif',
			'media/com_hotspots/images/utils/menu_small.gif',
			'media/com_hotspots/images/utils/mini-categories.png',
			'media/com_hotspots/images/utils/Mountain-32x32.png',
			'media/com_hotspots/images/utils/reset-map.png',
			'media/com_hotspots/images/utils/thumb_down_icon.gif',
			'media/com_hotspots/images/utils/117043-matte-blue-and-white-square-icon-business-printer.png',
			'media/com_hotspots/js/utils.js',
			'media/com_hotspots/js/Hotspots.Add.Backend.js',
			'media/com_hotspots/js/Hotspots.Add.js',
			'media/com_hotspots/js/Hotspots.Backend.js',
			'media/com_hotspots/js/Hotspots.Categories.js',
			'media/com_hotspots/js/Hotspots.Helper.js',
			'media/com_hotspots/js/Hotspots.Hotspot.js',
			'media/com_hotspots/js/Hotspots.js',
			'media/com_hotspots/js/Hotspots.Layout.js',
			'media/com_hotspots/js/Hotspots.Layout.Hotspot.js',
			'media/com_hotspots/js/Hotspots.Layout.Hotspots.js',
			'media/com_hotspots/js/Hotspots.Slide.js',
			'media/com_hotspots/js/Hotspots.Tab.js'
		),
		'folders' => array(
			'administrator/components/com_hotspots/assets',
			'administrator/components/com_hotspots/images',
			'administrator/components/com_hotspots/controlcenter',
			'administrator/components/com_hotspots/liveupdate',
			'components/com_hotspots/js',
			'components/com_hotspots/lang',
			'components/com_hotspots/images',
			'components/com_hotspots/captcha',
			'components/com_hotspots/views/all',
			'components/com_hotspots/views/getcords',
			'components/com_hotspots/views/gethotspots',
			'components/com_hotspots/views/popupmail',
			'components/com_hotspots/views/showaddhotspot',
			'components/com_hotspots/views/mailsent',
			'components/com_hotspots/views/getold',
			'components/com_hotspots/views/json',
			'components/com_hotspots/views/mail',
			'media/com_hotspots/captcha',
			'media/com_hotspots/tiles',
			'media/com_hotspots/ccc',
			'media/mod_hotspots/'
		)
	);

	private $installationQueue = array(
		'free' => array(
			// Modules => { (folder) => { (module) => { (position), (published) } }* }*
			'modules' => array(
				'admin' => array(
				),
			),
			'plugins' => array(
				'plg_hotspots_email' => 0,
				'plg_content_hotspotscategory' => 1,
				'plg_hotspotslinks_content' => 1,
				'plg_hotspotslinks_k2' => 0,
				'plg_hotspotslinks_sobipro' => 0
			)
		),
		'pro' => array(
			// Modules => { (folder) => { (module) => { (position), (published) } }* }*
			'modules' => array (
				'site' => array(
					'mod_hotspots_list' => array('left', 0)
				)
			),
			'plugins' => array(
				'plg_installer_hotspots' => 1,
				'plg_community_hotspots' => 0,
				'plg_hotspots_aup' => 0,
				'plg_hotspots_jomsocial' => 0,
				'plg_hotspotslinks_external' => 0,
				'plg_hotspotslinks_flexicontent' => 0,
				'plg_hotspotslinks_community' => 0,
				'plg_hotspotslinks_matukio' => 0,
				'plg_search_hotspots' => 0,
				'plg_content_hotspots' => 0,
				'plg_content_hotspotsanywhere' => 0,
				'plg_content_hotspotsmatukio' => 0,
				'plg_k2_hotspots' => 0
			)
		),
		// Key is the name without the lib_ prefix, value if the library should be autopublished
		'libraries' => array(
			'compojoom' => 1
		)
	);

	private $removeOnInstall = array(
		'modules' => array(
			'admin' => array(
				'mod_ccc_hotspots_icons' => 0,
				'mod_ccc_hotspots_newsfeed' => 0,
				'mod_ccc_hotspots_overview' => 0,
				'mod_ccc_hotspots_update' => 0,
				'mod_hotspots_stats' => 0,
				'mod_hotspots' => 0,
			),
		)
	);

	/**
	 * Executed on install/update/discover
	 *
	 * @param   string                      $type    - the type of th einstallation
	 * @param   JInstallerAdapterComponent  $parent  - the parent JInstaller obeject
	 *
	 * @return boolean - true if everything is OK and we should continue with the installation
	 */
	public function preflight($type, $parent)
	{
	    if ($type === "uninstall") {
	        return true;
        }

		$path = $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/include.php';

		require_once $path;

		// Load the installer files that come with our package - in case the library is already loaded on the page
		// The library can be loaded if updating using liveupdate, or if any plugin on the page is active
		JLoader::register('CompojoomInstaller', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/installer/installer.php', true);
		JLoader::register('CompojoomInstallerCb', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/installer/cb.php', true);
		JLoader::register('CompojoomInstallerAup', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/installer/aup.php', true);
		JLoader::register('CompojoomDatabaseInstaller', $parent->getParent()->getPath('source') . '/libraries/compojoom/libraries/compojoom/database/installer.php', true);

		$this->installer = new CompojoomInstaller($type, $parent, 'com_hotspots');

		if (!$this->installer->allowedInstall())
		{
			return false;
		}

		return true;
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   object  $parent  - the parent class
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		require_once JPATH_LIBRARIES . '/compojoom/include.php';

		$this->installer = new CompojoomInstaller('uninstall', $parent, 'com_hotspots');

		$this->status = new stdClass;
		require_once JPATH_ADMINISTRATOR . '/components/com_hotspots/version.php';

		// Let us install the modules & plugins
		$plugins = $this->installer->uninstallPlugins($this->installationQueue['free']['plugins']);
		$modules = $this->installer->uninstallModules($this->installationQueue['free']['modules']);

		if (HOTSPOTS_PRO)
		{
			$plugins = array_merge($plugins, $this->installer->uninstallPlugins($this->installationQueue['pro']['plugins']));
			$modules = array_merge($modules, $this->installer->uninstallModules($this->installationQueue['pro']['modules']));
		}

		$this->status->plugins = $plugins;
		$this->status->modules = $modules;

		$params = JComponentHelper::getParams('com_hotspots');

		if ($params->get('complete_uninstall', 0))
		{
			$dbInstaller = new CompojoomDatabaseInstaller(
				array(
					'dbinstaller_directory' => JPATH_ADMINISTRATOR . '/components/com_hotspots/sql/xml'
				)
			);
			$dbInstaller->removeSchema();
			$this->droppedTables = true;
		}

		echo $this->displayInfoUninstallation();
	}

	/**
	 * method to run after an install/update/discover method
	 *
	 * @param   string  $type    - the type of installation (install, update etc)
	 * @param   object  $parent  - the parent class
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
        if ($type === "uninstall") {
            return true;
        }

		$path = $parent->getParent()->getPath('source');
		JLoader::discover('HotspotsHelper', $path . '/components/com_hotspots/helpers');
		JLoader::discover('HotspotsModel', $path . '/administrator/components/com_hotspots/models');

		require_once $path . '/administrator/components/com_hotspots/version.php';
		$this->status = new stdClass;

		// Makes sure that the compojoom library tables are created (especially customfields)
		$libraryInstaller = new CompojoomDatabaseInstaller(
			array(
				'dbinstaller_directory' => $path . '/libraries/compojoom/libraries/compojoom/sql/xml'
			)
		);

		$dbInstaller = new CompojoomDatabaseInstaller(
			array(
				'dbinstaller_directory' => $path . '/administrator/components/com_hotspots/sql/xml'
			)
		);

		$libraryInstaller->updateSchema();
		$dbInstaller->updateSchema();

		// Fix everything related to the new categories in Hotspots 5
		HotspotsHelperUpdate::fixCategories();

		// Don't install the "Installer - Hotspots" plugin for Joomla! 3.0.0+
		if (version_compare(JVERSION, '3.0.0', 'ge') && HOTSPOTS_PRO)
		{
			if (isset($this->installationQueue['pro']['plugins']['plg_installer_hotspots']))
			{
				unset($this->installationQueue['pro']['plugins']['plg_installer_hotspots']);
			}
		}

		// Let us cleanup the old modules
		$this->installer->uninstallModules($this->removeOnInstall['modules']);

		// Let us install the modules & plugins
		$plugins = $this->installer->installPlugins($this->installationQueue['free']['plugins']);
		$modules = $this->installer->installModules($this->installationQueue['free']['modules']);
		$libraries = $this->installer->installLibraries($this->installationQueue['libraries']);

		$this->installer->removeFilesAndFolders($this->removeFilesAllVersions);

		if (HOTSPOTS_PRO)
		{
			$plugins = array_merge($plugins, $this->installer->installPlugins($this->installationQueue['pro']['plugins']));
			$modules = array_merge($modules, $this->installer->installModules($this->installationQueue['pro']['modules']));
		}

		$this->status->plugins = $plugins;
		$this->status->modules = $modules;
		$this->status->libraries = $libraries;

		// Install the cb plugin if CB is installed
		$this->status->cb = false;

		if (HOTSPOTS_PRO)
		{
			$this->status->cb = CompojoomInstallerCb::install($parent, 'plug_hotspots');
		}

		$this->status->aup = false;

		if (HOTSPOTS_PRO && JFile::exists(JPATH_ADMINISTRATOR . '/components/com_alphauserpoints/alphauserpoints.php'))
		{
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');

			$rules = JFolder::files($parent->getParent()->getPath('source') . '/components/com_hotspots/assets/aup', '\.xml', true, true);

			foreach ($rules as $rule)
			{
				CompojoomInstallerAup::installRule($rule);
			}

			$this->status->aup = true;
		}

		$this->installer->removeFilesAndFolders($this->removeFilesAllVersions);

		// Try to fix the most critical things
		$updateModel = JModelLegacy::getInstance('Updates', 'HotspotsModel', array('update_component' => 'com_hotspots'));

		if ($updateModel)
		{
			$updateModel->updateConfiguration();
		}

		echo $this->displayInfoInstallation();

		if (strstr(Juri::getInstance()->toString(), 'view=liveupdate&task=install'))
		{
			JFactory::getApplication()->enqueueMessage($this->displayInfoInstallation());
			JFactory::getApplication()->redirect('index.php?option=com_hotspots');
		}
	}

	/**
	 * Ads CSS to the page
	 *
	 * @return string
	 */
	public function addCss()
	{
		$css = '<style type="text/css">
					.compojoom-info {
						background-color: #D9EDF7;
					    border-color: #BCE8F1;
					    color: #3A87AD;
					    border-radius: 4px 4px 4px 4px;
					    padding: 8px 35px 8px 14px;
					    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
					    margin-bottom: 18px;
					}

				</style>
				';

		return $css;
	}

	/**
	 * Output installation info to the user
	 *
	 * @return string
	 */
	private function displayInfoInstallation()
	{
		$html[] = $this->addCSS();
		$html[] = '<div class="compojoom-info alert alert-info">'
			. JText::sprintf('COM_HOTSPOTS_INSTALLATION_SUCCESS', (HOTSPOTS_PRO ? 'Professional' : 'Core'))
			. '</div>';

		if (!HOTSPOTS_PRO)
		{
			$html[] .= '<p>' . JText::sprintf('COM_HOTSPOTS_UPGRADE_TO_PRO', 'https://compojoom.com/joomla-extensions/hotspots') . '</p>';
		}

		$html[] = CompojoomHtmlTemplates::renderSocialMediaInfo();

		$html[] = '<img src="' . JURI::root() . 'media/com_hotspots/images/utils/logo.jpg "/>';
		$html[] = '<p>' . JText::_('COM_HOTSPOTS_INSTALLATION_DOC_FORUMS_FIND');
		$html[] = ' <a href="https://compojoom.com" target="_blank">compojoom.com</a>';
		$html[] = '<br/>';
		$html[] = '<br/>';
		$html[] = '<strong>';
		$html[] = JText::_('COM_HOTSPOTS_INSTALLATION_QUICK_INSTRUCTIONS') . ' <br/>';

		$html[] = '</strong></p>';
		$html[] = '<div>';
		$html[] = '<ol>';
		$html[] = '<li>';
		$html[] = JText::_('COM_HOTSPOTS_INSTALLATION_CREATE_A_CATEGORY');
		$html[] = '(<a href="' . JRoute::_('index.php?option=com_categories&extension=com_hotspots') . '"
			    target="_blank">' . JText::_('COM_HOTSPOTS_INSTALLATION_CLICK_HERE') . ' </a>)';
		$html[] = '</li>';
		$html[] = '<li>';
		$html[] = JText::_('COM_HOTSPOTS_INSTALLATION_CREATE_A_HOTSPOT') . '(<a
			href="' . JRoute::_('index.php?option=com_hotspots&task=hotspot.edit') . '"
			target="_blank">' . JText::_('COM_HOTSPOTS_INSTALLATION_CLICK_HERE') . '</a>)';
		$html[] = '</li>';
		$html[] = '<li>';
		$html[] = JText::_('COM_HOTSPOTS_INSTALLATION_CREATE_A_MENU_LINK') . '(<a
			href="' . JRoute::_('index.php?option=com_menus&view=items&menutype=mainmenu') . '"
			target="_blank">' . JText::_('COM_HOTSPOTS_INSTALLATION_CLICK_HERE') . '</a>)';
		$html[] = '</li>';
		$html[] = '</ol>';
		$html[] = '</div>';

		if ($this->status->libraries)
		{
			$html[] = $this->installer->renderLibraryInfoInstall($this->status->libraries);
		}

		if ($this->status->plugins)
		{
			$html[] = $this->installer->renderPluginInfoInstall($this->status->plugins);
		}

		if ($this->status->modules)
		{
			$html[] = $this->installer->renderModuleInfoInstall($this->status->modules);
		}

		if ($this->status->cb)
		{
			$html[] = '<br /><span style="color:green;">Community builder detected. CB plugin installed!</span>';
		}

		if ($this->status->aup)
		{
			$html[] = '<br /><span style="color:green;">Alpha user points detected. AUP rules installed!</span>';
		}

		return implode('', $html);
	}

	/**
	 * Displays uninstall info
	 *
	 * @return string
	 */
	public function displayInfoUninstallation()
	{
		$html[] = '<div class="compojoom-info alert alert-info">Hotspots is now removed from your system</div>';

		if ($this->droppedTables)
		{
			$html[] = '<p>The option uninstall complete mode was set to true. Database tables were removed</p>';
		}
		else
		{
			$html[] = '<p>The option uninstall complete mode was set to false. The database tables were not removed.</p>';
		}

		$html[] = $this->installer->renderPluginInfoUninstall($this->status->plugins);
		$html[] = $this->installer->renderModuleInfoUninstall($this->status->modules);

		$html[] = CompojoomHtmlTemplates::renderSocialMediaInfo();

		return implode('', $html);
	}
}
