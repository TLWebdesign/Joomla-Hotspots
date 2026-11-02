<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       31.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filter.output');
$listDirn = $this->escape($this->lists['order_Dir']);
$listOrder = $this->escape($this->lists['order']);
$document = JFactory::getDocument();
$document->addStyleDeclaration(".icon-48-kmls{background: url(../media/com_hotspots/backend/images/kmls-48px.png) no-repeat;}");

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'kmls', 'COM_HOTSPOTS_KML', '');
$isPro = HOTSPOTS_PRO;
?>

<?php if(!$isPro): ?>
	<div class="alert alert-notice">
	 <?php echo JText::sprintf('COM_HOTSPOTS_FEATURE_PRO', 'KML', 'https://compojoom.com/joomla-extensions/hotspots'); ?>
	</div>
<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_hotspots&view=kmls'); ?>" method="post" name="adminForm"
	      id="adminForm">
		<div class="box-info full <?php echo !$isPro ? 'disabled' : ''; ?>">
			<h2><?php echo $this->pagination->getResultsCounter(); ?></h2>

			<div class="additional-btn">
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="col-md-4">
				<div class="input-group">

					<input type="text" name="filter_search" id="filter_search" class="form-control"
					       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>

					<span class="input-group-btn">
						<button class="btn btn-default" type="submit"
						        onclick="this.form.submit();"><?php echo JText::_('COM_HOTSPOTS_GO'); ?></button>
						<button class="btn btn-default" type="button"
						        onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_HOTSPOTS_RESET'); ?></button>
					</span>
				</div>
			</div>

			<div class="col-md-8">
				<div class="form-inline">
					<div class="pull-right">
						<select name="filter_published" class="form-control" onchange="this.form.submit()">
							<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
							<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('archived' => 0, 'trash' => 0, 'all' => 0)), 'value', 'text', $this->state->get('filter.published'), true); ?>
						</select>
					</div>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-hover table-striped">
					<thead>
					<tr>
						<th>
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							       onclick="Joomla.checkAll(this)"/>
						</th>
						<th>
							<?php echo JText::_('COM_HOTSPOTS_KML_TITLE'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_HOTSPOTS_KML_DESCRIPTION'); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort', 'JCATEGORY', 'cat.cat_name', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_KML_ORIGINAL_FILENAME', 'kmls.original_filename', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JText::_('COM_HOTSPOTS_KML_MANGLED_FILE'); ?>
						</th>
						<th>
							<?php echo JText::_('JGLOBAL_FIELD_CREATED_LABEL'); ?>
						</th>
						<th>
							<?php echo JText::_('JGLOBAL_FIELD_CREATED_BY_LABEL'); ?>
						</th>
						<th>
							<?php echo JText::_('JSTATUS'); ?>
						</th>
					</tr>
					</thead>

					<tbody>
					<?php if ($this->kmls) : ?>
						<?php foreach ($this->kmls as $i => $kml) : ?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center" width="1%">
									<?php echo JHtml::_('grid.id', $i, $kml->hotspots_kml_id); ?>
								</td>
								<td>
									<a href="<?php echo JRoute::_('index.php?option=com_hotspots&task=kml.edit&hotspots_kml_id=' . $kml->hotspots_kml_id); ?>">
										<?php echo $kml->title; ?>
									</a>
								</td>
								<td>
									<?php echo $kml->description; ?>
								</td>
								<td>
									<?php echo $kml->cat_name; ?>
								</td>
								<td>
									<?php echo $kml->original_filename; ?>
								</td>
								<td>
									<?php echo $kml->mangled_filename; ?>
								</td>
								<td>
									<?php echo $kml->created; ?>
								</td>
								<td>
									<?php echo $kml->user_name; ?>
								</td>
								<td>
									<?php echo JHtml::_('jgrid.published', $kml->state, $i, 'kmls.'); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="12">
								<?php echo JText::_('COM_HOTSPOTS_NO_MATCHING_RESULTS'); ?>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>

					<tfoot>
					<tr>
						<td colspan="10" class="text-center"><?php echo $this->pagination->getListFooter(); ?></td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
