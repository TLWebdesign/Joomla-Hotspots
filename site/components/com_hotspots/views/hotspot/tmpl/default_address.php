<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>

<?php if (HotspotsHelperSettings::get('show_address', 1)) : ?>
	<?php echo HotspotsHelperHotspot::formattedAddress($this->hotspot); ?>
<?php endif; ?>