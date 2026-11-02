<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Controller;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\RouteHelper;

\defined('_JEXEC') or die;

/**
 * Saves, deletes and (un)publishes markers from the frontend.
 * The form is opened with form.edit / form.add (FormController of this component).
 */
class HotspotController extends FormController
{
    protected $context = 'hotspot';

    protected $view_item = 'form';

    protected $view_list = 'userhotspots';

    protected $text_prefix = 'COM_HOTSPOTS';

    public function getModel($name = 'Form', $prefix = 'Site', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save($key = null, $urlVar = 'a_id')
    {
        return parent::save($key, $urlVar);
    }

    public function cancel($key = 'a_id')
    {
        parent::cancel($key);

        $this->setRedirect($this->getReturnPage());

        return true;
    }

    protected function allowAdd($data = [])
    {
        $user  = $this->app->getIdentity();
        $catid = (int) ($data['catid'] ?? 0);

        if (!$catid) {
            return parent::allowAdd($data);
        }

        // A menu item can limit the categories of the form.
        if (SettingsHelper::get('enable_category', 0)) {
            $allowed = ArrayHelper::toInteger((array) SettingsHelper::get('catid', []));

            if ($allowed && !\in_array($catid, $allowed, true)) {
                return false;
            }
        }

        return SecurityHelper::authorise('create', (object) ['catid' => $catid, 'created_by' => (int) $user->id], $user);
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id     = (int) ($data[$key] ?? 0);
        $record = $id ? $this->getModel()->getItem($id) : null;

        return $record && !empty($record->id) && SecurityHelper::authorise('edit', $record, $this->app->getIdentity());
    }

    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
    {
        $append = '&layout=form';

        if ($itemId = $this->input->getInt('Itemid')) {
            $append .= '&Itemid=' . $itemId;
        }

        if ($recordId) {
            $append .= '&' . $urlVar . '=' . (int) $recordId;
        }

        if ($return = $this->input->get('return', null, 'base64')) {
            $append .= '&return=' . $return;
        }

        return $append;
    }

    protected function getRedirectToListAppend()
    {
        $itemId = $this->input->getInt('Itemid');

        return $itemId ? '&Itemid=' . $itemId : '';
    }

    /**
     * After saving: store the uploaded images, then go to the marker page (published) or the map.
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        $id = (int) $model->getState($model->getName() . '.id');

        if (!$id) {
            return;
        }

        $this->saveImages($id);

        $item = $model->getItem($id);

        if ((int) $item->state === 1) {
            $category = Categories::getInstance('Hotspots')?->get((int) $item->catid);
            $catSlug  = $category ? $item->catid . ':' . $category->alias : (int) $item->catid;
            $link     = RouteHelper::getHotspotRoute($id . ':' . $item->alias, $catSlug);

            $this->setRedirect(Route::_($link, false), Text::_('COM_HOTSPOTS_HOTSPOT_' . (empty($validData['id']) ? 'ADDED' : 'EDITED')));

            return;
        }

        $this->app->enqueueMessage(Text::_(
            SecurityHelper::authorise('edit.state', $item) ? 'COM_HOTSPOTS_HOTSPOT_SAVED_BUT_IT_IS_SET_TO_UNPUBLISHED' : 'COM_HOTSPOTS_HOTSPOT_SAVED_BUT_NEEDS_MODERATION'
        ));

        $this->setRedirect($this->getReturnPage());
    }

    /**
     * Deletes one marker (link with a token on the marker page and in "my markers").
     */
    public function delete(): void
    {
        $this->checkToken('get');

        $id     = $this->input->getInt('id');
        $model  = $this->getModel();
        $record = $id ? $model->getItem($id) : null;
        $back   = Route::_('index.php?option=com_hotspots&view=userhotspots' . $this->getRedirectToListAppend(), false);

        if (!$record || empty($record->id) || !SecurityHelper::authorise('delete', $record, $this->app->getIdentity())) {
            $this->setRedirect($back, Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'error');

            return;
        }

        $pks = [$id];

        if ($model->delete($pks)) {
            $this->setRedirect($back, Text::plural('COM_HOTSPOTS_N_ITEMS_DELETED', 1));
        } else {
            $this->setRedirect($back, $model->getError(), 'error');
        }
    }

    public function publish(): void
    {
        $this->checkToken();

        $value = $this->getTask() === 'unpublish' ? 0 : 1;
        $pks   = ArrayHelper::toInteger((array) $this->input->post->get('cid', [], 'array'));
        $model = $this->getModel();
        $back  = Route::_('index.php?option=com_hotspots&view=userhotspots' . $this->getRedirectToListAppend(), false);

        // AdminModel::publish() checks edit.state for every marker.
        if ($pks && $model->publish($pks, $value)) {
            $this->setRedirect($back, Text::plural('COM_HOTSPOTS_N_ITEMS_' . ($value ? 'PUBLISHED' : 'UNPUBLISHED'), \count($pks)));
        } else {
            $this->setRedirect($back, $model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
        }
    }

    public function unpublish(): void
    {
        $this->publish();
    }

    /**
     * The images of the multimedia field: temporary uploads that now belong to the marker.
     */
    private function saveImages(int $id): void
    {
        $form  = (array) $this->input->post->get('jform', [], 'array');
        $files = array_values(array_filter((array) ($form['picture'] ?? []), 'is_string'));

        if (!$files) {
            return;
        }

        // Image titles and descriptions are shown on the site: plain text only.
        $filter = InputFilter::getInstance();
        $meta   = [];

        foreach ((array) ($form['picture_data'] ?? []) as $key => $value) {
            $meta[$key] = [
                'title'       => $filter->clean(\is_array($value) ? ($value['title'] ?? '') : '', 'string'),
                'description' => $filter->clean(\is_array($value) ? ($value['description'] ?? '') : '', 'string'),
            ];
        }

        $this->getModel('Multimedia', 'Administrator', ['ignore_request' => true])->uploadPermanent($id, $files, $meta);
    }

    private function getReturnPage(): string
    {
        $return = (string) $this->input->get('return', '', 'base64');

        if ($return !== '' && \Joomla\CMS\Uri\Uri::isInternal(base64_decode($return))) {
            return base64_decode($return);
        }

        $itemId = RouteHelper::findItem();

        return Route::_('index.php?option=com_hotspots&view=hotspots' . ($itemId ? '&Itemid=' . $itemId : ''), false);
    }
}
