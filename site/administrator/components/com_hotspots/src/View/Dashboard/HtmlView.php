<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Dashboard;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use TLWebdesign\Component\Hotspots\Administrator\Model\DashboardModel;

\defined('_JEXEC') or die;

/**
 * Dashboard: marker numbers, map of the latest markers and the version.
 */
class HtmlView extends BaseHtmlView
{
    protected array $stats = [];

    protected array $markers = [];

    protected array $version = [];

    public function display($tpl = null): void
    {
        /** @var DashboardModel $model */
        $model = $this->getModel();

        $this->stats   = $model->getStats();
        $this->markers = $model->getLatestMarkers();
        $this->version = $model->getVersion();

        ToolbarHelper::title(Text::_('COM_HOTSPOTS_DASHBOARD'), 'location');

        if ($this->getCurrentUser()->authorise('core.admin', 'com_hotspots') || $this->getCurrentUser()->authorise('core.options', 'com_hotspots')) {
            ToolbarHelper::preferences('com_hotspots');
        }

        parent::display($tpl);
    }
}
