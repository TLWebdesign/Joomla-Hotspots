<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('restricted access');

// TLWebdesign patch 2026-11-02: removed the compojoom.com update check, JED prompt, news feed, download ID warnings and Joomla 2.5/3 branches.

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$extensionName = 'Hotspots';
$document = \Joomla\CMS\Factory::getDocument();

if($this->needsMultimediaUpdate['update']) {
	$document->addScriptDeclaration(
		"(function($) {
			$(document).ready(function(){
				$.ajax('index.php?option=com_hotspots&task=update.migrateImages&tmpl=component&" . JSession::getFormToken() . "=1', {
					dataType: 'json',
					success: function(msg) {
						if(msg.success)
						{
							var container = $('.multimedia-update-status').removeClass('hide');
							container.append( \"<div>\" + msg.message + \"</div>\" );
							if(msg.shouldContinue)
							{
								$.ajax(this);
							}
						}
					}
				});
			});
		})(jQuery);"
	);
}

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'dashboard', 'COM_HOTSPOTS_DASHBOARD', '');

?>

<?php if($this->needsMultimediaUpdate['update']): ?>
	<div class="alert alert-danger">
		<?php echo JText::sprintf('LIB_COMPOJOOM_MULTIMEDIA_NEEDS_UPDATE', $this->needsMultimediaUpdate['update']); ?>
	</div>
	<div class="alert alert-info multimedia-update-status hide">

	</div>
<?php endif; ?>

<?php if($this->needsMultimediaUpdate['nonexisting']): ?>
	<div class="alert alert-danger">
		<?php foreach($this->needsMultimediaUpdate['nonexisting'] as $nonExisting) : ?>
			<div>
				<?php echo JText::sprintf('LIB_COMPOJOOM_FILE_MULTIMEDIA_NEEDS_TO_BE_DELETED',
					$nonExisting->mangled_filename,
					'index.php?option=com_hotspots&task=hotspot.edit&id=' . $nonExisting->item_id,
					$nonExisting->item_id
				); ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

	<div class="row">
		<div class="col-sm-6">
			<div class="box-info full">
				<?php echo $this->loadTemplate('map'); ?>
			</div>
		</div>

		<div class="col-sm-6">
			<div class=" box-info full">

                    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'stats')); ?>

                        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'stats', Text::_('COM_HOTSPOTS_STATS')); ?>
                            <?php echo $this->loadTemplate('stats'); ?>
                        <?php echo HTMLHelper::_('uitab.endTab'); ?>

                        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'version', Text::_('COM_HOTSPOTS_VERSION')); ?>
                            <?php echo $this->loadTemplate('version'); ?>
                        <?php echo HTMLHelper::_('uitab.endTab'); ?>
                
                    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12">
			<div class="box-info">
				<strong>
					Hotspots <?php echo HOTSPOTS_PRO ? 'Professional' : 'Core' ?> <?php echo HOTSPOTS_VERSION; ?>
				</strong>
				<br/>

			<span style="font-size: x-small">
				Copyright &copy;2008&ndash;2023 Daniel Dimitrov / compojoom.com. Maintained by TLWebdesign since 2026.
			</span>
				<br/>
			<span style="font-size: x-small">
				Hotspots is Free software released under the
				<a href="https://www.gnu.org/licenses/gpl.html">GNU General Public License,</a>
				version 2 of the license or &ndash;at your option&ndash; any later version
				published by the Free Software Foundation.
			</span>
			</div>
		</div>
	</div>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
?>


<?php

?>
