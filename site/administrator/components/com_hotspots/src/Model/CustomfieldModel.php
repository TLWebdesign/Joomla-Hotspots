<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\ParameterType;

\defined('_JEXEC') or die;

/**
 * One custom field; its categories ($item->catid, an array) are kept in #__compojoom_customfields_cats.
 */
class CustomfieldModel extends AdminModel
{
    public $typeAlias = 'com_hotspots.customfield';

    protected $text_prefix = 'COM_HOTSPOTS_CUSTOMFIELDS';

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_hotspots.customfield', 'customfield', ['control' => 'jform', 'load_data' => $loadData]);

        return $form ?: false;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_hotspots.edit.customfield.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_hotspots.customfield', $data);

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && $item->id) {
            $db    = $this->getDatabase();
            $id    = (int) $item->id;
            $query = $db->getQuery(true)
                ->select($db->quoteName('catid'))
                ->from($db->quoteName('#__compojoom_customfields_cats'))
                ->where($db->quoteName('compojoom_customfields_id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);

            $item->catid = array_map('intval', $db->setQuery($query)->loadColumn());
        } elseif ($item) {
            $item->catid = [];
        }

        return $item;
    }

    public function save($data)
    {
        $catids = array_values(array_filter(array_map('intval', (array) ($data['catid'] ?? []))));
        unset($data['catid']);

        if (!parent::save($data)) {
            return false;
        }

        $id = (int) $this->getState($this->getName() . '.id');
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__compojoom_customfields_cats'))
            ->where($db->quoteName('compojoom_customfields_id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        if (($data['show'] ?? 'all') === 'category') {
            foreach ($catids as $catid) {
                $row = (object) ['compojoom_customfields_id' => $id, 'catid' => $catid];
                $db->insertObject('#__compojoom_customfields_cats', $row);
            }
        }

        return true;
    }

    protected function prepareTable($table)
    {
        if (empty($table->id)) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('MAX(' . $db->quoteName('ordering') . ')')
                ->from($db->quoteName('#__compojoom_customfields'));

            $table->ordering = (int) $db->setQuery($query)->loadResult() + 1;
        }
    }

    protected function canDelete($record)
    {
        return $this->getCurrentUser()->authorise('core.delete', 'com_hotspots');
    }

    protected function canEditState($record)
    {
        return $this->getCurrentUser()->authorise('core.edit.state', 'com_hotspots');
    }

    /**
     * Ordering groups for drag-and-drop: all fields of the component.
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [$db->quoteName('component') . ' = ' . $db->quote((string) $table->component)];
    }
}
