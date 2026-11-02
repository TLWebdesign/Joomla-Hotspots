<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       12.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');

if (JVERSION > 3)
{
	JHtml::_('formbehavior.chosen', 'select');
}

$isPro = HOTSPOTS_PRO;

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'import', 'COM_HOTSPOTS_IMPORT', '');

if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
?>
<?php if(!$isPro): ?>
	<div class="alert alert-notice">
		<?php echo JText::sprintf('COM_HOTSPOTS_FEATURE_PRO', 'Import', 'https://compojoom.com/joomla-extensions/hotspots'); ?>
	</div>
<?php endif; ?>
	<div class="row">
		<div class="col-sm-3 col-xs-6">
			<div class="box-info animated bounceIn">
				<h2>
					<span class="fa-stack">
					  <i class="fa fa-circle fa-stack-2x success"></i>
					  <i class="fa fa-exchange fa-stack-1x fa-inverse"></i>
					</span>
					SobiPRO
				</h2>
				<p class="text-center">
					<a class="btn btn-default <?php echo !$isPro ? 'disabled' : ''; ?>"
					   href="<?php echo JRoute::_('index.php?option=com_hotspots&view=import&layout=sobipro'); ?>">
						Import
					</a>
				</p>
			</div>
		</div>
		<div class="col-sm-3 col-xs-6">
			<div class="box-info animated bounceIn">
				<h2>
					<span class="fa-stack">
					  <i class="fa fa-circle fa-stack-2x success"></i>
					  <i class="fa fa-exchange fa-stack-1x fa-inverse"></i>
					</span>
					KML
				</h2>
				<p class="text-center">
					<a class="btn btn-default  <?php echo !$isPro ? 'disabled' : ''; ?>"
					   href="<?php echo JRoute::_('index.php?option=com_hotspots&view=import&layout=kml'); ?>">
						Import
					</a>
				</p>
			</div>
		</div>
		<div class="col-sm-3 col-xs-6">
			<div class="box-info animated bounceIn">
				<h2>
					<span class="fa-stack">
					  <i class="fa fa-circle fa-stack-2x success"></i>
					  <i class="fa fa-exchange fa-stack-1x fa-inverse"></i>
					</span>
					Phoca Maps
				</h2>
				<p class="text-center">
					<a class="btn btn-default  <?php echo !$isPro ? 'disabled' : ''; ?>"
					   href="<?php echo JRoute::_('index.php?option=com_hotspots&task=import.importFromPhoca'); ?>">
						Import
					</a>
				</p>
			</div>
		</div>
	</div>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
