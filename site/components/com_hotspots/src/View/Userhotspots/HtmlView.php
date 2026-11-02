<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\View\Userhotspots;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Site\Helper\RouteHelper;

\defined('_JEXEC') or die;

/**
 * "My markers": the markers of the logged-in user.
 */
class HtmlView extends BaseHtmlView
{
    protected array $items = [];

    protected $pagination;

    protected $state;

    public $filterForm;

    public $activeFilters = [];

    public function display($tpl = null)
    {
        $user = $this->getCurrentUser();

        if ($user->guest) {
            $app = Factory::getApplication();
            $app->enqueueMessage(Text::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'notice');
            $app->redirect(Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString()), false));

            return;
        }

        $model = $this->getModel();

        $this->items         = $model->getItems() ?: [];
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        foreach ($this->items as $item) {
            $item->link = Route::_(RouteHelper::getHotspotRoute($item->id . ':' . $item->alias, $item->catid . ':' . $item->cat_alias));
        }

        $this->setDocumentTitle(Text::_('COM_HOTSPOTS_USER_HOTSPOTS'));

        parent::display($tpl);
    }
}
