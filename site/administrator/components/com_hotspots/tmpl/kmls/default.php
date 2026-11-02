<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2011
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Administrator\Model\KmlModel;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Kmls\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useScript('table.columns')->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canChange = $this->getCurrentUser()->authorise('core.edit.state', 'com_hotspots');
$canEdit   = $this->getCurrentUser()->authorise('core.edit', 'com_hotspots');
?>
<form action="<?php echo Route::_('index.php?option=com_hotspots&view=kmls'); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

    <?php if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <table class="table" id="kmlList">
            <caption class="visually-hidden"><?php echo Text::_('COM_HOTSPOTS_KMLS'); ?></caption>
            <thead>
            <tr>
                <td class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></td>
                <th scope="col" class="w-1 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'kmls.state', $listDirn, $listOrder); ?></th>
                <th scope="col"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HOTSPOTS_KML_TITLE', 'kmls.title', $listDirn, $listOrder); ?></th>
                <th scope="col" class="d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'JCATEGORY', 'cat.title', $listDirn, $listOrder); ?></th>
                <th scope="col" class="d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HOTSPOTS_KML_ORIGINAL_FILENAME', 'kmls.original_filename', $listDirn, $listOrder); ?></th>
                <th scope="col" class="d-none d-lg-table-cell"><?php echo Text::_('JGLOBAL_FIELD_CREATED_BY_LABEL'); ?></th>
                <th scope="col" class="d-none d-lg-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_FIELD_CREATED_LABEL', 'kmls.created', $listDirn, $listOrder); ?></th>
                <th scope="col" class="w-1 d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'kmls.hotspots_kml_id', $listDirn, $listOrder); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->hotspots_kml_id, false, 'cid', 'cb', $item->title); ?></td>
                    <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'kmls.', $canChange); ?></td>
                    <th scope="row">
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_hotspots&task=kml.edit&hotspots_kml_id=' . (int) $item->hotspots_kml_id); ?>">
                                <?php echo $this->escape($item->title); ?>
                            </a>
                        <?php else : ?>
                            <?php echo $this->escape($item->title); ?>
                        <?php endif; ?>
                    </th>
                    <td class="d-none d-md-table-cell"><?php echo $this->escape($item->category_title); ?></td>
                    <td class="d-none d-md-table-cell">
                        <a href="<?php echo Uri::root() . KmlModel::FOLDER . '/' . rawurlencode((string) $item->mangled_filename); ?>" target="_blank" rel="noopener">
                            <?php echo $this->escape($item->original_filename); ?>
                        </a>
                    </td>
                    <td class="d-none d-lg-table-cell"><?php echo $this->escape($item->user_name); ?></td>
                    <td class="d-none d-lg-table-cell"><?php echo $item->created ? HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')) : ''; ?></td>
                    <td class="d-none d-md-table-cell"><?php echo (int) $item->hotspots_kml_id; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
