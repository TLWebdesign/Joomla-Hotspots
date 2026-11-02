<?php
/**
 * @package    Hotspots
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @copyright  Copyright (C) 2008 - 2014 Compojoom.com. All rights reserved.
 * @license    GNU GPL version 3 or later <http://www.gnu.org/licenses/gpl.html>
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Render Modal
echo HTMLHelper::_(
	'bootstrap.renderModal',
	'changelog',
	array(
		'title'      => Text::_("LIB_COMPOJOOM_BTN_CHANGELOG"),
//		'height'     => '400px',
//		'width'      => '800px',
//		'modalWidth' => 70,
//		'bodyHeight' => 80,
	),
	CompojoomChangelogColoriser::colorise(JPATH_COMPONENT_ADMINISTRATOR . '/CHANGELOG.php')
);

?>

<table width="100%" class="table table-version table-bordered table-striped-offset1 table-condensed">
	<tr>
		<th colspan="2">
			<div class="hotspots-logo">
				<img src="<?php echo JUri::root(); ?>media/com_hotspots/images/utils/logo.jpg" align="middle" alt="Hotspots logo"/>
			</div>
		</th>
	</tr>
	<tr>
		<td width="120"><?php echo JText::_('LIB_COMPOJOOM_INSTALLED_VERSION'); ?></td>
		<td>
			<span id="hs-label-version" class="label"><?php echo $this->currentVersion ?></span>&nbsp;
			<a id="hs-btn-changelog" class="btn btn-default btn-sm" data-toggle="modal" data-target="#changelog"
               onclick="document.getElementById('changelog').open();"
			   title="<?php echo JText::_('LIB_COMPOJOOM_BTN_CHANGELOG'); ?>">
				<i class="fa fa-list"></i>
			</a>
		</td>
	</tr>
	<tr>
		<td><?php echo JText::_('LIB_COMPOJOOM_RELEASED'); ?></td>
		<td><?php echo $this->releaseDate ?></td>
	</tr>
	<tr>
		<td><?php echo JText::_('LIB_COMPOJOOM_COPYRIGHT'); ?></td>
		<td>2008 - <?php echo date('Y'); ?> <a href="https://compojoom.com" target="_blank">Compojoom</a></td>
	</tr>
	<tr>
		<td><?php echo JText::_('LIB_COMPOJOOM_LICENSE'); ?></td>
		<td><a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GNU GPLv3 or later</a> Paid</td>
	</tr>
</table>
