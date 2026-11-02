<?php

/**
 * @package    Hotspots
 * @copyright  (C) 2008-2023 compojoom.com - Daniel Dimitrov, (C) 2026 TLWebdesign
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Install script for com_hotspots.
 *
 * Replaces Compojoom's script (2026-11-02). The database tables come from sql/install.mysql.utf8.sql and
 * sql/updates/mysql. Plugins and modules are installed by the package (pkg_hotspots), not by this script.
 */
class Com_HotspotsInstallerScript extends InstallerScript
{
    /**
     * @var string
     */
    protected $extension = 'com_hotspots';

    /**
     * @var string
     */
    protected $minimumJoomla = '5.4.0';

    /**
     * @var string
     */
    protected $minimumPhp = '8.1.0';

    /**
     * Files removed from Hotspots since 6.0.13.
     *
     * @var string[]
     */
    protected $deleteFiles = [
        '/administrator/components/com_hotspots/controllers/jed.php',
        '/administrator/components/com_hotspots/models/stats.php',
        '/administrator/components/com_hotspots/models/fields/hotspotsk2extrafields.php',
        '/components/com_hotspots/jomsocial_rule.xml',
        '/components/com_hotspots/helpers/update.php',
        '/libraries/compojoom/controller/jed.php',
        '/libraries/compojoom/controller/stats.php',
        '/libraries/compojoom/model/stats.php',
    ];

    /**
     * Folders removed from Hotspots since 6.0.13.
     *
     * @var string[]
     */
    protected $deleteFolders = [
        '/administrator/components/com_hotspots/sql/xml',
        '/components/com_hotspots/assets/aup',
        '/libraries/compojoom/database',
        '/libraries/compojoom/geolocation',
        '/libraries/compojoom/installer',
        '/libraries/compojoom/layouts/jed',
        '/libraries/compojoom/layouts/update',
        '/libraries/compojoom/mvc',
        '/libraries/compojoom/sql',
        '/libraries/compojoom/testing',
    ];

    /**
     * Runs after install and update.
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

        $this->removeFiles();
        $this->setConfigurationDefaults();

        return true;
    }

    /**
     * Drops the Hotspots tables only when "complete uninstall" is set in the options, as Compojoom's script did.
     * The shared #__compojoom_* tables stay; only the Hotspots rows are removed from them.
     *
     * @param   InstallerAdapter  $parent  The installer
     *
     * @return  boolean
     */
    public function uninstall($parent)
    {
        if (!ComponentHelper::getParams($this->extension)->get('complete_uninstall', 0)) {
            return true;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        foreach (['#__hotspots_marker', '#__hotspots_kmls', '#__hotspots_mappings'] as $table) {
            $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName($table))->execute();
        }

        $tables = $db->getTableList();
        $prefix = $db->getPrefix();

        if (\in_array($prefix . 'compojoom_multimedia', $tables, true)) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__compojoom_multimedia'))
                ->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_hotspots.hotspot'));
            $db->setQuery($query)->execute();
        }

        if (\in_array($prefix . 'compojoom_customfields', $tables, true)) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__compojoom_customfields'))
                ->where($db->quoteName('component') . ' = ' . $db->quote('com_hotspots.hotspot'));
            $db->setQuery($query)->execute();
        }

        return true;
    }

    /**
     * Adds the defaults from config.xml for options that are not stored yet, so a fresh install works
     * without saving the options first (what Compojoom's updateConfiguration() did). Stored values win.
     *
     * @return  void
     */
    private function setConfigurationDefaults(): void
    {
        $configFile = JPATH_ADMINISTRATOR . '/components/' . $this->extension . '/config.xml';

        if (!is_file($configFile)) {
            return;
        }

        $xml = simplexml_load_file($configFile);

        if (!$xml) {
            return;
        }

        $defaults = [];

        foreach ($xml->xpath('//field[@name]') as $field) {
            if (!isset($field['default'])) {
                continue;
            }

            $groups = [];

            foreach ($field->xpath('ancestor::fields[@name]') as $fields) {
                $groups[] = (string) $fields['name'];
            }

            $target = &$defaults;

            foreach ($groups as $group) {
                $target[$group] = $target[$group] ?? [];
                $target         = &$target[$group];
            }

            $target[(string) $field['name']] = (string) $field['default'];
            unset($target);
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['extension_id', 'params']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('element') . ' = ' . $db->quote($this->extension));
        $row = $db->setQuery($query)->loadObject();

        if (!$row) {
            return;
        }

        $stored = (new Registry($row->params))->toArray();
        $params = array_replace_recursive($defaults, $stored);

        // Hotspots 4 stored the ordering as "name ..."; the column is "title" since Hotspots 5.
        if (isset($params['hotspots_order']) && \is_string($params['hotspots_order'])) {
            $params['hotspots_order'] = str_replace('name', 'title', $params['hotspots_order']);
        }

        if ($params === $stored) {
            return;
        }

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
            ->where($db->quoteName('extension_id') . ' = ' . (int) $row->extension_id);
        $db->setQuery($query)->execute();
    }
}
