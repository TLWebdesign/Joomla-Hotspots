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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}

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

$document->addScriptDeclaration("(function($) {
		$(document).ready(function(){
			$.ajax('index.php?option=com_hotspots&task=update.updateinfo&tmpl=component&". JSession::getFormToken() . "=1', {
				success: function(msg, textStatus, jqXHR)
				{
					// Get rid of junk before and after data
					var match = msg.match(/###([\s\S]*?)###/);
					data = match[1];

					if (data.length)
					{
						$('#updateNotice').html(data);
					}
				}
			});
			$.ajax('index.php?option=com_hotspots&task=jed.reviewed&tmpl=component&". JSession::getFormToken() . "=1', {
				success: function(msg, textStatus, jqXHR)
				{
					// Get rid of junk before and after data
					var match = msg.match(/###([\s\S]*?)###/);
					data = match[1];

					if (data.length)
					{
						$('#jedNotice').html(data);
					}
				}
			})
		});
	})(jQuery);");

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'dashboard', 'COM_HOTSPOTS_DASHBOARD', '');

?>

<?php if (HOTSPOTS_PRO && (version_compare(JVERSION, '2.5.19', 'lt') || (version_compare(JVERSION, '3.0.0', 'gt') && version_compare(JVERSION, '3.2.1', 'lt')))):?>
	<div class="alert alert-danger">
		<?php echo JText::sprintf('LIB_COMPOJOOM_ERR_OLDJOOMLANOUPDATES', $extensionName); ?>
	</div>
<?php elseif (HOTSPOTS_PRO && version_compare(JVERSION, '2.5.999', 'lt') && !$this->updatePlugin): ?>
	<div class="alert alert-warning">
		<?php echo JText::sprintf('LIB_COMPOJOOM_ERR_NOPLUGINNOUPDATES', $extensionName, $extensionName); ?>
	</div>
<?php endif; ?>

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
<?php if($this->needsdlid): ?>
	<div class="alert alert-danger">
		<?php echo JText::sprintf('COM_HOTSPOTS_DASHBOARD_NEEDSDLID',
			'https://compojoom.com/download-ids',
			'https://compojoom.com/support/documentation/hotspots/ch02s06s02'); ?>
	</div>
<?php elseif ($this->needscoredlidwarning): ?>
	<div class="alert alert-danger">
		<?php echo JText::_('COM_HOTSPOTS_DASHBOARD_NEEDSUPGRADE'); ?>
	</div>
<?php endif; ?>

<div id="updateNotice"></div>
<div id="jedNotice"></div>

	<div class="row">
		<div class="col-sm-6">
			<div class="box-info full">
				<?php echo $this->loadTemplate('map'); ?>
			</div>
		</div>

		<div class="col-sm-6">
			<div class=" box-info full">
                <?php if (version_compare(JVERSION, '4.0', 'lt')): ?>
                    <ul class="nav nav-tabs nav-justified">
                        <li class="active">
                            <a data-toggle="tab" href="#stats">
                                <?php echo JText::_('COM_HOTSPOTS_STATS'); ?>
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#rss">
                                <?php echo JText::_('COM_HOTSPOTS_LATEST_NEWS'); ?>
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#version">
                                <?php echo JText::_('COM_HOTSPOTS_VERSION'); ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="stats" class="tab-pane active">
                            <?php echo $this->loadTemplate('stats'); ?>
                        </div>
                        <div id="rss" class="tab-pane">
                            <?php echo CompojoomHtmlFeed::renderFeed('https://compojoom.com/blog/tags/listings/hotspots?format=feed&amp;type=rss'); ?>
                        </div>
                        <div id="version" class="tab-pane">
                            <?php echo $this->loadTemplate('version'); ?>
                        </div>
                    </div>
                <?php else: ?>

                    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'stats')); ?>

                        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'stats', Text::_('COM_HOTSPOTS_STATS')); ?>
                            <?php echo $this->loadTemplate('stats'); ?>
                        <?php echo HTMLHelper::_('uitab.endTab'); ?>

                        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rss', Text::_('COM_HOTSPOTS_LATEST_NEWS')); ?>
                            <?php echo CompojoomHtmlFeed::renderFeed('https://compojoom.com/blog/tags/listings/hotspots?format=feed&amp;type=rss'); ?>

                        <?php echo HTMLHelper::_('uitab.endTab'); ?>


                        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'version', Text::_('COM_HOTSPOTS_VERSION')); ?>
                            <?php echo $this->loadTemplate('version'); ?>
                        <?php echo HTMLHelper::_('uitab.endTab'); ?>
                
                    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

                <?php endif; ?>
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
				Copyright &copy;2008&ndash;<?php echo date('Y'); ?> Daniel Dimitrov / compojoom.com
			</span>
				<br/>

				<strong>
					<?php
					$url = 'http://extensions.joomla.org/extensions/maps-a-weather/maps-a-locations/maps/24962';

					if (HOTSPOTS_PRO)
					{
						$url = 'http://extensions.joomla.org/extensions/maps-a-weather/maps-a-locations/maps/9468';
					}
					?>

					<?php echo JText::sprintf('LIB_COMPOJOOM_PLEASE_POST_RATING_REVIEW', 'Hotspots', $url ); ?>
				</strong>
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

<?php if (!HOTSPOTS_PRO) : ?>
	<p class="alert alert-warning"><?php echo JText::sprintf('COM_HOTSPOTS_UPGRADE_TO_PRO', 'https://compojoom.com/joomla-extensions/hotspots'); ?></p>
<?php endif; ?>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
?>


<?php

?>
