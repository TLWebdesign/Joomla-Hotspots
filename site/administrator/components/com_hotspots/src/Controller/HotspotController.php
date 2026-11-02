<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Controller;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;

\defined('_JEXEC') or die;

/**
 * Edit, save and batch of one marker.
 */
class HotspotController extends FormController
{
    protected $text_prefix = 'COM_HOTSPOTS';

    public function edit($key = null, $urlVar = null)
    {
        // Markers need a category.
        if (!\count(Categories::getInstance('Hotspots')?->get()?->getChildren() ?? [])) {
            $this->setRedirect(
                Route::_('index.php?option=com_categories&extension=com_hotspots', false),
                Text::_('COM_HOTSPOTS_CREATE_CATEGORIES_FIRST'),
                'notice'
            );

            return false;
        }

        return parent::edit($key, $urlVar);
    }

    protected function allowAdd($data = [])
    {
        $catid = ArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');

        if ($catid) {
            return SecurityHelper::authorise('create', (object) ['catid' => $catid, 'created_by' => 0], $this->app->getIdentity());
        }

        return parent::allowAdd($data);
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        if (!$id) {
            return parent::allowEdit($data, $key);
        }

        $item = $this->getModel()->getItem($id);

        return $item && $item->id && SecurityHelper::authorise('edit', $item, $this->app->getIdentity());
    }

    public function batch($model = null)
    {
        $this->checkToken();

        $model = $this->getModel('Hotspot', 'Administrator', []);

        $this->setRedirect(Route::_('index.php?option=com_hotspots&view=hotspots' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }

    /**
     * Saves the images of the upload field after the marker itself was saved.
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        $form  = $this->input->post->get('jform', [], 'array');
        $files = array_values(array_filter(array_map('strval', (array) ($form['picture'] ?? []))));
        $meta  = (array) ($form['picture_data'] ?? []);

        $model->saveMultimedia((int) $model->getState($model->getName() . '.id'), $files, $meta);
    }
}
