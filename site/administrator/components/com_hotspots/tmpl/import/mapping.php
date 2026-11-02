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
use TLWebdesign\Component\Hotspots\Administrator\Model\ImportModel;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Import\HtmlView $this */
?>
<form action="<?php echo Route::_('index.php?option=com_hotspots&view=import'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="card">
        <div class="card-body">
            <h2 class="h4"><?php echo Text::_('COM_HOTSPOTS_IMPORT_MAPPING'); ?></h2>
            <p><?php echo Text::sprintf('COM_HOTSPOTS_IMPORT_MAPPING_DESC', $this->escape(implode(', ', $this->files))); ?></p>

            <?php if (!$this->categories) : ?>
                <div class="alert alert-warning"><?php echo Text::_('COM_HOTSPOTS_IMPORT_ERR_CATEGORY'); ?></div>
            <?php else : ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col"><?php echo Text::_('COM_HOTSPOTS_IMPORT_STYLE'); ?></th>
                        <th scope="col"><?php echo Text::_('JCATEGORY'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->styles as $style => $icon) : ?>
                        <?php $id = 'style-' . md5((string) $style); ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo $id; ?>">
                                    <?php if ($icon !== '' && preg_match('#^https?://#i', $icon)) : ?>
                                        <img src="<?php echo $this->escape($icon); ?>" alt="" width="24" height="24" loading="lazy">
                                    <?php endif; ?>
                                    <?php echo $style === ImportModel::DEFAULT_STYLE ? Text::_('COM_HOTSPOTS_IMPORT_DEFAULT_STYLE') : $this->escape((string) $style); ?>
                                </label>
                            </th>
                            <td>
                                <select class="form-select" name="styles[<?php echo $this->escape((string) $style); ?>]" id="<?php echo $id; ?>">
                                    <?php foreach ($this->categories as $category) : ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo str_repeat('- ', max(0, $category['level'] - 1)) . $this->escape($category['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary" onclick="this.form.task.value='import.import'">
                    <span class="icon-download" aria-hidden="true"></span> <?php echo Text::_('COM_HOTSPOTS_IMPORT'); ?>
                </button>
            <?php endif; ?>
            <button type="submit" class="btn btn-secondary" onclick="this.form.task.value='import.cancel'" formnovalidate>
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </div>
    <input type="hidden" name="task" value="import.import">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
