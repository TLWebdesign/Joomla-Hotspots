<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Hotspot\HtmlView $this */

$this->getDocument()->getWebAssetManager()
    ->registerAndUseStyle('com_hotspots.admin-marker', 'com_hotspots/hotspots-backend.css');

$this->useCoreUI = true;
$canAdmin        = $this->getCurrentUser()->authorise('core.admin', 'com_hotspots');
?>
<form action="<?php echo Route::_('index.php?option=com_hotspots&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate" aria-label="<?php echo Text::_($this->item->id ? 'COM_HOTSPOTS_EDIT_MARKER' : 'COM_HOTSPOTS_NEW_HOTSPOT', true); ?>">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_HOTSPOTS_FIELDSET_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('hotspotText'); ?>
                <?php echo $this->form->renderField('picture'); ?>
                <div id="custom-fields"></div>

                <h2 class="h4 mt-4"><?php echo Text::_('COM_HOTSPOTS_LOCATION_DETAILS'); ?></h2>
                <div class="row">
                    <div class="col-xl-5">
                        <div id="hotspots-geolocation-info"></div>
                        <button type="button" class="btn btn-secondary mb-3" id="hotspots-geolocation">
                            <span class="icon-location" aria-hidden="true"></span> <?php echo Text::_('COM_HOTSPOTS_FIND_MY_LOCATION'); ?>
                        </button>
                        <?php foreach (['street', 'plz', 'town', 'administrative_area_level_1', 'country'] as $field) : ?>
                            <?php echo $this->form->renderField($field); ?>
                        <?php endforeach; ?>
                        <?php echo $this->form->renderField('sticky', 'params'); ?>
                        <?php echo $this->form->renderField('gmlat'); ?>
                        <?php echo $this->form->renderField('gmlng'); ?>
                    </div>
                    <div class="col-xl-7">
                        <div id="map-add" style="height:450px" title="<?php echo Text::_('COM_HOTSPOTS_MOVE_MARKER_DRAG'); ?>"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <fieldset class="form-vertical">
                    <legend class="visually-hidden"><?php echo Text::_('COM_HOTSPOTS_FIELDSET_DETAILS'); ?></legend>
                    <?php foreach (['catid', 'state', 'access', 'language'] as $field) : ?>
                        <?php echo $this->form->renderField($field); ?>
                    <?php endforeach; ?>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('COM_HOTSPOTS_MARKER_FIELDSET_OPTIONS')); ?>
        <fieldset id="fieldset-options" class="options-form">
            <legend><?php echo Text::_('COM_HOTSPOTS_MARKER_FIELDSET_OPTIONS'); ?></legend>
            <?php foreach ($this->form->getFieldset('markeroptions') as $field) : ?>
                <?php if ($field->fieldname !== 'sticky') : ?>
                    <?php echo $field->renderField(); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_HOTSPOTS_FIELDSET_PUBLISHING')); ?>
        <fieldset id="fieldset-publishingdata" class="options-form">
            <legend><?php echo Text::_('COM_HOTSPOTS_FIELDSET_PUBLISHING'); ?></legend>
            <?php echo $this->form->renderFieldset('publishing'); ?>
            <?php echo $this->form->renderField('id'); ?>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if ($canAdmin) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL')); ?>
            <fieldset id="fieldset-rules" class="options-form">
                <legend><?php echo Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></legend>
                <?php echo $this->form->getInput('rules'); ?>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" id="hotspot-id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="return" value="<?php echo $this->escape(\Joomla\CMS\Factory::getApplication()->getInput()->getBase64('return')); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
