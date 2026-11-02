<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       25.08.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

?>

<script type="text/template" id="js-hs-send-map-template">
	<form action="<?php echo JURI::base() ?>index.php?option=com_hotspots&task=mail.send&format=raw" name="hotspots-mail-map" id="hotspots-mail-map"
	      class="form mail map" method="post">
		<?php if(!JFactory::getUser()->guest) : ?>
			<span class="fa-stack pull-right hs-fa-close js-hs-cancel btn-link" title="close tab">
				<i class="fa fa-circle-thin fa-stack-1x"></i>
				<i class="fa fa-times fa-stack-1x"></i>
			</span>

			<div class="muted small">
			You are logged in as <?php echo (JFactory::getUser()->name); ?>
				and we will send the mail from: <?php echo (JFactory::getUser()->email); ?>
			</div>
		<?php endif; ?>

		<div>
			<label for="mailto">
				<?php echo JText::_('COM_HOTSPOTS_EMAILTO'); ?>:
			</label>
			<input type="text" name="mailto" class="inputbox required validate-email js-hs-mail-mailto" size="25" value=""
			       placeholder="<?php echo JText::_('COM_HOTSPOTS_EMAILTO_TITLE'); ?>"/>
		</div>

		<?php if(JFactory::getUser()->guest) : ?>
			<div>
				<label for="sender">
						<?php echo JText::_('COM_HOTSPOTS_SENDER'); ?>:
				</label>
				<input type="text" name="sender"
				       class="js-hs-mail-sender"
				       placeholder="<?php echo JText::_('COM_HOTSPOTS_SENDER_TITLE'); ?>"
				       size="25"
					/>
			</div>

			<div>
				<label for="sender-email">
						<?php echo JText::_('COM_HOTSPOTS_YOUR_EMAIL'); ?>:
				</label>
				<input type="text"
				       name="sender-email"
				       id="sender-email"
				       class="inputbox js-hs-mail-sender-email"
				       size="25"
				       placeholder="<?php echo JText::_('COM_HOTSPOTS_YOUR_EMAIL_TITLE'); ?>"
					/>
			</div>
		<?php endif; ?>

		<div>
			<label for="subject">
				<?php echo JText::_('COM_HOTSPOTS_SUBJECT'); ?>:
			</label>
			<input type="text" name="subject"
			       id="subject"
			       class="inputbox js-hs-mail-subject"
			       value=""
			       size="25"
			       placeholder="<?php echo JText::_('COM_HOTSPOTS_SUBJECT_TITLE');?>"/>
		</div>

		<div>
			<label for="bodytext">
				<?php echo JText::_('COM_HOTSPOTS_BODYTEXT'); ?>:
			</label>
			<textarea name="bodytext" class="text_area js-hs-mail-textarea"
			          rows="10"
			          cols="40"
			          placeholder="<?php echo JText::_('COM_HOTSPOTS_BODYTEXT_TITLE'); ?>"></textarea>
		</div>

		<div class="alert alert-error hide">
		</div>
		<button type="submit" class="btn btn-primary js-hs-submit"><?php echo JText::_('COM_HOTSPOTS_SEND_MAP_BUTTON'); ?></button>
		<button type="submit" class="btn js-hs-cancel"><?php echo JText::_('JCANCEL'); ?></button>

		<?php echo JHTML::_('form.token'); ?>
	</form>
</script>

<script type="text/template" id="js-hs-send-map-template-sent-response">
	{{ status }}

	<button type="submit" class="btn js-hs-cancel"><?php echo JText::_('COM_HOTSPOTS_CLOSE_MAP_BUTTON'); ?></button>
</script>

