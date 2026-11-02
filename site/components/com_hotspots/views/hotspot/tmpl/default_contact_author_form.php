<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       24.02.16
 *
 * @copyright  Copyright (C) 2008 - 2016 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();
$config = JComponentHelper::getParams('com_hotspots');
$contactAuthorGroups = $config->get('authorized_contact_author_groups');
$showMessageToNonAuthorized = $config->get('show_contact_author_message_authorized');

$authorized = HotspotsHelperSecurity::groupHasAccess($user->getAuthorisedGroups(), $contactAuthorGroups);

?>
<?php if ($authorized) : ?>
<div class="row-fluid">
	<div id="hs-contact-author" class="span12 box-shadow hide" style="margin: 20px 0">

		<div class="alert alert-warning">
			<?php echo JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_WARNING_VISIBLE_EMAIL'); ?>
		</div>
		<form
				action="<?php echo JRoute::_('index.php?option=com_hotspots&task=mail.contact&format=raw'); ?>"
				class=""
				method="post">
			<?php if ($user->guest) : ?>
				<div class="form-group">
					<label for="hs-contactform-name"><?php echo JText::_('COM_HOTSPOTS_YOUR_NAME'); ?>
						*:</label>
					<input type="text" class="form-control input-large" name="name" id="hs-contactform-name"
						   placeholder="<?php echo JText::_('COM_HOTSPOTS_YOUR_NAME_DESC'); ?>" required
					/>
				</div>
				<div class="form-group">
					<label for="hs-contactform-email"><?php echo JText::_('COM_HOTSPOTS_YOUR_EMAIL'); ?>
						*:</label>
					<input type="email" class="form-control input-large" name="email"
						   id="hs-contactform-email"
						   placeholder="<?php echo JText::_('COM_HOTSPOTS_YOUR_EMAIL_DESC'); ?>" required>
				</div>
			<?php endif ?>
			<div class="form-group">
				<label for="message"><?php echo JText::_('COM_HOTSPOTS_MESSAGE'); ?>*:</label>
				<textarea name="message" id="message" class="input-large"
						  placeholder="<?php echo JText::_('COM_HOTSPOTS_BODYTEXT_TITLE'); ?>"
						  required></textarea>
			</div>
			<?php if (JPluginHelper::isEnabled('captcha', HotspotsHelperSettings::get('captcha')) && HotspotsHelperSettings::get('captcha')): ?>
				<?php
				$captcha = JCaptcha::getInstance(HotspotsHelperSettings::get('captcha'));
				echo $captcha->display('compojoom-captcha', 'compojoom-contact-captcha', 'g-recaptcha');
				?>
			<?php endif; ?>
			<input type="hidden" name="hotspot_id" value="<?php echo $this->hotspot->id; ?>"/>
			<?php echo JHTML::_('form.token'); ?>
			<button type="submit"
					class="btn btn-default btn-primary"><?php echo JText::_('COM_HOTSPOTS_SUBMIT'); ?></button>
			<button type="reset" class="btn hs-btn-contact-author-cancel" onClick="">Cancel</button>
		</form>

	</div>
</div>
<?php else: ?>
	<?php if($showMessageToNonAuthorized) : ?>
			<div class="row-fluid">
				<div id="hs-contact-author" class="span12 box-shadow hide" style="margin: 20px 0">
					<div>
						<?php echo JText::sprintf('COM_HOTSPOTS_YOU_ARE_NOT_AUTHORISED_TO_CONTACT_THE_AUTHOR', JRoute::_('index.php?option=com_users&view=login&return='.base64_encode(JUri::getInstance()->toString().'#hs-contact'))); ?>
					</div>
				</div>
			</div>
	<?php endif; ?>
<?php endif; ?>
