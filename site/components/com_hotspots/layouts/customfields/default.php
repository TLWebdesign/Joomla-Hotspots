<?php
/**
 * @package    Hotspots
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       09.11.2015
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$customfieldsItem = $displayData['customfieldsItem'];
$customfieldsConfig = $displayData['customfieldsConfig'];
?>

<?php if ($customfieldsItem && $customfieldsConfig) : ?>

		<?php foreach ($customfieldsItem as $key => $customFields): ?>
			<?php if (isset($customfieldsConfig[$key])) : ?>
				<?php if ($customFields): ?>
					<dl class="dl-horizontal">
						<dt><?php echo JText::_($customfieldsConfig[$key]->title); ?></dt>
						<dd><?php echo CompojoomFormCustom::render($customfieldsConfig[$key], $customFields, 'com_hotspots'); ?></dd>
					</dl>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>