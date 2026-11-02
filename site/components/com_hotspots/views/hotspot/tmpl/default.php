<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       27.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Specify title of the page
$document = JFactory::getDocument();
$document->setTitle($this->hotspot->title . ' - ' . JFactory::getConfig()->get('sitename'));

HotspotsHelperUtils::getJsLocalization();
CompojoomHtmlBehavior::jquery();

if (HotspotsHelperSettings::get('emulate_bootstrap', 1))
{
	JHTML::_('stylesheet', 'media/lib_compojoom/css/bootstrap-232.css');
}

JHTML::_('stylesheet', 'media/com_hotspots/css/hotspot.css');
JHTML::_('script', HotspotsHelperUtils::getGmapsUrl());

JHTML::stylesheet('media/lib_compojoom/third/font-awesome/css/font-awesome.min.css');

$marker = array(
	'id'          => $this->hotspot->id,
	'lat'         => (float) $this->hotspot->gmlat,
	'lng'         => (float) $this->hotspot->gmlng,
	'title'       => $this->hotspot->title,
	'description' => $this->loadTemplate('description'),
	'icon'        => $this->category->cat_icon
);

if ($this->hotspot->params->get('markerimage'))
{
	$marker['icon'] = HOTSPOTS_PICTURE_CATEGORIES_PATH . $this->hotspot->params->get('markerimage');
}

CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		HotspotsHelperUtils::createJsonMarker($marker),
		HotspotsHelperUtils::createJSConfig()
	)
);
CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/com_hotspots/js/v4/vendor/handlebars-v2.0.0.js',
		'media/lib_compojoom/js/jquery.ui.custom.js',
		'media/com_hotspots/js/v4/vendor/jquery.cookie.js',
		'media/com_hotspots/js/v4/vendor/underscore.js',
		'media/com_hotspots/js/v4/vendor/backbone.js',
		'media/com_hotspots/js/v4/vendor/backbone.marionette.js',
		'media/com_hotspots/js/v4/vendor/backbone.picky.js',
		'media/com_hotspots/js/v4/vendor/backbone.stickit.js',
		'media/com_hotspots/js/v4/vendor/backbone.validator.js',
		'media/com_hotspots/js/v4/vendor/backbone.googlemaps.js',
		'media/com_hotspots/js/v4/vendor/backbone.paginator.js',
		'media/com_hotspots/js/v4/overrides/backbone.marionette.js',
		'media/com_hotspots/js/v4/app.js',

		'media/com_hotspots/js/v4/entities/map.js',
		'media/com_hotspots/js/v4/entities/direction.js',
		'media/com_hotspots/js/v4/entities/marker.js',

		'media/com_hotspots/js/v4/common/views.js',
		'media/com_hotspots/js/v4/common/map/show/show_view.js',
	)
);

CompojoomHtml::addScriptsToQueue(
	'hotspots', array(
		'media/com_hotspots/js/v4/subapps/hotspot/map/map_app.js',
		'media/com_hotspots/js/v4/subapps/hotspot/map/show/show_controller.js',

		'media/com_hotspots/js/v4/subapps/hotspot/map/show/show_view.js',
		'media/com_hotspots/js/v4/subapps/hotspot/map/marker/marker_controller.js',
		'media/com_hotspots/js/v4/subapps/hotspot/map/marker/marker_view.js'
	)
);

if (HotspotsHelperSettings::get('show_marker_directions', 1))
{
	CompojoomHtml::addScriptsToQueue(
		'hotspots', array(
			'media/com_hotspots/js/v4/subapps/hotspot/directions/directions_app.js',
			'media/com_hotspots/js/v4/subapps/hotspot/directions/show/show_controller.js',
			'media/com_hotspots/js/v4/subapps/hotspot/directions/show/show_view.js'
		)
	);
}

$currentUser = JFactory::getUser();
$layout      = new CompojoomLayoutFile('galleria.galleria');

if (JPluginHelper::isEnabled('captcha', 'recaptcha'))
{
	JPluginHelper::importPlugin('captcha');
	$appl = JFactory::getApplication();
	$appl->triggerEvent('onInit', array('dynamic_recaptcha_1'));
}


$meta = array();

if (HotspotsHelperSettings::get('ogp', 0))
{
	$meta = array(
		'title'       => $this->hotspot->title,
		'description' => $this->hotspot->description_small,
		'image'       => isset($this->galleria[0]) ? $this->galleria[0]['image'] : '',
		'type'        => 'place',
		'lat'         => $this->hotspot->gmlat,
		'lng'         => $this->hotspot->gmlng,
	);

	CompojoomOgp::add($meta);
}

