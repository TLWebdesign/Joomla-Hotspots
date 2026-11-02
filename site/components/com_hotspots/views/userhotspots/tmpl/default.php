<?php
/**
 * @author     Daniel Dimitrov
 * @date: 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
JHTML::stylesheet('media/lib_compojoom/third/font-awesome/css/font-awesome.min.css');
$user = JFactory::getUser();
?>



<form name="adminForm" id="adminForm" action="<?php JRoute::_('index.php?option=com_hotspots&view=hotspots'); ?>" method="POST">
	<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filtersHidden' => true)));
	?>
<table class="contentpaneopen table table-striped">
	<thead>
	<th>
		#
	</th>
	<th>
		<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_DATE', 'a.created', $listDirn, $listOrder); ?>
	</th>
	</thead>
	<tbody>
	<?php if(count($this->hotspots)) : ?>
		<?php
		$i = 0;
		foreach ($this->hotspots as $key => $hotspot) : ?>
		<?php
			$canChange = HotspotsHelperSecurity::authorise('edit',$hotspot);
			$published = JHTML::_('jgrid.published', $hotspot->state, $i, 'hotspots.', $canChange);

			$checked = JHTML::_('grid.id', $i, $hotspot->id);
			?>

			<tr>
				<td><?php echo $key + 1; ?><span style="display:none;"><?php echo $checked; ?></span></td>
				<td><a href="<?php echo $hotspot->link ?>"><?php echo $hotspot->title; ?></a>
					<?php if (HotspotsHelperSecurity::authorise('edit',$hotspot)) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_hotspots&task=form.edit&id=' . $hotspot->id); ?>" class="btn btn-small">
							<span class="fa fa-edit"></span> <?php echo JText::_('JACTION_EDIT'); ?></a>
					<?php endif; ?>
					<?php if (HotspotsHelperSecurity::authorise('delete',$hotspot)) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_hotspots&task=hotspots.delete&cid=' . $hotspot->id .'&' . JSession::getFormToken() .'=1'); ?>"
						   onClick="return confirm('<?php echo JText::_('COM_HOTSPOTS_REMOVE_HOTSPOT_PERMANENT', true); ?>');" class="btn btn-small">
							<span class="fa fa-trash-o"></span> <?php echo JText::_('JACTION_DELETE'); ?></a>
					<?php endif; ?>
				</td>
				<td><?php echo $published; ?></td>
				<td><?php echo $hotspot->created; ?></td>
			</tr>
		<?php $i++; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="4">
				<?php echo JText::_('COM_HOTSPOTS_USERHOTSPOTS_COULDNT_FIND_HOTSPOTS'); ?>
			</td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>

<?php echo $this->pagination->getListFooter(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
