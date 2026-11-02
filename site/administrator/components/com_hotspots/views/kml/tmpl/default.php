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

$editor = JEditor::getInstance(JFactory::getConfig()->get('editor'));


JHTML::_('stylesheet', 'media/com_hotspots/css/hotspots-backend.css');
if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'cpanel', '', '');
?>

	<form enctype="multipart/form-data"
	      action="<?php echo JRoute::_('index.php?option=com_hotspots&view=kml&hotspots_kml_id=' . (int) $this->item->hotspots_kml_id); ?>"
	      method="post" class="form-horizontal" name="adminForm" id="adminForm">
		<div class="row">
			<div class="col-sm-8">
				<div class="box-info">
					<h2>
						<?php echo empty($this->item->hotspots_kml_id) ?
							JText::_('COM_HOTSPOTS_NEW_KML') : JText::sprintf('COM_HOTSPOTS_EDIT_KML', $this->item->hotspots_kml_id); ?>
					</h2>

					<div class="form-group">
						<?php echo $this->form->getLabel('title'); ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('catid'); ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('catid'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('state'); ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('state'); ?>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('hotspots_kml_id'); ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('hotspots_kml_id'); ?>
						</div>
					</div>
					<?php if ($this->item->mangled_filename) : ?>
						<div class="form-group">
							<label class="col-sm-2 compojoom-control-label">
								<?php echo JText::_('COM_HOTSPOTS_CURRENT_KML_FILE'); ?>
							</label>

							<div class="col-sm-10">
								<a href="<?php echo JURI::root() . 'media/com_hotspots/kmls/' . $this->item->mangled_filename; ?>">
									<?php echo $this->item->original_filename; ?>
								</a>
							</div>
						</div>
					<?php endif; ?>
					<div class="form-group">
						<?php echo $this->form->getLabel('kml_file'); ?>
						<div class="col-sm-10">
							<?php echo $this->form->getInput('kml_file'); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('description'); ?>
						<div class="col-sm-10">
							<?php echo $this->form->getinput('description'); ?>
						</div>
					</div>

				</div>
			</div>
			<div class="col-sm-4">
				<div class="box-info">
					<h2><?php echo JText::_('COM_HOTSPOTS_FIELDSET_PUBLISHING'); ?></h2>

					<div class="form-group">
						<?php echo $this->form->getLabel('created_by'); ?>
						<div class="col-sm-8">
							<?php echo $this->form->getInput('created_by'); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('created'); ?>
						<div class="col-sm-8">
							<?php echo $this->form->getInput('created'); ?>
						</div>
					</div>

				</div>
			</div>
		</div>
		<input type="hidden" name="task" value=""/>
		<?php echo JHTML::_('form.token'); ?>
	</form>

<script type="text/javascript">
	jQuery(document).ready(function() {
		//FILE INPUT
		jQuery('input[type=file]').bootstrapFileInput({buttonWord: '<?php echo JText::_('COM_HOTSPOTS_BROWSE_FILES'); ?>'});
	});
</script>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