$config                     = JComponentHelper::getParams('com_hotspots');
$contactAuthorGroups        = $config->get('authorized_contact_author_groups');
$showMessageToNonAuthorized = $config->get('show_contact_author_message_authorized');

$authorizedContact = HotspotsHelperSecurity::groupHasAccess($currentUser->getAuthorisedGroups(), $contactAuthorGroups)
?>

	<script type="text/template" id="js-hs-main-region">
		<div id="js-map-region" class="hs-map-region"></div>
	</script>

	<script type="text/template" id="main-map-template">
		<div id="map-container"></div>
	</script>

	<script type="text/template" id="js-hs-item-direction">
		<div class="js-hs-get-directions hs-get-directions text-right">
			<div class="input-prepend  input-append control-group visible-phone">
				<span class="add-on hs-quick-dir active"
					  data-id="from"><?php echo JText::_('COM_HOTSPOTS_FROM'); ?></span>
				<input type="text" value="" class="input-min js-hs-input-dir-phone"
					   placeholder="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"
					   style="width:125px"
					   title="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>">
				<button class="btn js-hs-search"><span class="fa fa-search"></span></button>
			</div>
			<div class="input-prepend input-append control-group hidden-phone">
				<span class="add-on hs-quick-dir" data-id="to"><?php echo JText::_('COM_HOTSPOTS_TO'); ?></span>
				<span class="add-on hs-quick-dir active"
					  data-id="from"><?php echo JText::_('COM_HOTSPOTS_FROM'); ?></span>
				<input type="text" value="" class="input-small js-hs-input-dir-desktop" id="test"
					   placeholder="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>"
					   title="<?php echo JText::_('COM_HOTSPOTS_ADDRESS_OR_LANDMARK'); ?>">
				<button class="btn js-hs-search"><span class="fa fa-search"></span></button>
				<button class="btn js-hs-close"><span class="fa fa-times"></span></button>
			</div>

		</div>
		<div class="hs-directions-results"></div>
	</script>

	<script type="text/template" id="hs-fullscreen-template">
		<div class="hs-buttons-map">
			<?php if (HotspotsHelperSettings::get('resize_map', 1)): ?>
				<div class="js-hs-fullscreen-toggle"
					 title="<?php echo JText::_('COM_HOTSPOTS_CENTER_TOGGLE_FULLSCREEN'); ?>">
					<span class="fa fa-expand"></span>
				</div>
			<?php endif; ?>
		</div>
	</script>

	<div id="hotspots-id-<?php echo $this->hotspot->id; ?>" class="hotspots compojoom-bootstrap">
		<?php if ($this->galleria) : ?>
			<div class="row-fluid">
				<div class="span12">
					<?php echo $layout->render(array('data' => json_encode($this->galleria))); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="row-fluid">
			<h2 class="componentheading">
				<?php echo $this->hotspot->title; ?>

				<?php if ($this->hotspot->created_by && HotspotsHelperSettings::get('show_contact_author', 0)): ?>
					<?php if ($authorizedContact || $showMessageToNonAuthorized) : ?>
						<button class="btn btn-link pull-right hs-btn-contact-author">
							<span class="fa fa-envelope-o"> <?php echo JText::_('COM_HOTSPOTS_CONTACT_AUTHOR'); ?></span>
						</button>
					<?php endif; ?>
				<?php endif; ?>


				<?php if (HotspotsHelperSecurity::authorise('edit', $this->hotspot)) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_hotspots&task=form.edit&id=' . $this->hotspot->id); ?>"
					   class="btn btn-small">
						<span class="fa fa-edit"></span> <?php echo JText::_('JACTION_EDIT'); ?>
					</a>
				<?php endif; ?>
				<?php if (HotspotsHelperSecurity::authorise('delete', $this->hotspot)): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_hotspots&task=hotspots.delete&cid=' . $this->hotspot->id . '&' . JSession::getFormToken() . '=1'); ?> "
					   onClick="return confirm('<?php echo JText::_('COM_HOTSPOTS_REMOVE_HOTSPOT_PERMANENT', true); ?>');"
					   class="btn btn-small">
						<span class="fa fa-trash-o"></span> <?php echo JText::_('JACTION_DELETE'); ?>
					</a>
				<?php endif; ?>
			</h2>

			<div class="row-fluid">
				<div class="pull-right span6">
					<div id="" class="hs-map-card-container single-view box-shadow"
						 style="width: 100%;position: relative;">
						<div id="js-hs-main-app" class="hotspots hs-main-region"></div>
						<?php echo $this->loadTemplate('one_line_address'); ?>

						<div id="js-hs-item-direction-region" class="text-right"></div>
					</div>
				</div>

				<div class="hotspots-description">
					<?php echo $this->hotspot->description_small; ?>
					<?php echo $this->hotspot->description; ?>
				</div>

				<?php if ($this->hotspot->customfields && $this->customFields) : ?>
					<div class="hotspots-customfields">
						<h6><?php echo JText::_('COM_HOTSPOTS_CUSTOM_FIELDS'); ?></h6>
						<?php foreach ($this->hotspot->customfields as $key => $customFields): ?>
							<?php if (isset($this->customFields[$key])) : ?>
								<?php if ($customFields): ?>
									<dl class="dl-horizontal">
										<dt><?php echo JText::_($this->customFields[$key]->title); ?></dt>
										<dd><?php echo CompojoomFormCustom::render($this->customFields[$key], $customFields); ?></dd>
									</dl>
								<?php endif; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if (HotspotsHelperSettings::get('social_media', 0)) : ?>
					<div class="hs-social">
						<?php
						$share = new CompojoomLayoutFile('social.share');
						echo $share->render(
							array(
								'meta' => $meta
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="hs-creation-info text-right muted">
			<?php if ($this->settings->get('show_author')) : ?>
				<?php echo JTEXT::_('COM_HOTSPOTS_POSTED_BY'); ?>
				<?php if ($this->profile) : ?>
					<a href="<?php echo $this->profile; ?>">
				<?php endif; ?>

				<?php
				if ($this->hotspot->created_by)
				{
					$user = JFactory::getUser($this->hotspot->created_by);
					if (HotspotsHelperSettings::get('use_name', 1))
					{
						$userName = $user->name;
					}
					else
					{
						$userName = $user->username;
					}
				}
				else
				{
					$userName = $this->hotspot->created_by_alias;
				}

				echo $userName;
				?>

				<?php if ($this->profile) : ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($this->settings->get('show_date')) : ?>
				<?php echo JText::_('COM_HOTSPOTS_ON'); ?>
				<?php echo $this->hotspot->created; ?>
			<?php endif; ?>
		</div>

		<?php if ($this->hotspot->created_by && HotspotsHelperSettings::get('show_contact_author', 0)): ?>
			<?php echo $this->loadTemplate('contact_author_form'); ?>

		<?php endif; ?>

		<div class="clearfix"></div>

		<?php if (HotspotsHelperSettings::get('josc_support', '0') == 1) : ?>
			<?php
			$file = JPATH_BASE . '/administrator/components/com_comment/plugins/com_hotspots/hotspots.php';
			if (file_exists($file)) :
				?>
				<div class="hotspots-comments">
					<?php
					JLoader::discover('ccommentHelper', JPATH_ROOT . '/components/com_comment/helpers');
					echo ccommentHelperUtils::commentInit('com_hotspots', $this->hotspot, $this->hotspot->params);
					?>
				</div>
			<?php else : ?>
				<div class="alert alert-error">
					<?php echo JText::_('COM_HOTSPOTS_CCOMMENT_ENABLED_BUT_NO_CCOMMENT_INSTALLED'); ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php require_once(JPATH_COMPONENT . '/views/hotspots/tmpl/default_footer.php'); ?>

	</div>

<?php if ($authorizedContact || $showMessageToNonAuthorized) : ?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			var $ = jQuery;
            var hash = window.location.hash;

			$('.hs-btn-contact-author').click(function () {
				$('#hs-contact-author').toggleClass('hide');
				$('html, body').animate({
					scrollTop: $("#hs-contact-author").offset().top
				}, 1000);
			});

			$('.hs-btn-contact-author-cancel').click(function () {
				$('#hs-contact-author').hide();
			});

            if(hash.indexOf('hs-contact') !== -1) {
                $('.hs-btn-contact-author').click();
            }
		})
	</script>
<?php endif; ?>
<?php
// Now add all js files to the head
CompojoomHtml::script(
	CompojoomHtml::getScriptQueue('hotspots'),
	'media/com_hotspots/cache',
	HotspotsHelperSettings::get('minify', true)
);
