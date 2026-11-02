<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

\defined('_JEXEC') or die;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Form\HtmlView $this */

$form = $this->form;
$id   = (int) ($this->item->id ?? 0);
$user = $this->getCurrentUser();
$wa   = $this->getDocument()->getWebAssetManager();

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
MapHelper::loadJsStrings();
MapHelper::registerGoogleMaps($wa);

$this->getDocument()->addScriptOptions('com_hotspots.form', MapHelper::getConfig());

$wa->useScript('jquery')
    ->useScript('form.validate')
    ->useStyle('fontawesome')
    ->useScript('com_hotspots.googlemaps')
    ->registerAndUseStyle('com_hotspots.form', 'com_hotspots/form.css')
    ->registerAndUseScript('com_hotspots.submit-hotspots', 'com_hotspots/submit-hotspots.js', [], [], ['jquery', 'com_hotspots.googlemaps'])
    ->addInlineScript(
        "window.hotspotsConf = Joomla.getOptions('com_hotspots.form');",
        ['position' => 'before'],
        [],
        ['com_hotspots.submit-hotspots']
    );

$action = Route::_('index.php?option=com_hotspots&view=form&layout=form&a_id=' . $id);

/**
 * One field with its label (no output when the form has no such field).
 */
$field = static function (string $name, ?string $group = null, string $class = 'mb-3') use ($form): string {
    $f = $form->getField($name, $group);

    return $f ? '<div class="' . $class . '">' . $f->renderField() . '</div>' : '';
};
?>
<div id="hotspots" class="hotspots addform">
    <form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
          class="form-validate form-vertical hs-frontend-form" enctype="multipart/form-data">
        <h2><?php echo $id ? Text::sprintf('COM_HOTSPOTS_EDIT_HOTSPOT', $id) : Text::_('COM_HOTSPOTS_NEW_HOTSPOT'); ?></h2>

        <?php if ($user->guest) : ?>
            <?php echo $field('created_by_alias'); ?>
            <?php echo $field('email'); ?>
        <?php endif; ?>

        <?php echo $field('title'); ?>
        <?php echo $field('catid'); ?>
        <?php echo $field('picture'); ?>
        <?php echo $field('hotspotText'); ?>
        <?php echo $field('language', null, 'mb-3 hs-language'); ?>

        <!-- Filled by submit-hotspots.js with the custom fields of the chosen category (view=customfields&format=raw). -->
        <div id="custom-fields"></div>

        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_HOTSPOTS_LOCATION_DETAILS'); ?></legend>

            <div id="hotspots-geolocation-info"></div>

            <div class="mb-3">
                <?php echo $form->getLabel('street'); ?>
                <div class="input-group">
                    <?php echo $form->getInput('street'); ?>
                    <button type="button" id="hotspots-geolocation" class="btn btn-secondary">
                        <span class="fa fa-street-view" aria-hidden="true"></span>
                        <?php echo Text::_('COM_HOTSPOTS_FIND_MY_LOCATION'); ?>
                    </button>
                </div>
            </div>

            <?php if ((int) $this->params->get('user_interface', 1) === 0) : ?>
                <?php echo $field('plz'); ?>
                <?php echo $field('town'); ?>
            <?php else : ?>
                <?php echo $field('town'); ?>
                <?php echo $field('plz'); ?>
                <?php echo $field('administrative_area_level_1'); ?>
            <?php endif; ?>

            <?php echo $field('country'); ?>
            <?php echo $field('sticky', 'params'); ?>

            <div class="mb-3">
                <div id="map-add" title="<?php echo $this->escape(Text::_('COM_HOTSPOTS_MOVE_MARKER_DRAG')); ?>"></div>
            </div>

            <div class="row">
                <?php echo $field('gmlat', null, 'col-sm-6 mb-3'); ?>
                <?php echo $field('gmlng', null, 'col-sm-6 mb-3'); ?>
            </div>
        </fieldset>

        <?php if ($this->canEditState) : ?>
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_HOTSPOTS_FIELDSET_PUBLISHING'); ?></legend>
                <?php echo $field('publish_up'); ?>
                <?php echo $field('publish_down'); ?>
                <?php echo $field('state'); ?>
                <?php echo $field('access'); ?>
            </fieldset>
        <?php endif; ?>

        <?php echo $field('captcha'); ?>

        <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-secondary" onclick="Joomla.submitbutton('hotspot.cancel');">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
            <button type="submit" class="btn btn-primary" onclick="Joomla.submitbutton('hotspot.save'); return false;">
                <?php echo Text::_('COM_HOTSPOTS_SUBMIT'); ?>
            </button>
        </div>

        <input type="hidden" name="task" value="">
        <input type="hidden" id="hotspot-id" name="hotspot-id" value="<?php echo $id; ?>">
        <input type="hidden" name="return" value="<?php echo $this->escape($this->returnPage); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<script>
    Joomla.submitbutton = function (task) {
        const form = document.getElementById('adminForm');

        if (task === 'hotspot.cancel' || document.formvalidator.isValid(form)) {
            Joomla.submitform(task, form);
            form.querySelectorAll('button[type=submit]').forEach((button) => { button.disabled = true; });
        } else {
            document.getElementById('system-message-container').scrollIntoView({ block: 'end', behavior: 'smooth' });
        }
    };
</script>
