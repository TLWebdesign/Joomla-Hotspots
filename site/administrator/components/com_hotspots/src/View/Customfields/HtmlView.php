<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Customfields;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * List of the marker custom fields.
 */
class HtmlView extends BaseHtmlView
{
    protected array $items = [];

    protected ?Pagination $pagination = null;

    protected ?Registry $state = null;

    public $filterForm;

    public $activeFilters = [];

    public function display($tpl = null): void
    {
        $model = $this->getModel();

        $this->items         = $model->getItems() ?: [];
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $canDo   = ContentHelper::getActions('com_hotspots');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_HOTSPOTS') . ': ' . Text::_('COM_HOTSPOTS_CUSTOM_FIELDS'), 'list');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('customfield.add');
        }

        if ($canDo->get('core.edit.state') && $this->items) {
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();
            $childBar->publish('customfields.publish')->listCheck(true);
            $childBar->unpublish('customfields.unpublish')->listCheck(true);
        }

        if ($canDo->get('core.delete') && $this->items) {
            $toolbar->delete('customfields.delete')
                ->message('COM_HOTSPOTS_DO_YOU_REALLY_WANTO_TO_REMOVE_THIS_CUSTOMFIELD')
                ->listCheck(true);
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences('com_hotspots');
        }
    }
}
