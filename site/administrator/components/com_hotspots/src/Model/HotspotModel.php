<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;

\defined('_JEXEC') or die;

/**
 * One marker: edit form, save, publish, delete and batch.
 */
class HotspotModel extends AdminModel
{
    public $typeAlias = 'com_hotspots.hotspot';

    protected $text_prefix = 'COM_HOTSPOTS';

    /**
     * Batch: copy/move to a category, access level and language.
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
    ];

    public function getTable($name = 'Marker', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_hotspots.marker', 'marker', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        // The custom fields of the category, so they are validated and filtered on save.
        $catid  = (int) ($data['catid'] ?? $form->getValue('catid'));
        $fields = CustomfieldsHelper::getFields($catid);

        if ($fields) {
            $form->load(CustomfieldsHelper::formXml($fields));
        }

        $id = (int) ($data['id'] ?? $form->getValue('id'));

        // Existing markers can only be moved to categories the user may edit in, new ones only created where allowed.
        $form->setFieldAttribute('catid', 'action', $id ? 'core.edit' : 'core.create');

        if (!$this->canEditState((object) ['id' => $id, 'catid' => $catid, 'created_by' => (int) ($data['created_by'] ?? $form->getValue('created_by'))])) {
            foreach (['state', 'publish_up', 'publish_down'] as $field) {
                $form->setFieldAttribute($field, 'disabled', 'true');
                // The controller already checked that this record may be edited.
                $form->setFieldAttribute($field, 'filter', 'unset');
            }
        }

        return $form;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if (!$item) {
            return $item;
        }

        $description       = trim((string) ($item->description ?? ''));
        $item->hotspotText = (string) ($item->description_small ?? '')
            . ($description !== '' ? '<hr id="system-readmore">' . $item->description : '');

        if (isset($item->customfields) && !\is_array($item->customfields)) {
            $item->customfields = (new Registry($item->customfields))->toArray();
        }

        return $item;
    }

    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_hotspots.edit.hotspot.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // New marker: preselect the category of the list filter.
            if (!$data->id) {
                $filter = $app->getUserState('com_hotspots.hotspots.filter.category_id');

                if (is_numeric($filter) && (int) $filter) {
                    $data->catid = (int) $filter;
                }
            }
        }

        $this->preprocessData('com_hotspots.hotspot', $data);

        return $data;
    }

    protected function prepareTable($table)
    {
        // Publishing without a start date: start now.
        if ((int) $table->state === 1 && empty($table->publish_up)) {
            $table->publish_up = Factory::getDate()->toSql();
        }
    }

    /**
     * Moves the uploaded images of the marker from the temporary upload folder and stores their title
     * and description. $files are the file names from the upload field, $meta the title/description per file.
     */
    public function saveMultimedia(int $markerId, array $files, array $meta = []): void
    {
        if (!$markerId || !$files) {
            return;
        }

        /** @var MultimediaModel $multimedia */
        $multimedia = $this->getMVCFactory()->createModel('Multimedia', 'Administrator', ['ignore_request' => true]);
        $multimedia->uploadPermanent($markerId, $files, $meta);
    }

    /**
     * Deletes the markers and their images.
     */
    public function delete(&$pks)
    {
        $pks = array_filter(array_map('intval', (array) $pks));

        if (!parent::delete($pks)) {
            return false;
        }

        /** @var MultimediaModel $multimedia */
        $multimedia = $this->getMVCFactory()->createModel('Multimedia', 'Administrator', ['ignore_request' => true]);

        // AdminModel::delete() removes the ids it could not delete from $pks.
        foreach ($pks as $pk) {
            $multimedia->deleteAll((int) $pk);
        }

        return true;
    }

    protected function canDelete($record)
    {
        if (empty($record->id)) {
            return false;
        }

        return SecurityHelper::authorise('delete', $record, $this->getCurrentUser());
    }

    protected function canEditState($record)
    {
        return SecurityHelper::authorise('edit.state', !empty($record->catid) ? $record : null, $this->getCurrentUser());
    }

    /**
     * Batch copy/move: the user needs create permission in the target category.
     */
    protected function batchMove($value, $pks, $contexts)
    {
        return $this->canCreateIn((int) $value) ? parent::batchMove($value, $pks, $contexts) : false;
    }

    protected function batchCopy($value, $pks, $contexts)
    {
        return $this->canCreateIn((int) $value) ? parent::batchCopy($value, $pks, $contexts) : false;
    }

    private function canCreateIn(int $catid): bool
    {
        if (SecurityHelper::authorise('create', (object) ['catid' => $catid, 'created_by' => 0], $this->getCurrentUser())) {
            return true;
        }

        $this->setError(\Joomla\CMS\Language\Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

        return false;
    }
}
