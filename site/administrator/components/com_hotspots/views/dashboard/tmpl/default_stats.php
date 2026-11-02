<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$stats = HotspotsHelperStats::getStats();
$state = array(
	'all' => '',
	'published' => '1',
	'unpublished' => '0'
);

?>

<table>
	<tbody>
	<?php foreach($stats as $key => $value) : ?>

		<tr>
			<td>
				<?php echo JText::_('COM_HOTSPOTS_STATS_'.strtoupper($key)); ?>
			</td>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_hotspots&view=hotspots&filter_published='.$state[$key]); ?>">
					<?php echo $value; ?>
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>