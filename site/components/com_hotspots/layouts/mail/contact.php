<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       12.05.2015
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

?>

<?php echo JText::sprintf('COM_HOTSPOTS_CONTACT_AUTHOR_HELLO_LINE', $displayData['author']->name); ?><br />
<br />
<?php echo JText::sprintf('COM_HOTSPOTS_CONTACT_AUTHOR_MEMBER_IS_CONTACTING',
	JFactory::getConfig()->get('sitename'), '"' . $displayData['hotspot']->title . '"'); ?><br />
<br />

<?php echo JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_NAME'); ?>: <?php echo $displayData['name']; ?><br />
<?php echo JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_EMAIL'); ?>: <?php echo $displayData['from']; ?><br />
<?php echo JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_MESSAGE'); ?>:<br />
<?php echo $displayData['message']; ?><br />

------------------------------------------------------------------------<br />
<?php echo JText::_('COM_HOTSPOTS_CONTACT_AUTHOR_PRIVACY'); ?>
