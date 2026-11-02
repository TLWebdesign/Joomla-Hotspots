<?php

/**
 * @package    Hotspots
 * @copyright  (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2023
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
 * Install script for com_hotspots 7.
 *
 * The database tables come from sql/install.mysql.utf8.sql and sql/updates/mysql. Plugins and modules are installed by the package (pkg_hotspots), not by this script.
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
     * Files removed since 6.0.13: the Joomla 3 style code of 6.x, replaced by services/, src/, tmpl/ and forms/ in 7.0.
     * Joomla removes the top level folders of the old manifest on update; this list also covers sites that were
     * installed in another way and the files inside folders that stay.
     *
     * @var string[]
     */
    protected $deleteFiles = [
        // 6.0.13
        '/administrator/components/com_hotspots/controllers/jed.php',
        '/administrator/components/com_hotspots/models/stats.php',
        '/administrator/components/com_hotspots/models/fields/hotspotsk2extrafields.php',
        '/components/com_hotspots/jomsocial_rule.xml',
        '/components/com_hotspots/helpers/update.php',
        '/libraries/compojoom/controller/jed.php',
        '/libraries/compojoom/controller/stats.php',
        '/libraries/compojoom/model/stats.php',
        // 6.1.0 (replaced by the namespaced code in 7.0)
        '/administrator/components/com_hotspots/CHANGELOG.php',
        '/administrator/components/com_hotspots/controller.php',
        '/administrator/components/com_hotspots/hotspots.php',
        '/administrator/components/com_hotspots/version.php',
        '/components/com_hotspots/controller.php',
        '/components/com_hotspots/hmvc.php',
        '/components/com_hotspots/hotspots.php',
        '/components/com_hotspots/router.php',
        '/modules/mod_hotspots_list/helper.php',
        '/modules/mod_hotspots_list/mod_hotspots_list.php',
        '/media/com_hotspots/js/core.js',
        '/media/com_hotspots/js/fixes.js',
        '/media/com_hotspots/js/sandbox.js',
        '/media/com_hotspots/js/fields/hotspotsLink.js',
        '/media/com_hotspots/js/fields/markerImage.js',
        '/media/com_hotspots/css/hotspots.css',
        '/media/com_hotspots/css/hotspots-j2.5-hacks.css',
    ];

    /**
     * Folders removed since 6.0.13. 7.0 must not install folders with these names.
     *
     * @var string[]
     */
    protected $deleteFolders = [
        // 6.0.13
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
        // 6.1.0 (replaced by the namespaced code in 7.0)
        '/administrator/components/com_hotspots/controllers',
        '/administrator/components/com_hotspots/elements',
        '/administrator/components/com_hotspots/helpers',
        '/administrator/components/com_hotspots/libraries',
        '/administrator/components/com_hotspots/models',
        '/administrator/components/com_hotspots/tables',
        '/administrator/components/com_hotspots/views',
        '/components/com_hotspots/api',
        '/components/com_hotspots/assets',
        '/components/com_hotspots/controllers',
        '/components/com_hotspots/helpers',
        '/components/com_hotspots/includes',
        '/components/com_hotspots/models',
        '/components/com_hotspots/views',
        // Generated JS config and concatenated scripts (CompojoomHtml::script), the MooTools map and the
        // public upload folder of the old KML import wizard (the new import uses Joomla's tmp folder).
        '/media/com_hotspots/cache',
        '/media/com_hotspots/js/moo',
        '/media/com_hotspots/import',
        '/media/com_hotspots/js/config',
        '/media/com_hotspots/js/helpers',
        '/media/com_hotspots/js/lazy',
        '/media/com_hotspots/js/libraries',
        '/media/com_hotspots/js/lightface',
        '/media/com_hotspots/js/modules',
        '/media/com_hotspots/js/spin',
        '/media/com_hotspots/js/views',
        '/media/com_hotspots/backend/js',
        '/media/com_hotspots/css/fields',
        '/modules/mod_hotspots_list/language',
        '/modules/mod_hotspots_list/media',
    ];

    /**
     * Options of removed features, deleted from the stored component options.
     *
     * @var string[]
     */
    private const OBSOLETE_OPTIONS = [
        'ads', 'category_ordering', 'downloadid', 'emulate_bootstrap', 'jed', 'library_load', 'minify', 'update_stats',
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
        $this->moveRowsFromCompojoomTables();
        $this->warnAboutUnmigratedImages();

        return true;
    }

    /**
     * Drops the Hotspots tables only when "complete uninstall" is set in the options.
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

        foreach (['#__hotspots_marker', '#__hotspots_kmls', '#__hotspots_mappings', '#__hotspots_customfields',
            '#__hotspots_customfields_cats', '#__hotspots_multimedia'] as $table) {
            $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName($table))->execute();
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
        $params = array_replace_recursive($defaults, array_diff_key($stored, array_flip(self::OBSOLETE_OPTIONS)));

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

    /**
     * Up to 6.x the custom fields and images were in the #__compojoom_* tables of lib_compojoom, which other
     * Compojoom extensions can share. 7.0 has its own tables: copy the Hotspots rows over (once, while the new
     * table is empty), remove them from the old tables, and drop an old table only when nothing is left in it.
     *
     * @return  void
     */
    private function moveRowsFromCompojoomTables(): void
    {
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $prefix = $db->getPrefix();
        $tables = $db->getTableList();
        $has    = static fn (string $name): bool => \in_array($prefix . $name, $tables, true);
        $empty  = static fn (string $table): bool => !(int) $db->setQuery('SELECT COUNT(*) FROM ' . $db->quoteName($table))->loadResult();
        $moved  = [];

        $db->transactionStart();

        try {
            if ($has('compojoom_customfields') && $has('hotspots_customfields') && $empty('#__hotspots_customfields')) {
                $columns = '`id`, `title`, `slug`, `component`, `show`, `type`, `options`, `default`, `allow_empty`, `params`, '
                    . '`enabled`, `filter`, `ordering`, `created_by`, `created_on`, `modified_by`, `modified_on`';
                $db->setQuery(
                    'INSERT INTO ' . $db->quoteName('#__hotspots_customfields') . " ($columns) SELECT $columns FROM "
                    . $db->quoteName('#__compojoom_customfields') . ' WHERE `component` = ' . $db->quote('com_hotspots.hotspot')
                )->execute();
                $moved[] = $db->getAffectedRows() . ' custom fields';

                if ($has('compojoom_customfields_cats')) {
                    $db->setQuery(
                        'INSERT INTO ' . $db->quoteName('#__hotspots_customfields_cats') . ' (`customfield_id`, `catid`)'
                        . ' SELECT c.`compojoom_customfields_id`, c.`catid` FROM ' . $db->quoteName('#__compojoom_customfields_cats') . ' AS c'
                        . ' INNER JOIN ' . $db->quoteName('#__hotspots_customfields') . ' AS f ON f.`id` = c.`compojoom_customfields_id`'
                    )->execute();
                    $db->setQuery(
                        'DELETE c FROM ' . $db->quoteName('#__compojoom_customfields_cats') . ' AS c'
                        . ' INNER JOIN ' . $db->quoteName('#__hotspots_customfields') . ' AS f ON f.`id` = c.`compojoom_customfields_id`'
                    )->execute();
                }

                $db->setQuery(
                    'DELETE FROM ' . $db->quoteName('#__compojoom_customfields') . ' WHERE `component` = ' . $db->quote('com_hotspots.hotspot')
                )->execute();
            }

            if ($has('compojoom_multimedia') && $has('hotspots_multimedia') && $empty('#__hotspots_multimedia')) {
                $columns = '`item_id`, `type_alias`, `title`, `description`, `mangled_filename`, `mime_type`, `origin`, '
                    . '`created_on`, `created_by`, `enabled`, `params`';
                $db->setQuery(
                    'INSERT INTO ' . $db->quoteName('#__hotspots_multimedia') . " (`id`, $columns) SELECT `compojoom_multimedia_id`, $columns FROM "
                    . $db->quoteName('#__compojoom_multimedia') . ' WHERE `type_alias` = ' . $db->quote('com_hotspots.hotspot')
                )->execute();
                $moved[] = $db->getAffectedRows() . ' images';

                $db->setQuery(
                    'DELETE FROM ' . $db->quoteName('#__compojoom_multimedia') . ' WHERE `type_alias` = ' . $db->quote('com_hotspots.hotspot')
                )->execute();
            }

            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();
            Factory::getApplication()->enqueueMessage('Hotspots: could not move the custom fields and images to the new tables: ' . $e->getMessage(), 'error');

            return;
        }

        // Drop the old tables once no other extension has rows in them (DROP TABLE ends a transaction, so after the commit).
        foreach (['compojoom_customfields_cats', 'compojoom_customfields', 'compojoom_multimedia'] as $name) {
            if ($has($name) && $empty('#__' . $name) && ($name !== 'compojoom_customfields_cats' || !$has('compojoom_customfields') || $empty('#__compojoom_customfields'))) {
                $db->setQuery('DROP TABLE ' . $db->quoteName('#__' . $name))->execute();
            }
        }

        if ($moved) {
            Factory::getApplication()->enqueueMessage('Hotspots: moved ' . implode(' and ', $moved) . ' to the Hotspots tables.', 'message');
        }
    }

    /**
     * Hotspots 5 moved the images of Hotspots 4 (media/com_hotspots/images/hotspots) on the dashboard, one by one.
     * 7.0 no longer has that step, so warn when a site still has images waiting for it.
     *
     * @return  void
     */
    private function warnAboutUnmigratedImages(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!\in_array($db->getPrefix() . 'hotspots_multimedia', $db->getTableList(), true)) {
            return;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__hotspots_multimedia'))
            ->where($db->quoteName('origin') . ' LIKE ' . $db->quote('com_hotspots.migrate%'));

        $count = (int) $db->setQuery($query)->loadResult();

        if ($count > 0) {
            Factory::getApplication()->enqueueMessage(
                \sprintf(
                    'Hotspots: %d images from Hotspots 4 were never moved to the Hotspots 5 storage (#__hotspots_multimedia rows with origin "com_hotspots.migrate"). Hotspots 7 cannot move them; they stay in media/com_hotspots/images/hotspots.',
                    $count
                ),
                'warning'
            );
        }
    }
}
