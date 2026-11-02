<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\CaptchaHelper;

\defined('_JEXEC') or die;

/**
 * Frontend marker form: add and edit markers.
 *
 * What a frontend user may change is decided here (not only in the form), so every save path gets the
 * same checks: no owner, dates or permissions from the request, publishing fields only with core.edit.state,
 * new markers only in categories where the user may create.
 */
class FormModel extends AdminModel
{
    public $typeAlias = 'com_hotspots.hotspot';

    protected $text_prefix = 'COM_HOTSPOTS';

    /** Fields only users with core.edit.state may set. */
    private const PUBLISHING_FIELDS = ['state', 'access', 'publish_up', 'publish_down'];

    /** Fields never taken from the frontend form (core.admin excepted). */
    private const PROTECTED_FIELDS = ['created_by', 'created', 'modified', 'modified_by', 'rules', 'asset_id', 'hits', 'import_table', 'import_id'];

    protected function populateState()
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $this->setState('hotspot.id', $input->getInt('a_id', $input->getInt('id')));

        $return = (string) $input->get('return', '', 'base64');
        $this->setState('return_page', ($return !== '' && Uri::isInternal(base64_decode($return))) ? base64_decode($return) : '');

        $this->setState('params', $app->getParams());
    }

    public function getTable($name = 'Marker', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function getItem($pk = null)
    {
        $pk   = (int) ($pk ?: $this->getState('hotspot.id'));
        $item = parent::getItem($pk);

        if (!$item) {
            return $item;
        }

        $item->hotspotText = trim((string) $item->description) !== ''
            ? $item->description_small . '<hr id="system-readmore">' . $item->description
            : (string) $item->description_small;

        if (isset($item->customfields) && !\is_array($item->customfields)) {
            $item->customfields = (new Registry($item->customfields))->toArray();
        }

        return $item;
    }

    /**
     * Base64 encoded return URL (internal only), or '' to go back to the map.
     */
    public function getReturnPage(): string
    {
        $return = (string) $this->getState('return_page', '');

        return $return !== '' ? base64_encode($return) : '';
    }

    public function getForm($data = [], $loadData = true)
    {
        $data  = (array) $data;
        $catid = (int) ($data['catid'] ?? 0);

        if (!$catid && $loadData) {
            $catid = (int) ($this->getItem()->catid ?? 0);
        }

        $form = $this->loadForm('com_hotspots.form.' . $catid, 'marker', ['control' => 'jform', 'load_data' => $loadData]);

        if (!$form) {
            return false;
        }

        // The custom fields of the category.
        $fields = $catid ? CustomfieldsHelper::getFields($catid) : [];

        if ($fields) {
            $form->load(CustomfieldsHelper::formXml($fields));
        }

        $user  = $this->getCurrentUser();
        $id    = (int) ($data['id'] ?? $this->getState('hotspot.id'));
        $isNew = $id === 0;

        // Name and e-mail are only asked from guests.
        if (!$user->guest) {
            $form->removeField('created_by_alias');
            $form->removeField('email');
        }

        // Captcha only for new markers and only for the configured groups.
        $captcha = $isNew ? CaptchaHelper::forMarkerForm($user) : '';

        if ($captcha === '') {
            $form->removeField('captcha');
        } else {
            $form->setFieldAttribute('captcha', 'plugin', $captcha);
        }

        $record = $isNew ? (object) ['catid' => $catid, 'created_by' => (int) $user->id] : $this->getItem($id);

        if (!$record || !$this->canEditState($record)) {
            foreach (self::PUBLISHING_FIELDS as $field) {
                $form->removeField($field);
            }
        }

        if ($isNew && ($default = (int) SettingsHelper::get('default_category', 0))) {
            $form->setFieldAttribute('catid', 'default', (string) $default);
        }

        $form->setFieldAttribute('catid', 'action', $isNew ? 'core.create' : 'core.edit');

        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_hotspots.edit.hotspot.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_hotspots.hotspot', $data);

        return $data;
    }

    protected function canDelete($record)
    {
        return !empty($record->id) && SecurityHelper::authorise('delete', $record, $this->getCurrentUser());
    }

    protected function canEditState($record)
    {
        return SecurityHelper::authorise('edit.state', $record, $this->getCurrentUser());
    }

    public function save($data)
    {
        $user  = $this->getCurrentUser();
        $table = $this->getTable();
        $id    = (int) ($data['id'] ?? 0);
        $isNew = $id === 0;

        if (!$isNew && !$table->load($id)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_NOT_EXIST'));

            return false;
        }

        $existing = $isNew ? null : (object) $table->getProperties();
        $catid    = (int) ($data['catid'] ?? ($existing->catid ?? 0));
        $target   = (object) ['catid' => $catid, 'created_by' => $isNew ? (int) $user->id : (int) $existing->created_by];

        // New markers, or markers moved to another category: the user must be allowed to create there.
        if (($isNew || $catid !== (int) $existing->catid) && !SecurityHelper::authorise('create', $target, $user)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));

            return false;
        }

        if (!$isNew && !SecurityHelper::authorise('edit', $existing, $user)) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

            return false;
        }

        if (!$this->canEditState($existing ?? $target)) {
            foreach (self::PUBLISHING_FIELDS as $field) {
                unset($data[$field]);
            }

            $autoPublish = (bool) SettingsHelper::get('addhs_autopublish', 1);

            // Without auto publish every change goes back to moderation.
            if ($isNew || !$autoPublish) {
                $data['state'] = $autoPublish ? 1 : 0;
            }
        }

        if (!$user->authorise('core.admin', 'com_hotspots')) {
            foreach (self::PROTECTED_FIELDS as $field) {
                unset($data[$field]);
            }
        }

        // The form only has some params (the admin sets the icon and the link): keep the others.
        $params         = $existing ? (new Registry($existing->params))->toArray() : [];
        $data['params'] = array_merge($params, (array) ($data['params'] ?? []));

        if ($user->id) {
            unset($data['created_by_alias']);
        }

        unset($data['email'], $data['captcha']);

        if ($isNew && trim((string) ($data['alias'] ?? '')) === '') {
            $data['alias'] = $this->uniqueAlias($catid, (string) ($data['title'] ?? ''));
        }

        return parent::save($data);
    }

    /**
     * Alias from the title, unique within the category.
     */
    private function uniqueAlias(int $catid, string $title): string
    {
        $alias = Factory::getApplication()->get('unicodeslugs')
            ? OutputFilter::stringUrlUnicodeSlug($title)
            : OutputFilter::stringURLSafe($title);

        if ($alias === '') {
            $alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        $table = $this->getTable();

        while ($table->load(['alias' => $alias, 'catid' => $catid])) {
            $alias = StringHelper::increment($alias, 'dash');
        }

        return $alias;
    }

    protected function prepareTable($table)
    {
        $table->title = trim((string) $table->title);
    }
}
