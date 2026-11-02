<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;
use TLWebdesign\Component\Hotspots\Administrator\Helper\SecurityHelper;

\defined('_JEXEC') or die;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Userhotspots\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useStyle('fontawesome')->useScript('table.columns')->useScript('multiselect');

$user      = $this->getCurrentUser();
$listOrder = $this->escape((string) $this->state->get('list.ordering'));
$listDirn  = $this->escape((string) $this->state->get('list.direction'));
$itemId    = Factory::getApplication()->getInput()->getInt('Itemid');
$addLink   = Route::_('index.php?option=com_hotspots&task=form.add' . ($itemId ? '&Itemid=' . $itemId : ''));
$canCreate = $user->authorise('core.create', 'com_hotspots') || \count($user->getAuthorisedCategories('com_hotspots', 'core.create')) > 0;
?>
<div class="hotspots hotspots-userhotspots">
    <?php if ($canCreate) : ?>
        <p class="text-end">
            <a class="btn btn-primary" href="<?php echo $addLink; ?>">
                <span class="fa fa-plus" aria-hidden="true"></span> <?php echo Text::_('COM_HOTSPOTS_NEW_HOTSPOT'); ?>
            </a>
        </p>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_hotspots&view=userhotspots' . ($itemId ? '&Itemid=' . $itemId : '')); ?>" method="post" name="adminForm" id="adminForm">
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <?php if (!$this->items) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span>
                <?php echo Text::_('COM_HOTSPOTS_USERHOTSPOTS_COULDNT_FIND_HOTSPOTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HOTSPOTS_TITLE', 'a.title', $listDirn, $listOrder); ?></th>
                        <th scope="col"><?php echo Text::_('JCATEGORY'); ?></th>
                        <th scope="col" class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?></th>
                        <th scope="col"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HOTSPOTS_DATE', 'a.created', $listDirn, $listOrder); ?></th>
                        <th scope="col" class="text-end"><span class="visually-hidden"><?php echo Text::_('JACTION'); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <?php
                        $canEdit      = SecurityHelper::authorise('edit', $item, $user);
                        $canEditState = SecurityHelper::authorise('edit.state', $item, $user);
                        $canDelete    = SecurityHelper::authorise('delete', $item, $user);
                        ?>
                        <tr>
                            <th scope="row">
                                <?php if ((int) $item->state === 1) : ?>
                                    <a href="<?php echo $item->link; ?>"><?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>
                            </th>
                            <td><?php echo $this->escape((string) $item->cat_name); ?></td>
                            <td class="text-center">
                                <?php if ($canEditState) : ?>
                                    <span class="d-none"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></span>
                                    <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'hotspot.', true); ?>
                                <?php else : ?>
                                    <span class="badge <?php echo (int) $item->state === 1 ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo Text::_((int) $item->state === 1 ? 'JPUBLISHED' : 'JUNPUBLISHED'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $this->escape(MarkerHelper::localDate($item->created)); ?></td>
                            <td class="text-end text-nowrap">
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_hotspots&task=form.edit&id=' . (int) $item->id . ($itemId ? '&Itemid=' . $itemId : '')); ?>" class="btn btn-sm btn-secondary">
                                        <span class="fa fa-edit" aria-hidden="true"></span> <?php echo Text::_('JACTION_EDIT'); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($canDelete) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_hotspots&task=hotspot.delete&id=' . (int) $item->id . ($itemId ? '&Itemid=' . $itemId : '') . '&' . Session::getFormToken() . '=1'); ?>"
                                       onclick="return confirm(<?php echo $this->escape(json_encode(Text::_('COM_HOTSPOTS_REMOVE_HOTSPOT_PERMANENT'))); ?>);"
                                       class="btn btn-sm btn-danger">
                                        <span class="fa fa-trash" aria-hidden="true"></span> <?php echo Text::_('JACTION_DELETE'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
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
</div>
