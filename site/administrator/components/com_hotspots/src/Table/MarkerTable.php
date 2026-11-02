<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Table;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

\defined('_JEXEC') or die;

/**
 * A marker (#__hotspots_marker). Assets ("com_hotspots.hotspot.<id>") are only kept when the marker
 * has its own permission rules.
 */
class MarkerTable extends Table
{
    /**
     * Columns that are NOT NULL without a default; strict SQL mode refuses an INSERT that leaves them out.
     */
    private const TEXT_COLUMNS = [
        'params', 'description', 'description_small', 'street', 'plz', 'town', 'country',
        'administrative_area_level_1', 'created_by_alias', 'alias',
    ];

    private const INT_COLUMNS = ['asset_id', 'created_by', 'modified_by'];

    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_hotspots.hotspot';

        parent::__construct('#__hotspots_marker', 'id', $db, $dispatcher);

        $this->setColumnAlias('published', 'state');
        $this->_trackAssets = false;
    }

    /**
     * Splits hotspotText at the read more line into description_small and description, and turns
     * params, customfields and rules from arrays into their stored form.
     */
    public function bind($array, $ignore = '')
    {
        if (\is_object($array)) {
            $array = get_object_vars($array);
        }

        if (isset($array['hotspotText'])) {
            $parts = preg_split('#<hr\s+id=("|\')system-readmore("|\')\s*/*>#i', (string) $array['hotspotText'], 2);

            $array['description_small'] = $parts[0];
            $array['description']       = $parts[1] ?? '';
        }

        foreach (['params', 'customfields'] as $column) {
            if (isset($array[$column]) && \is_array($array[$column])) {
                $array[$column] = (string) new Registry($array[$column]);
            }
        }

        // Track assets only when at least one rule is set for this marker.
        $this->_trackAssets = false;

        if (isset($array['rules']) && \is_array($array['rules'])) {
            foreach ($array['rules'] as $rule) {
                if (\is_array($rule) && \count($rule)) {
                    $this->_trackAssets = true;
                    break;
                }
            }

            if ($this->_trackAssets) {
                $this->setRules(new Rules($array['rules']));
            }
        }

        if (!$this->_trackAssets) {
            $array['asset_id'] = 0;
        }

        return parent::bind($array, $ignore);
    }

    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (trim((string) $this->title) === '') {
            $this->setError(Text::_('COM_HOTSPOTS_WARNING_PROVIDE_VALID_TITLE'));

            return false;
        }

        $this->generateAlias();

        foreach (self::TEXT_COLUMNS as $column) {
            $this->$column ??= '';
        }

        foreach (self::INT_COLUMNS as $column) {
            if ($this->$column === null || $this->$column === '') {
                $this->$column = 0;
            }
        }

        foreach (['publish_up', 'publish_down', 'created', 'modified'] as $column) {
            if (!$this->$column || str_starts_with((string) $this->$column, '0000')) {
                $this->$column = null;
            }
        }

        // The publish down date must not be before publish up.
        if ($this->publish_up !== null && $this->publish_down !== null && $this->publish_down < $this->publish_up) {
            [$this->publish_up, $this->publish_down] = [$this->publish_down, $this->publish_up];
        }

        return true;
    }

    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()?->id;

        $this->modified    = $date;
        $this->modified_by = $userId;

        // Created and created_by can be set by the form, so only fill them in when empty.
        if (!$this->created) {
            $this->created = $date;
        }

        if (empty($this->created_by)) {
            $this->created_by = $userId;
        }

        // The alias must be unique within the category.
        $table = new self($this->getDatabase(), $this->getDispatcher());

        if ($table->load(['alias' => $this->alias, 'catid' => $this->catid]) && ((int) $table->id !== (int) $this->id || !$this->id)) {
            $this->setError(Text::_('COM_HOTSPOTS_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        $result = parent::store($updateNulls);

        if ($result && !$this->_trackAssets) {
            $this->deleteAsset();
        }

        return $result;
    }

    /**
     * Makes the alias URL safe, or creates one from the title that is unique in the category.
     */
    public function generateAlias(): string
    {
        if ($this->alias === null || trim((string) $this->alias) === '') {
            $this->alias = Factory::getApplication()->get('unicodeslugs') == 1
                ? OutputFilter::stringURLUnicodeSlug((string) $this->title)
                : OutputFilter::stringURLSafe((string) $this->title);

            if (!$this->id) {
                $table = new self($this->getDatabase(), $this->getDispatcher());

                while ($table->load(['alias' => $this->alias, 'catid' => $this->catid])) {
                    $this->alias = StringHelper::increment($this->alias, 'dash');
                }
            }
        } else {
            $this->alias = ApplicationHelper::stringURLSafe((string) $this->alias, (string) $this->language);
        }

        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return $this->alias;
    }

    protected function _getAssetName()
    {
        return 'com_hotspots.hotspot.' . (int) $this->id;
    }

    protected function _getAssetTitle()
    {
        return (string) $this->title;
    }

    protected function _getAssetParentId(?Table $table = null, $id = null)
    {
        $asset = new \Joomla\CMS\Table\Asset($this->getDatabase(), $this->getDispatcher());

        return $asset->loadByName('com_hotspots') ? (int) $asset->id : parent::_getAssetParentId($table, $id);
    }

    /**
     * Removes the asset of a marker that no longer has its own rules.
     */
    private function deleteAsset(): void
    {
        $asset = new \Joomla\CMS\Table\Asset($this->getDatabase(), $this->getDispatcher());

        if ($this->id && $asset->loadByName($this->_getAssetName())) {
            $asset->delete();
        }
    }
}
