<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Site\Helper\CaptchaHelper;

\defined('_JEXEC') or die;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Hotspot\HtmlView $this */

$user    = $this->getCurrentUser();
$captcha = CaptchaHelper::plugin();
?>
<div id="hs-contact-author" class="hs-contact-author card my-3 d-none">
    <div class="card-body">
        <?php if ($this->canContact) : ?>
            <div class="alert alert-warning">
                <?php echo Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_WARNING_VISIBLE_EMAIL'); ?>
            </div>
            <form action="<?php echo Route::_('index.php?option=com_hotspots&task=mail.contact'); ?>" method="post" class="form-validate">
                <?php if ($user->guest) : ?>
                    <div class="mb-3">
                        <label class="form-label" for="hs-contactform-name"><?php echo Text::_('COM_HOTSPOTS_YOUR_NAME'); ?> *</label>
                        <input type="text" class="form-control" name="name" id="hs-contactform-name" required
                               placeholder="<?php echo $this->escape(Text::_('COM_HOTSPOTS_YOUR_NAME_DESC')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="hs-contactform-email"><?php echo Text::_('COM_HOTSPOTS_YOUR_EMAIL'); ?> *</label>
                        <input type="email" class="form-control" name="email" id="hs-contactform-email" required
                               placeholder="<?php echo $this->escape(Text::_('COM_HOTSPOTS_YOUR_EMAIL_DESC')); ?>">
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label" for="hs-contactform-message"><?php echo Text::_('COM_HOTSPOTS_MESSAGE'); ?> *</label>
                    <textarea name="message" id="hs-contactform-message" class="form-control" rows="5" required
                              placeholder="<?php echo $this->escape(Text::_('COM_HOTSPOTS_BODYTEXT_TITLE')); ?>"></textarea>
                </div>
                <?php if ($captcha !== '') : ?>
                    <div class="mb-3"><?php echo CaptchaHelper::display($captcha, 'hs_captcha', 'hs-contact-captcha'); ?></div>
                <?php endif; ?>
                <input type="hidden" name="hotspot_id" value="<?php echo (int) $this->item->id; ?>">
                <?php echo HTMLHelper::_('form.token'); ?>
                <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_HOTSPOTS_SUBMIT'); ?></button>
                <button type="reset" class="btn btn-secondary hs-btn-contact-author-cancel"><?php echo Text::_('JCANCEL'); ?></button>
            </form>
        <?php else : ?>
            <?php $login = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString() . '#hs-contact')); ?>
            <?php echo Text::sprintf('COM_HOTSPOTS_YOU_ARE_NOT_AUTHORISED_TO_CONTACT_THE_AUTHOR', $login); ?>
        <?php endif; ?>
    </div>
</div>
