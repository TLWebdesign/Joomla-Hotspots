<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Layout\LayoutHelper;
$published = $this->state->get('filter.published');
?>

<div class="row">
	<div class="col-sm-6">
		<div class="controls">
            <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="controls">
            <?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
		</div>
	</div>
</div>
<div class="row">
	<?php if ($published >= 0) : ?>
		<div class="col-sm-6">
			<div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_hotspots']); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
