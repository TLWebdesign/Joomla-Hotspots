<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

/**
 * Mail to the author of a marker. Data: hotspot, author (User), name, from, message.
 *
 * @var array $displayData
 */

$e = static fn ($text): string => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
?>
<?php echo Text::sprintf('COM_HOTSPOTS_CONTACT_AUTHOR_HELLO_LINE', $e($displayData['author']->name)); ?><br>
<br>
<?php echo Text::sprintf('COM_HOTSPOTS_CONTACT_AUTHOR_MEMBER_IS_CONTACTING', $e(Factory::getApplication()->get('sitename')), '"' . $e($displayData['hotspot']->title) . '"'); ?><br>
<br>
<?php echo Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_NAME'); ?>: <?php echo $e($displayData['name']); ?><br>
<?php echo Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_EMAIL'); ?>: <?php echo $e($displayData['from']); ?><br>
<?php echo Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_MESSAGE'); ?>:<br>
<?php echo nl2br($e($displayData['message'])); ?><br>
<br>
------------------------------------------------------------------------<br>
<?php echo Text::_('COM_HOTSPOTS_CONTACT_AUTHOR_PRIVACY'); ?>
