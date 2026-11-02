<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2014
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Import;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use TLWebdesign\Component\Hotspots\Administrator\Model\ImportModel;

\defined('_JEXEC') or die;

/**
 * KML import: upload form (default layout) and style mapping (layout mapping).
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Uploaded files: stored name => original name.
     */
    protected array $files = [];

    /**
     * KML styles: id => icon URL.
     */
    protected array $styles = [];

    /**
     * Hotspots categories: list of [id, title, level].
     */
    protected array $categories = [];

    public function display($tpl = null): void
    {
        if (!$this->getCurrentUser()->authorise('core.create', 'com_hotspots')) {
            throw new \Joomla\CMS\Access\Exception\NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /** @var ImportModel $model */
        $model       = $this->getModel();
        $this->files = $model->getFiles();

        if ($this->getLayout() === 'mapping') {
            if (!$this->files) {
                $this->setLayout('default');
            } else {
                $this->styles = $model->getStyles();

                foreach (Categories::getInstance('Hotspots')?->get()?->getChildren(true) ?? [] as $category) {
                    $this->categories[] = ['id' => (int) $category->id, 'title' => $category->title, 'level' => (int) $category->level];
                }
            }
        }

        ToolbarHelper::title(Text::_('COM_HOTSPOTS_IMPORT'), 'upload');

        parent::display($tpl);
    }
}
