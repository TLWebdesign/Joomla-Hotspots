<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2014 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Import\HtmlView $this */
?>
<form action="<?php echo Route::_('index.php?option=com_hotspots&view=import'); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm">
    <div class="card">
        <div class="card-body">
            <h2 class="h4"><?php echo Text::_('COM_HOTSPOTS_IMPORT_KML'); ?></h2>
            <p><?php echo Text::_('COM_HOTSPOTS_IMPORT_KML_DESC'); ?></p>

            <?php if ($this->files) : ?>
                <div class="alert alert-info">
                    <?php echo Text::sprintf('COM_HOTSPOTS_IMPORT_FILES_WAITING', $this->escape(implode(', ', $this->files))); ?>
                    <a class="btn btn-sm btn-primary ms-2" href="<?php echo Route::_('index.php?option=com_hotspots&view=import&layout=mapping'); ?>"><?php echo Text::_('JNEXT'); ?></a>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="kml_files" class="form-label"><?php echo Text::_('COM_HOTSPOTS_IMPORT_FILES'); ?></label>
                <input type="file" class="form-control" name="kml_files[]" id="kml_files" accept=".kml,.kmz" multiple required>
            </div>
            <button type="submit" class="btn btn-primary" onclick="this.form.task.value='import.upload'">
                <span class="icon-upload" aria-hidden="true"></span> <?php echo Text::_('COM_HOTSPOTS_IMPORT_UPLOAD'); ?>
            </button>
        </div>
    </div>
    <input type="hidden" name="task" value="import.upload">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
