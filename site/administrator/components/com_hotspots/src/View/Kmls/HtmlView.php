<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2011
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Kmls;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * List of KML files.
 */
class HtmlView extends BaseHtmlView
{
    protected array $items = [];

    protected ?Pagination $pagination = null;

    protected ?Registry $state = null;

    public ?Form $filterForm = null;

    public array $activeFilters = [];

    public function display($tpl = null): void
    {
        $model = $this->getModel();

        $this->items         = $model->getItems() ?: [];
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $user    = $this->getCurrentUser();
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_HOTSPOTS_KMLS'), 'map');

        if ($user->authorise('core.create', 'com_hotspots')) {
            $toolbar->addNew('kml.add');
        }

        if ($user->authorise('core.edit.state', 'com_hotspots') && $this->items) {
            $toolbar->publish('kmls.publish')->listCheck(true);
            $toolbar->unpublish('kmls.unpublish')->listCheck(true);
        }

        if ($user->authorise('core.delete', 'com_hotspots') && $this->items) {
            $toolbar->delete('kmls.delete')
                ->message('COM_HOTSPOTS_DO_YOU_REALLY_WANTO_TO_REMOVE_THIS_KML_FILE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', 'com_hotspots') || $user->authorise('core.options', 'com_hotspots')) {
            $toolbar->preferences('com_hotspots');
        }
    }
}
