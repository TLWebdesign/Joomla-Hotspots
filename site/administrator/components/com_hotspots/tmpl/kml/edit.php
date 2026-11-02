<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2011
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Administrator\Model\KmlModel;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Kml\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useScript('keepalive')->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_hotspots&layout=edit&hotspots_kml_id=' . (int) $this->item->hotspots_kml_id); ?>"
      method="post" name="adminForm" id="kml-form" class="form-validate" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-9">
            <fieldset class="options-form">
                <legend><?php echo Text::_('JDETAILS'); ?></legend>
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('catid'); ?>
                <?php if (!empty($this->item->mangled_filename)) : ?>
                    <div class="control-group">
                        <div class="control-label"><?php echo Text::_('COM_HOTSPOTS_CURRENT_KML_FILE'); ?></div>
                        <div class="controls">
                            <a href="<?php echo Uri::root() . KmlModel::FOLDER . '/' . rawurlencode((string) $this->item->mangled_filename); ?>" target="_blank" rel="noopener">
                                <?php echo $this->escape($this->item->original_filename); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php echo $this->form->renderField('kml_file'); ?>
                <?php echo $this->form->renderField('description'); ?>
            </fieldset>
        </div>
        <div class="col-lg-3">
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_HOTSPOTS_FIELDSET_PUBLISHING'); ?></legend>
                <?php echo $this->form->renderFieldset('publishing'); ?>
                <?php echo $this->form->renderField('hotspots_kml_id'); ?>
            </fieldset>
        </div>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
