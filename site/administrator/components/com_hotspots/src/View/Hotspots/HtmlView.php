<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Hotspots;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Marker list (layout default) and marker select list for the modal field (layout modal).
 */
class HtmlView extends BaseHtmlView
{
    protected array $items = [];

    protected ?Pagination $pagination = null;

    protected ?Registry $state = null;

    public ?Form $filterForm = null;

    public array $activeFilters = [];

    public function display($tpl = null)
    {
        // Address and map strings are shared with the site.
        Factory::getApplication()->getLanguage()->load('com_hotspots', JPATH_SITE);

        /** @var \TLWebdesign\Component\Hotspots\Administrator\Model\HotspotsModel $model */
        $model = $this->getModel();

        $this->items         = $model->getItems() ?: [];
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (\count($errors = $model->getErrors())) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        if (!Multilanguage::isEnabled()) {
            unset($this->activeFilters['language']);
            $this->filterForm?->removeField('language', 'filter');
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $canDo   = ContentHelper::getActions('com_hotspots', 'category', (int) $this->state->get('filter.category_id'));
        $user    = $this->getCurrentUser();
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_HOTSPOTS_MARKERS'), 'location');

        if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_hotspots', 'core.create'))) {
            $toolbar->addNew('hotspot.add');
        }

        if ($this->items && ($canDo->get('core.edit.state') || $canDo->get('core.delete'))) {
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('hotspots.publish')->listCheck(true);
                $childBar->unpublish('hotspots.unpublish')->listCheck(true);
            }

            if ($canDo->get('core.edit')) {
                $childBar->popupButton('geocode', 'COM_HOTSPOTS_GEOCODE')
                    ->popupType('inline')
                    ->textHeader(Text::_('COM_HOTSPOTS_GEOCODE'))
                    ->url('#joomla-dialog-geocode')
                    ->modalWidth('600px')
                    ->modalHeight('fit-content')
                    ->icon('icon-location')
                    ->listCheck(true);
            }

            if ($user->authorise('core.create', 'com_hotspots') && $user->authorise('core.edit', 'com_hotspots') && $canDo->get('core.edit.state')) {
                $childBar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->popupType('inline')
                    ->textHeader(Text::_('COM_HOTSPOTS_BATCH_OPTIONS'))
                    ->url('#joomla-dialog-batch')
                    ->modalWidth('800px')
                    ->modalHeight('fit-content')
                    ->listCheck(true);
            }

            if ($canDo->get('core.delete')) {
                $childBar->delete('hotspots.delete')
                    ->message('COM_HOTSPOTS_DO_YOU_REALLY_WANTO_TO_REMOVE_THIS_MARKER')
                    ->listCheck(true);
            }
        }

        if ($user->authorise('core.admin', 'com_hotspots') || $user->authorise('core.options', 'com_hotspots')) {
            $toolbar->preferences('com_hotspots');
        }
    }
}
