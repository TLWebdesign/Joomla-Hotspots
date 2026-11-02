<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       10.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

Jhtml::stylesheet('media/mod_hotspots_list/css/mod_hotspots_list.css');
JLoader::discover('HotspotsHelper', JPATH_BASE . '/components/com_hotspots/helpers/');
$suffix = $params->get('moduleclass_sfx');
?>
<div class="hotspots-list hotspots-list<?php echo $suffix; ?>">
	<ul>
		<?php foreach ($list as $hotspot) : ?>
			<?php $hotspot->hotspots_id = $hotspot->id; ?>
			<li>
				<a href="<?php echo HotspotsHelperUtils::createLink($hotspot); ?>">
					<?php if ($params->get('image', 1)) : ?>
						<?php if (isset($images[$hotspot->id])) : ?>
							<div>
								<img src="<?php echo $images[$hotspot->id][0]['image']; ?>"
									 title="<?php echo $images[$hotspot->id][0]['title']; ?>"
									 description="<?php echo $images[$hotspot->id][0]['description']; ?>"/>
							</div>
						<?php endif; ?>
					<?php endif; ?>
					<h3><?php echo $hotspot->title; ?></h3>
				</a>
				<?php if ($params->get('description', 1)) : ?>
					<div class="hotspots-list-description">
						<?php echo CompojoomHtmlString::truncateComplex($hotspot->description_small, 100); ?>
					</div>
				<?php endif; ?>
				<?php if ($params->get('address', 1)) : ?>
					<div class="hotspots-list-address muted">
						<?php echo HotspotsHelperHotspot::formattedAddress($hotspot); ?>
					</div>
				<?php endif; ?>
				<?php if ($params->get('author', 1) || $params->get('date', 1)) : ?>
					<div class="hotspots-list-info muted">
						<?php if ($params->get('author', 1)) : ?>
							<?php echo JText::_('MOD_HOTSPOTS_LIST_CREATED_BY') ?>
							<?php if ($hotspot->created_by) : ?>
								<?php echo $hotspot->user_name; ?>
							<?php else: ?>
								<?php echo $hotspot->created_by_alias; ?>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ($params->get('date', 1)) : ?>
							<?php echo JText::_('MOD_HOTSPOTS_LIST_ON'); ?>
							<?php echo HotspotsHelperUtils::getLocalDate($hotspot->created); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</li>

		<?php endforeach; ?>
	</ul>
</div>