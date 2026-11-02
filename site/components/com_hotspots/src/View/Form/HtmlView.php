<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Form;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SettingsHelper;
use TLWebdesign\Component\Hotspots\Site\Model\FormModel;

\defined('_JEXEC') or die;

/**
 * Frontend form to add or edit a marker.
 */
class HtmlView extends BaseHtmlView
{
    protected Form $form;

    protected object $item;

    protected Registry $params;

    protected string $returnPage = '';

    protected bool $canEditState = false;

    public function display($tpl = null)
    {
        // The form has one template (tmpl/form/form.php), whatever layout the URL or menu item names.
        $this->setLayout('form');

        /** @var FormModel $model */
        $model = $this->getModel();
        $user  = $this->getCurrentUser();

        $this->item       = $model->getItem() ?: (object) ['id' => 0, 'catid' => 0, 'created_by' => 0];
        $this->params     = SettingsHelper::getAll();
        $this->returnPage = $model->getReturnPage();

        $id = (int) ($this->item->id ?? 0);

        if ($id) {
            $authorised = SecurityHelper::authorise('edit', $this->item, $user);
        } else {
            $authorised = $user->authorise('core.create', 'com_hotspots')
                || \count($user->getAuthorisedCategories('com_hotspots', 'core.create')) > 0;
        }

        if (!$authorised) {
            if ($user->guest) {
                // Send guests to the login page.
                Factory::getApplication()->enqueueMessage(Text::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'notice');
            }

            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Editing needs the id held by form.edit (FormController::checkEditId on save).
        $form = $model->getForm(['id' => $id, 'catid' => (int) ($this->item->catid ?? 0)]);

        if (!$form) {
            throw new GenericDataException(implode("\n", $model->getErrors()), 500);
        }

        $this->form         = $form;
        $this->canEditState = $form->getField('state') !== false;

        $this->setDocumentTitle(Text::_($id ? 'COM_HOTSPOTS_EDIT_HOTSPOT_TITLE' : 'COM_HOTSPOTS_NEW_HOTSPOT'));

        parent::display($tpl);
    }
}
