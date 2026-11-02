<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Hotspots\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useScript('modal-content-select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div class="container-popup">
    <form action="<?php echo Route::_('index.php?option=com_hotspots&view=hotspots&layout=modal&tmpl=component&' . Session::getFormToken() . '=1'); ?>"
          method="post" name="adminForm" id="adminForm">

        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-sm">
                <caption class="visually-hidden"><?php echo Text::_('COM_HOTSPOTS_SELECT_HOTSPOT'); ?></caption>
                <thead>
                    <tr>
                        <th scope="col"><?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?></th>
                        <th scope="col" class="w-15 d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?></th>
                        <th scope="col" class="w-15 d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HOTSPOTS_TOWN', 'a.town', $listDirn, $listOrder); ?></th>
                        <th scope="col" class="w-1"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $item) : ?>
                    <tr>
                        <th scope="row">
                            <a class="select-link" href="javascript:void(0)" data-content-select data-content-type="com_hotspots.hotspot"
                               data-id="<?php echo (int) $item->id; ?>" data-title="<?php echo $this->escape($item->title); ?>"
                               data-cat-id="<?php echo (int) $item->catid; ?>" data-language="<?php echo $this->escape($item->language); ?>">
                                <?php echo $this->escape($item->title); ?>
                            </a>
                        </th>
                        <td class="small d-none d-md-table-cell"><?php echo $this->escape($item->category_title); ?></td>
                        <td class="small d-none d-md-table-cell"><?php echo $this->escape($item->town); ?></td>
                        <td><?php echo (int) $item->id; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php echo $this->pagination->getListFooter(); ?>
        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="forcedLanguage" value="<?php echo $this->escape(\Joomla\CMS\Factory::getApplication()->getInput()->get('forcedLanguage', '', 'CMD')); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
