<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

\defined('_JEXEC') or die;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Customfields\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useScript('table.columns')->useScript('multiselect');

$user      = Factory::getApplication()->getIdentity();
$canChange = $user->authorise('core.edit.state', 'com_hotspots');
$canEdit   = $user->authorise('core.edit', 'com_hotspots');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder === 'cf.ordering';

if ($saveOrder && $this->items) {
    $saveOrderingUrl = 'index.php?option=com_hotspots&task=customfields.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_hotspots&view=customfields'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php if (!$this->items) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('COM_HOTSPOTS_NO_CUSTOM_FIELDS_CREATED_YET'); ?>
            </div>
        <?php else : ?>
            <table class="table" id="customfieldList">
                <caption class="visually-hidden"><?php echo Text::_('COM_HOTSPOTS_CUSTOM_FIELDS'); ?></caption>
                <thead>
                <tr>
                    <td class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></td>
                    <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', '', 'cf.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                    </th>
                    <th scope="col" class="w-1 text-center">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'cf.enabled', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_TITLE', 'cf.title', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="d-none d-md-table-cell"><?php echo Text::_('COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_CATEGORY'); ?></th>
                    <th scope="col" class="w-10 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_TYPE', 'cf.type', $listDirn, $listOrder); ?>
                    </th>
                    <th scope="col" class="w-10 d-none d-md-table-cell"><?php echo Text::_('COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_DEFAULT'); ?></th>
                    <th scope="col" class="w-5 d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'cf.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
                </thead>
                <tbody <?php if ($saveOrder) : ?>class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="0">
                        <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?></td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php $iconClass = !$canChange ? ' inactive' : (!$saveOrder ? ' inactive" title="' . Text::_('JORDERINGDISABLED') : ''); ?>
                            <span class="sortable-handler<?php echo $iconClass; ?>"><span class="icon-ellipsis-v" aria-hidden="true"></span></span>
                            <?php if ($canChange && $saveOrder) : ?>
                                <input type="text" name="order[]" size="5" value="<?php echo (int) $item->ordering; ?>" class="width-20 text-area-order hidden">
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->enabled, $i, 'customfields.', $canChange); ?>
                        </td>
                        <th scope="row">
                            <?php if ($canEdit) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_hotspots&task=customfield.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->title); ?></a>
                            <?php else : ?>
                                <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>
                            <div class="small"><?php echo Text::_('COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_SLUG'); ?>: <?php echo $this->escape($item->slug); ?></div>
                        </th>
                        <td class="d-none d-md-table-cell">
                            <?php if ($item->show === 'category') : ?>
                                <?php echo $this->escape(implode(', ', array_map(static fn ($cat) => (string) $cat->title, $item->cats))); ?>
                            <?php else : ?>
                                <?php echo Text::_('COM_HOTSPOTS_CUSTOM_FIELDS_ALL_CATS_LABEL'); ?>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-md-table-cell"><?php echo $this->escape($item->type); ?></td>
                        <td class="d-none d-md-table-cell"><?php echo $this->escape($item->default); ?></td>
                        <td class="d-none d-md-table-cell"><?php echo (int) $item->id; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php echo $this->pagination->getListFooter(); ?>
        <?php endif; ?>
        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
