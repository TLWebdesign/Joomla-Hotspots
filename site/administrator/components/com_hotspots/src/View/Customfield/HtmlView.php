<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Customfield;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

\defined('_JEXEC') or die;

/**
 * Edit a custom field.
 */
class HtmlView extends BaseHtmlView
{
    protected $form;

    protected $item;

    public function display($tpl = null): void
    {
        $model = $this->getModel();

        $this->form = $model->getForm();
        $this->item = $model->getItem();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew   = empty($this->item->id);
        $canDo   = ContentHelper::getActions('com_hotspots');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            Text::_('COM_HOTSPOTS') . ': ' . Text::_($isNew ? 'COM_HOTSPOTS_CREATE_CUSTOM_FIELD' : 'COM_HOTSPOTS_EDIT_CUSTOM_FIELD'),
            'list'
        );

        if ($canDo->get($isNew ? 'core.create' : 'core.edit')) {
            $toolbar->apply('customfield.apply');
            $toolbar->save('customfield.save');
        }

        $toolbar->cancel('customfield.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
