<?php

/**
 * @package    Hotspots
 * @copyright  (C) 2026 TLWebdesign
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\DatabaseInterface;

/**
 * Install script for pkg_hotspots.
 */
class Pkg_HotspotsInstallerScript extends InstallerScript
{
    /**
     * @var string
     */
    protected $minimumJoomla = '5.4.0';

    /**
     * @var string
     */
    protected $minimumPhp = '8.1.0';

    /**
     * Plugins enabled on a fresh install (as in Compojoom's script).
     *
     * @var array<string, string>  element => folder
     */
    private array $enableOnInstall = [
        'hotspotscategory' => 'content',
        'content'          => 'hotspotslinks',
    ];

    /**
     * Compojoom plugins that are no longer part of Hotspots (removed 2026-11-02). They are uninstalled on
     * update, but only when they are disabled.
     *
     * @var array<int, array{string, string}>  [folder, element]
     */
    private array $obsoletePlugins = [
        ['community', 'hotspots'],
        ['k2', 'hotspots'],
        ['search', 'hotspots'],
        ['hotspots', 'aup'],
        ['hotspots', 'jomsocial'],
        ['hotspots', 'email'],
        ['content', 'hotspotsanywhere'],
        ['content', 'hotspotsmatukio'],
        ['hotspotslinks', 'community'],
        ['hotspotslinks', 'flexicontent'],
        ['hotspotslinks', 'k2'],
        ['hotspotslinks', 'matukio'],
        ['hotspotslinks', 'sobipro'],
    ];

    /**
     * Elements of the Hotspots extensions whose compojoom.com update sites are removed.
     *
     * @var string[]
     */
    private array $ownElements = [
        'pkg_hotspots', 'com_hotspots', 'compojoom', 'mod_hotspots_list', 'hotspots', 'hotspotscategory',
        'content', 'external', 'com_hotspots-fr-FR',
    ];

    /**
     * Runs after the package and all its extensions are installed or updated.
     *
     * @param   string            $type    install, update or discover_install
     * @param   InstallerAdapter  $parent  The installer
     *
     * @return  boolean
     */
    public function postflight($type, $parent)
    {
        if ($type === 'uninstall') {
            return true;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if ($type === 'install' || $type === 'discover_install') {
            foreach ($this->enableOnInstall as $element => $folder) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('enabled') . ' = 1')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
                    ->where($db->quoteName('element') . ' = ' . $db->quote($element));
                $db->setQuery($query)->execute();
            }
        }

        $this->removeCompojoomUpdateSites($db);

        if ($type === 'update') {
            $this->uninstallObsoletePlugins($db);
            $this->releaseCompojoomLibrary($db);
        }

        return true;
    }

    /**
     * Removes the compojoom.com update sites of the Hotspots extensions; the server no longer exists.
     */
    private function removeCompojoomUpdateSites(DatabaseInterface $db): void
    {
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('us.update_site_id'))
            ->from($db->quoteName('#__update_sites', 'us'))
            ->join('INNER', $db->quoteName('#__update_sites_extensions', 'ue'), 'ue.update_site_id = us.update_site_id')
            ->join('INNER', $db->quoteName('#__extensions', 'e'), 'e.extension_id = ue.extension_id')
            ->where($db->quoteName('us.location') . ' LIKE ' . $db->quote('%compojoom.com%'))
            ->whereIn($db->quoteName('e.element'), $this->ownElements, \Joomla\Database\ParameterType::STRING);
        $ids = $db->setQuery($query)->loadColumn();

        if (!$ids) {
            return;
        }

        $ids = array_map('intval', $ids);

        foreach (['#__updates', '#__update_sites_extensions', '#__update_sites'] as $table) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName($table))
                ->whereIn($db->quoteName('update_site_id'), $ids);
            $db->setQuery($query)->execute();
        }

        Factory::getApplication()->enqueueMessage('Hotspots: removed the compojoom.com update site.', 'message');
    }

    /**
     * Hotspots 7 no longer uses lib_compojoom. It is not uninstalled here, because other Compojoom
     * extensions may need it, but it is detached from the package so it can be uninstalled by hand
     * (the package blocks uninstalling its own extensions).
     */
    private function releaseCompojoomLibrary(DatabaseInterface $db): void
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName(['extension_id', 'package_id']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('library'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('compojoom'));
        $library = $db->setQuery($query)->loadObject();

        if (!$library) {
            return;
        }

        if ((int) $library->package_id !== 0) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('package_id') . ' = 0')
                ->where($db->quoteName('extension_id') . ' = ' . (int) $library->extension_id);
            $db->setQuery($query)->execute();
        }

        Factory::getApplication()->enqueueMessage(
            'Hotspots no longer needs the Compojoom library (lib_compojoom). If no other Compojoom extension is installed, uninstall it under Extensions: Manage.',
            'notice'
        );
    }

    /**
     * Uninstalls the Compojoom plugins that are no longer part of Hotspots, if they are disabled.
     */
    private function uninstallObsoletePlugins(DatabaseInterface $db): void
    {
        $removed = [];
        $kept    = [];

        foreach ($this->obsoletePlugins as [$folder, $element]) {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['extension_id', 'enabled']))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element));
            $plugin = $db->setQuery($query)->loadObject();

            if (!$plugin) {
                continue;
            }

            if ((int) $plugin->enabled === 1) {
                $kept[] = "plg_{$folder}_{$element}";
                continue;
            }

            // A separate installer instance, so the running package install is not affected.
            $installer = new Installer();
            $installer->setDatabase($db);

            if ($installer->uninstall('plugin', (int) $plugin->extension_id)) {
                $removed[] = "plg_{$folder}_{$element}";
            }
        }

        $app = Factory::getApplication();

        if ($removed) {
            $app->enqueueMessage('Hotspots: uninstalled plugins that are no longer part of Hotspots: ' . implode(', ', $removed) . '.', 'message');
        }

        if ($kept) {
            $app->enqueueMessage('Hotspots: these plugins are no longer part of Hotspots but are enabled, so they were not uninstalled: ' . implode(', ', $kept) . '.', 'warning');
        }
    }
}
