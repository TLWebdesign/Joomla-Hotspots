<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       31.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('restricted access');
$user = JFactory::getUser();

JHTML::_('stylesheet', 'media/com_hotspots/css/hotspots.css');
JHTML::_('stylesheet', 'media/com_hotspots/css/hotspots-backend.css');
JText::script('COM_HOTSPOTS_GEOCODE');
JText::script('COM_HOTSPOTS_GEOCODING_NOTICE');
JText::script('COM_HOTSPOTS_CLOSE');
echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'hotspots', 'COM_HOTSPOTS_LOCATIONS', '');
if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
?>
<?php if (!HOTSPOTS_PRO): ?>
	<?php if ($this->pagination->total <= 100) : ?>
		<div class="alert alert-info">
			<?php echo JText::sprintf('COM_HOTSPOTS_CORE_VERSION_LIMIT_100_HOTSPOTS', $this->pagination->total); ?>
		</div>
	<?php else : ?>
		<div class="alert alert-info">
			<?php echo JText::sprintf('COM_HOTSPOTS_CORE_VERSION_NEED_MORE_THAN_100_HOTSPOTS', $this->pagination->total, 'https://compojoom.com/joomla-extensions/hotspots'); ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
	<form action="index.php?option=com_hotspots&view=hotspots" method="post" id="adminForm" name="adminForm">
		<div class="box-info full">
			<h2><?php echo $this->pagination->getResultsCounter(); ?></h2>

			<div class="additional-btn">
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="col-md-4">
				<div class="input-group">
					<input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_HOTSPOTS_FILTER'); ?>" id="filter_search"
					       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					       class="form-control"/>
						<span class="input-group-btn">
							<button class="btn btn-default" type="submit"
							        onclick="this.form.submit();"><?php echo JText::_('COM_HOTSPOTS_GO'); ?></button>
							<button class="btn btn-default" type="button"
							        onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_HOTSPOTS_RESET'); ?></button>

						</span>
				</div>
			</div>

			<div class="col-md-8">
				<div class="form-inline">
					<div class="pull-right">
						<?php echo $this->lists['language']; ?>
						<?php echo $this->lists['sectionid']; ?>


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
						<th width="5"><?php echo JText::_('COM_HOTSPOTS_NUM'); ?></th>
						<th width="5">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="title"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_TITLE', 'a.name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th class="title"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_DATE', 'a.created', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="15%"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_STREET', 'a.street', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="5%"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_PLZ', 'a.plz', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="10%"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_TOWN', 'a.town', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="10%"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_COUNTRY', 'a.country', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="10%"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_CATEGORY', 'cat.cat_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="10%"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_LATITUDE', 'a.gmlat', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="10%"><?php echo JHTML::_('grid.sort', 'COM_HOTSPOTS_LONGITUDE', 'a.gmlng', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th width="5%"
						    nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'a.published', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th><?php echo JHTML::_('grid.sort', 'JGLOBAL_FIELD_ID_LABEL', 'a.id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="12" class="text-center"><?php echo $this->pagination->getListFooter(); ?></td>
					</tr>
					</tfoot>
					<tbody>
					<?php if ($this->list) : ?>
						<?php
						$i = 0;
						foreach ($this->list as $l)
						{


							$link = JRoute::_('index.php?option=com_hotspots&task=hotspot.edit&id=' . $l->id);
							$canEdit = $user->authorise('core.edit', 'com_hotspots');
							$canChange = $user->authorise('core.edit.state', 'com_hotspots');

							$checked = JHTML::_('grid.id', $i, $l->id);
							$published = JHTML::_('jgrid.published', $l->state, $i, 'hotspots.', $canChange);
							?>
							<tr class="<?php echo "row" . $i % 2; ?>">
								<td><?php echo $this->pagination->getRowOffset($i); ?></td>
								<td>
									<?php echo $checked; ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
										<a href="<?php echo $link; ?>"><?php echo htmlspecialchars((string) $l->title, ENT_QUOTES, 'UTF-8', false); ?></a>
									<?php else : ?>
										<?php echo htmlspecialchars((string) $l->title, ENT_QUOTES, 'UTF-8', false); ?>
									<?php endif; ?>
								</td>
								<td>
									<?php echo HotspotsHelperUtils::getLocalDate($l->created); ?>
								</td>
								<td>
									<?php echo htmlspecialchars((string) $l->street, ENT_QUOTES, 'UTF-8', false); ?>
								</td>
								<td>
									<?php echo $l->plz; ?>
								</td>
								<td>
									<?php echo $l->town; ?>
								</td>
								<td>
									<?php echo $l->country; ?>
								</td>
								<td>
									<?php echo $l->cat_name; ?>
								</td>
								<td>
									<?php echo $l->gmlat; ?>
								</td>
								<td>
									<?php echo $l->gmlng; ?>
								</td>
								<td align="center">
									<?php echo $published; ?>
								</td>
								<td>
									<?php echo $l->id; ?>
								</td>
							</tr>
							<?php
							$i++;
						}
						?>
					<?php else: ?>
						<tr>
							<td colspan="12">
								<?php echo JText::_('COM_HOTSPOTS_NO_MATCHING_RESULTS'); ?>
							</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>"/>
		<?php echo JHTML::_('form.token'); ?>

        <?php echo JHtml::_(
            'bootstrap.renderModal',
            'geocodeModal',
            array(
                'title' => JText::_('COM_HOTSPOTS_GEOCODE'),
                'footer' => $this->loadTemplate('geocode_footer')
            ),
            $this->loadTemplate('geocode_body')
        ); ?>

		<?php echo JHtml::_(
				'bootstrap.renderModal',
				'collapseModal',
				array(
						'title' => JText::_('COM_HOTSPOTS_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer')
				),
				$this->loadTemplate('batch_body')
		); ?>


	</form>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
