<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.multiselect');

$hasAjaxOrderingSupport = version_compare(JVERSION, '3.0', 'ge');

$user = JFactory::getUser();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$saveOrder = $listOrder == 'ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_hotspots&task=customfields.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'customfields', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$this->lists = new stdClass;
$this->lists->order_Dir = $listDirn;
$this->lists->order = $listOrder;

if (JVERSION > 3)
{
	JHtml::_('formbehavior.chosen', 'select');
}

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'customfields', 'COM_HOTSPOTS_CUSTOM_FIELDS');
?>
	<script type="text/javascript">
		Joomla.orderTable = function () {
			table = document.getElementById("sortTable");
			direction = document.getElementById("directionTable");
			order = table.options[table.selectedIndex].value;
			if (order != '<?php echo $listOrder; ?>') {
				dirn = 'asc';
			}
			else {
				dirn = direction.options[direction.selectedIndex].value;
			}
			Joomla.tableOrdering(order, dirn, '');
		}
	</script>

<?php if (!HOTSPOTS_PRO) : ?>
	<p class="alert alert-warning">
		<?php echo JText::sprintf('COM_HOTSPOTS_UPGRADE_TO_PRO_CUSTOM_FIELDS', 'https://compojoom.com/joomla-extensions/hotspots'); ?>
	</p>
<?php endif; ?>

	<form method="post" action="<?php echo JRoute::_('index.php?option=com_hotspots&view=customfields'); ?>" id="adminForm" name="adminForm">
		<div class="box-info full <?php echo !HOTSPOTS_PRO ? 'disabled' : ''; ?>">
			<table class="table table-hover table-striped" id="customfields">
				<thead>
				<tr>
					<?php if ($hasAjaxOrderingSupport !== false): ?>
						<th width="20px">
							<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $this->lists->order_Dir, $this->lists->order, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
						</th>
					<?php endif; ?>
					<th width="30">
						<?php echo JHTML::_('grid.sort', 'Num', 'compojoom_customfield_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th width="20"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/></th>
					<th>
						<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_TITLE', 'title', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th>
						<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_SLUG', 'title', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th>
						<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_CATEGORY', 'title', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th width="50">
						<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_TYPE', 'type', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th width="120">
						<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_CUSTOM_FIELDS_FIELD_DEFAULT', 'default', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th width="8%">
						<?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'enabled', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
				</tr>
				<tr>
					<?php if ($hasAjaxOrderingSupport !== false): ?>
						<td></td>
					<?php endif; ?>
					<td></td>
					<td>
					</td>
					<td class="form-inline">
						<div class="filter-search btn-group pull-left">
							<div class="input-group">
							<input type="text" name="filter[search]" id="search"
							       value="<?php echo $this->escape($this->state->get('filter.search', '')); ?>"
							       class="form-control" onchange="document.adminForm.submit();"
							       placeholder="<?php echo JText::_('LIB_COMPOJOOM_CUSTOM_FIELDS_FIELD_TITLE') ?>"
								/>
								<span class="input-group-btn">
									<button class="btn btn-default" onclick="this.form.submit();">
										<?php echo JText::_('JSEARCH_FILTER'); ?>
									</button>
							<button class="btn btn-default" onclick="document.adminForm.search.value='';this.form.submit();">
								<?php echo JText::_('JSEARCH_RESET'); ?>
							</button>
								</span>
						</div>
					</td>
					<td>

					</td>
					<td></td>
					<?php if ($hasAjaxOrderingSupport === false): ?>
						<td></td>
					<?php endif; ?>
					<td>

					</td>
				</tr>
				</thead>

				<tbody>
				<?php if (count($this->items)) : ?>
					<?php $i = 0;
					foreach ($this->items as $item): ?>
						<?php
						$i++;
						$item->published = $item->enabled;
						$canChange = $this->canDo->get('core.edit.state');
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<?php if ($hasAjaxOrderingSupport) : ?>
								<td class="order nowrap center hidden-phone">

									<?php

									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
							</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>"
										       class="width-20 text-area-order "/>
									<?php endif; ?>

								</td>
							<?php endif; ?>
							<td>
								<?php echo $item->id; ?>
							</td>
							<td>
								<?php echo JHTML::_('grid.id', $i, $item->id, false); ?>
							</td>
							<td>
								<a href="<?php echo JRoute::_('index.php?option=com_hotspots&view=customfield&layout=edit&id=' . $item->id); ?>">
									<?php echo $item->title; ?>
								</a>
							</td>
							<td>
								<?php echo $item->slug; ?>
							</td>
							<td>
								<?php
								if (isset($item->cats))
								{
									foreach ($item->cats as $key => $cat)
									{
										echo $cat->title . ($key == count($item->cats) - 1 ? '' : ', ');
									}
								}
								else
								{
									echo JText::_('COM_HOTSPOTS_CUSTOM_FIELDS_ALL_CATS_LABEL');
								}
								?>
							</td>
							<td>
								<?php echo $item->type; ?>
							</td>
							<td>
								<?php echo $item->default; ?>
							</td>
							<td>
								<?php echo JHTML::_('jgrid.published', $item->enabled, $i, 'customfields.'); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="11"><?php echo JText::_('COM_HOTSPOTS_NO_CUSTOM_FIELDS_CREATED_YET'); ?></td>
					</tr>
				<?php endif; ?>
				</tbody>

				<tfoot>
				<tr>
					<td colspan="20">
						<?php if ($this->pagination->total > 0) echo $this->pagination->getListFooter() ?>
					</td>
				</tr>
				</tfoot>
			</table>
		</div>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->lists->order; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists->order_Dir; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
