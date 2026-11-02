<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2011 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Kml;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

\defined('_JEXEC') or die;

/**
 * Edit a KML file.
 */
class HtmlView extends BaseHtmlView
{
    protected ?Form $form = null;

    protected ?object $item = null;

    public function display($tpl = null): void
    {
        $model = $this->getModel();

        $this->form = $model->getForm();
        $this->item = $model->getItem();

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew   = empty($this->item->hotspots_kml_id);
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_($isNew ? 'COM_HOTSPOTS_NEW_KML' : 'COM_HOTSPOTS_EDIT_KML_TITLE'), 'map');

        $toolbar->apply('kml.apply');
        $toolbar->save('kml.save');
        $toolbar->cancel('kml.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
