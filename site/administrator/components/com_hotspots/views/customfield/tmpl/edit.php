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

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'customfields', '', '');
?>

	<form id="adminForm" method="post"
	      action="<?php echo JRoute::_('index.php?option=com_hotspots&view=customfield&id=' . (int) $this->item->id); ?>"
	      class="form-horizontal">
		<div class="box-info">
			<div class="form-group">
				<?php echo $this->form->getLabel('title'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('title'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('title')->description); ?></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('slug'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('slug'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('slug')->description); ?></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('show'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('show'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('show')->description); ?></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('catid'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('catid'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('catid')->description); ?></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('type'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('type'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('type')->description); ?></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('options'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('options'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('options')->description); ?></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('default'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('default'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('default')->description); ?></p>
				</div>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('allow_empty'); ?>
				<div class="col-sm-10">
					<?php echo $this->form->getInput('allow_empty'); ?>
					<p class="help-block"><?php echo JText::_($this->form->getField('allow_empty')->description); ?></p>
				</div>
			</div>
		</div>
		<?php echo $this->form->getInput('component'); ?>
		<input type="hidden" name="task" value=""/>
		<?php echo JHTML::_('form.token'); ?>
	</form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
