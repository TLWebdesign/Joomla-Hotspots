<?php
/**
 * @author     Daniel Dimitrov
 * @date: 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted Access');
?>
<?php if (HotspotsHelperSettings::get('footer', 1) == 1) : ?>
	<div class="hotspots-footer text-center small">
		<div class="hotspots-footer-box">
			<?php echo JText::_('COM_HOTSPOTS_POWERED_BY'); ?> <a href="http://www.compojoom.com"
			                                                      title="Joomla extensions, modules and plugins">compojoom.com</a>
		</div>
	</div>
<?php endif; ?>