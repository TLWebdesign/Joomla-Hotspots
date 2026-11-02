<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2013 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use TLWebdesign\Component\Hotspots\Administrator\Helper\MarkerHelper;

\defined('_JEXEC') or die;

/**
 * @var \Joomla\Registry\Registry $params
 * @var object[]                  $list
 * @var array                     $images  Marker id => gallery images.
 */

if (!$list) {
    return;
}

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

if (!$wa->assetExists('style', 'mod_hotspots_list')) {
    $wa->registerStyle('mod_hotspots_list', 'mod_hotspots_list/mod_hotspots_list.css');
}

$wa->useStyle('mod_hotspots_list');

$e = static fn ($text): string => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
?>
<div class="hotspots-list">
    <ul>
        <?php foreach ($list as $marker) : ?>
            <?php $image = $images[$marker->id][0] ?? null; ?>
            <li>
                <a href="<?php echo $e($marker->link ?: MarkerHelper::markerUrl($marker)); ?>">
                    <?php if ($params->get('image', 0) && $image) : ?>
                        <div>
                            <img src="<?php echo $e($image['image'] ?? $image['thumb'] ?? $image['big']); ?>"
                                 alt="<?php echo $e($image['title'] ?: $marker->title); ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <h3><?php echo $e($marker->title); ?></h3>
                </a>
                <?php if ($params->get('description', 0)) : ?>
                    <div class="hotspots-list-description">
                        <?php echo HTMLHelper::_('string.truncateComplex', (string) $marker->description_small, 100); ?>
                    </div>
                <?php endif; ?>
                <?php if ($params->get('address', 0)) : ?>
                    <div class="hotspots-list-address text-muted">
                        <?php echo MarkerHelper::formattedAddress($marker); ?>
                    </div>
                <?php endif; ?>
                <?php if (($params->get('author', 1) && $marker->author !== '') || $params->get('date', 1)) : ?>
                    <div class="hotspots-list-info text-muted">
                        <?php if ($params->get('author', 1) && $marker->author !== '') : ?>
                            <?php echo Text::_('MOD_HOTSPOTS_LIST_CREATED_BY'); ?> <?php echo $e($marker->author); ?>
                        <?php endif; ?>
                        <?php if ($params->get('date', 1)) : ?>
                            <?php echo Text::_('MOD_HOTSPOTS_LIST_ON'); ?> <?php echo $e(MarkerHelper::localDate($marker->created)); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
