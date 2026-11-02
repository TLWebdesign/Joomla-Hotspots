<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;

\defined('_JEXEC') or die;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Hotspot\HtmlView $this */

$item = $this->item;
$wa   = $this->getDocument()->getWebAssetManager();

$wa->useStyle('fontawesome');

if ($wa->assetExists('style', 'com_hotspots.hotspot')) {
    $wa->useStyle('com_hotspots.hotspot');
} else {
    $wa->registerAndUseStyle('com_hotspots.hotspot', 'com_hotspots/hotspot.css');
}

if ($this->canContact || $this->showContactNote) {
    $wa->registerAndUseScript('com_hotspots.contact-author', 'com_hotspots/contact-author.js', [], ['defer' => true]);
}

$shareUrl = Uri::getInstance()->toString(['scheme', 'host', 'port']) . $item->link;
?>
<div id="hotspots-id-<?php echo (int) $item->id; ?>" class="hotspots hotspots-item<?php echo $this->escape((string) $this->params->get('pageclass_sfx', '')); ?>">
    <?php if ($this->gallery) : ?>
        <div class="hotspots-gallery mb-3">
            <?php echo LayoutHelper::render('gallery.galleria', ['images' => $this->gallery], JPATH_ADMINISTRATOR . '/components/com_hotspots/layouts'); ?>
        </div>
    <?php endif; ?>

    <div class="page-header d-flex flex-wrap align-items-center gap-2">
        <h2 class="componentheading me-auto"><?php echo $this->escape($item->title); ?></h2>

        <?php if ($this->canContact || $this->showContactNote) : ?>
            <button type="button" class="btn btn-link hs-btn-contact-author">
                <span class="fa fa-envelope" aria-hidden="true"></span> <?php echo Text::_('COM_HOTSPOTS_CONTACT_AUTHOR'); ?>
            </button>
        <?php endif; ?>

        <?php if ($this->canEdit) : ?>
            <a href="<?php echo Route::_('index.php?option=com_hotspots&task=form.edit&id=' . (int) $item->id); ?>" class="btn btn-sm btn-secondary">
                <span class="fa fa-edit" aria-hidden="true"></span> <?php echo Text::_('JACTION_EDIT'); ?>
            </a>
        <?php endif; ?>

        <?php if ($this->canDelete) : ?>
            <a href="<?php echo Route::_('index.php?option=com_hotspots&task=hotspot.delete&id=' . (int) $item->id . '&' . Session::getFormToken() . '=1'); ?>"
               onclick="return confirm(<?php echo $this->escape(json_encode(Text::_('COM_HOTSPOTS_REMOVE_HOTSPOT_PERMANENT'))); ?>);"
               class="btn btn-sm btn-danger">
                <span class="fa fa-trash" aria-hidden="true"></span> <?php echo Text::_('JACTION_DELETE'); ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-6 order-md-2 mb-3">
            <div class="hs-map-card-container single-view box-shadow">
                <?php echo $this->renderMap(); ?>
                <?php echo $this->loadTemplate('one_line_address'); ?>
            </div>
        </div>

        <div class="col-md-6 order-md-1">
            <div class="hotspots-description">
                <?php echo $item->description_small; ?>
                <?php echo $item->description; ?>
            </div>

            <?php if ($this->customFields && $this->customValues) : ?>
                <div class="hotspots-customfields">
                    <h3 class="h6"><?php echo Text::_('COM_HOTSPOTS_CUSTOM_FIELDS'); ?></h3>
                    <dl class="dl-horizontal">
                        <?php foreach ($this->customValues as $slug => $value) : ?>
                            <?php if (isset($this->customFields[$slug]) && $value !== '' && $value !== null && $value !== []) : ?>
                                <dt><?php echo $this->escape(Text::_($this->customFields[$slug]->title)); ?></dt>
                                <dd><?php echo CustomfieldsHelper::render($this->customFields[$slug], $value); ?></dd>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </dl>
                </div>
            <?php endif; ?>

            <?php if ($this->params->get('social_media', 0)) : ?>
                <div class="hs-social">
                    <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"
                       href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode($shareUrl); ?>">
                        <span class="fab fa-facebook" aria-hidden="true"></span> Facebook
                    </a>
                    <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"
                       href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode($shareUrl); ?>&amp;text=<?php echo rawurlencode((string) $item->title); ?>">
                        <span class="fab fa-x-twitter" aria-hidden="true"></span> X
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (($this->params->get('show_author', 1) && $this->author !== '') || $this->params->get('show_date', 1)) : ?>
        <div class="hs-creation-info text-end text-muted small">
            <?php if ($this->params->get('show_author', 1) && $this->author !== '') : ?>
                <?php echo Text::_('COM_HOTSPOTS_POSTED_BY'); ?>
                <?php if ($this->profile !== '') : ?>
                    <a href="<?php echo $this->escape($this->profile); ?>"><?php echo $this->escape($this->author); ?></a>
                <?php else : ?>
                    <?php echo $this->escape($this->author); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->params->get('show_date', 1) && $item->postdate !== '') : ?>
                <?php echo Text::_('COM_HOTSPOTS_ON'); ?> <?php echo $this->escape($item->postdate); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($this->canContact || $this->showContactNote) : ?>
        <?php echo $this->loadTemplate('contact_author_form'); ?>
    <?php endif; ?>
</div>
