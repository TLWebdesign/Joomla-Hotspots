<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\View\Hotspot;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

\defined('_JEXEC') or die;

/**
 * Edit form of one marker, with a map to place it.
 */
class HtmlView extends BaseHtmlView
{
    protected ?Form $form = null;

    protected ?object $item = null;

    public function display($tpl = null)
    {
        // Address and map strings are shared with the site.
        Factory::getApplication()->getLanguage()->load('com_hotspots', JPATH_SITE);

        /** @var \TLWebdesign\Component\Hotspots\Administrator\Model\HotspotModel $model */
        $model = $this->getModel();

        $this->form = $model->getForm();
        $this->item = $model->getItem();

        if (\count($errors = $model->getErrors())) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addMapAssets();
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Google Maps and submit-hotspots.js (also used by the frontend form), which reads window.hotspotsConf.
     */
    private function addMapAssets(): void
    {
        $document = $this->getDocument();
        $wa       = $document->getWebAssetManager();

        $document->addScriptOptions('com_hotspots.config', MapHelper::getConfig());
        MapHelper::loadJsStrings();
        MapHelper::registerGoogleMaps($wa);

        $wa->useScript('jquery')
            ->useScript('keepalive')
            ->useScript('form.validate')
            ->useScript('com_hotspots.googlemaps')
            ->addInlineScript('window.hotspotsConf = Joomla.getOptions("com_hotspots.config");', [], [], ['core']);

        if (!$wa->assetExists('script', 'com_hotspots.submit-hotspots')) {
            $wa->registerScript('com_hotspots.submit-hotspots', 'com_hotspots/submit-hotspots.js', [], ['defer' => true], ['jquery', 'core', 'com_hotspots.googlemaps']);
        }

        $wa->useScript('com_hotspots.submit-hotspots');
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $user    = $this->getCurrentUser();
        $isNew   = !(int) $this->item->id;
        $toolbar = $this->getDocument()->getToolbar();
        $canEdit = $isNew
            ? ($user->authorise('core.create', 'com_hotspots') || \count($user->getAuthorisedCategories('com_hotspots', 'core.create')))
            : SecurityHelper::authorise('edit', $this->item, $user);

        ToolbarHelper::title(Text::_($isNew ? 'COM_HOTSPOTS_NEW_HOTSPOT' : 'COM_HOTSPOTS_EDIT_MARKER'), 'location');

        if ($canEdit) {
            $toolbar->apply('hotspot.apply');

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(function ($childBar) use ($user): void {
                $childBar->save('hotspot.save');

                if ($user->authorise('core.create', 'com_hotspots') || \count($user->getAuthorisedCategories('com_hotspots', 'core.create'))) {
                    $childBar->save2new('hotspot.save2new');
                }
            });
        }

        $toolbar->cancel('hotspot.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
