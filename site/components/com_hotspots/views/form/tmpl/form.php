<?php
/**
 * @author     Daniel Dimitrov
 * @date       : 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$doc = JFactory::getDocument();
$doc->addScript(HotspotsHelperUtils::getGmapsUrl());

if (HotspotsHelperSettings::get('emulate_bootstrap', 1))
{
//	JHTML::_('stylesheet', 'media/lib_compojoom/css/compojoom-bootstrap-3.3.6.css');
}

JHTML::stylesheet('media/lib_compojoom/third/font-awesome/css/font-awesome.min.css');

JHTML::_('stylesheet', 'media/com_hotspots/css/form.css');

JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHTML::_('script', 'media/com_hotspots/js/submit-hotspots.js');

HotspotsHelperUtils::getJsLocalization();
$options = HotspotsHelperUtils::getJSVariables(true);

?>
<script type="text/javascript">
  window.hotspotsConf = <?php echo json_encode($options, JSON_UNESCAPED_UNICODE); ?>
</script>
<script type="text/javascript">
  	Joomla.submitbutton = function (task) {
        var form = document.getElementById('adminForm');
  		if (document.formvalidator.isValid(form)) {
            Joomla.submitform(task, form);
  			// Disable the button so that we don't end up with multiple sent forms
  			jQuery('#adminForm button[type=submit]').prop('disabled', true);
  		} else {
  			document.getElementById('system-message-container').scrollIntoView({block: "end", behavior: "smooth"});
  			return false;
  		}
  	}
</script>
<div id="hotspots" class="compojoom-bootstrap hotspots addform">
    <form action="<?php echo JRoute::_('index.php?option=com_hotspots&view=form&a_id=' . (int) $this->item->id); ?>"
          method="post"
          class="form form-validate form-vertical hs-frontend-form" name="adminForm" id="adminForm"
          enctype="multipart/form-data">
        <div>
            <h2>
				<?php echo empty($this->item->id) ?
					JText::_('COM_HOTSPOTS_NEW_HOTSPOT') :
					JText::sprintf('COM_HOTSPOTS_EDIT_HOTSPOT', $this->item->id); ?>
            </h2>

			<?php if (!$this->user->id) : ?>
                <div class="control-group">
					<?php echo $this->form->getLabel('created_by_alias'); ?>

					<?php echo $this->form->getInput('created_by_alias'); ?>

                </div>
                <div class="control-group">
					<?php echo $this->form->getLabel('email'); ?>

					<?php echo $this->form->getInput('email'); ?>

                </div>
			<?php endif; ?>

            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('title'); ?>
                </div>
				<?php echo $this->form->getInput('title'); ?>
            </div>

            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('catid'); ?>
                </div>
				<?php echo $this->form->getInput('catid'); ?>
            </div>

            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('picture'); ?>
                </div>
				<?php echo $this->form->getInput('picture'); ?>
            </div>

            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('hotspotText'); ?>
                </div>
				<?php echo $this->form->getInput('hotspotText'); ?>
            </div>

            <div class="control-group hs-language">
                <div class="control-label">
					<?php echo $this->form->getLabel('language'); ?>
                </div>
				<?php echo $this->form->getInput('language'); ?>
            </div>

            <div class="clearfix"></div>
            <div id="custom-fields"></div>

            <fieldset class="adminform">

                <h2><?php echo JText::_('COM_HOTSPOTS_LOCATION_DETAILS'); ?></h2>

                <div id="hotspots-geolocation-info"></div>

                <div class="clearfix"></div>

                <div class="form-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('street'); ?>
                    </div>
                    <div class="input-group">
						<?php echo $this->form->getInput('street'); ?>
                        <span class="input-group-btn">
						            <button type="button" id="hotspots-geolocation" class="btn btn-default">
							            <span class="fa fa-street-view"></span>
							            <?php echo JText::_('COM_HOTSPOTS_FIND_MY_LOCATION'); ?>
						            </button>
								</span>
                    </div>
                </div>
				<?php if (HotspotsHelperSettings::get('user_interface', 1) == 0) : ?>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('plz'); ?>
                        </div>
						<?php echo $this->form->getInput('plz'); ?>
                    </div>

                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('town'); ?>
                        </div>
						<?php echo $this->form->getInput('town'); ?>
                    </div>
				<?php else: ?>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('town'); ?>
                        </div>
						<?php echo $this->form->getInput('town'); ?>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('plz'); ?>
                        </div>
						<?php echo $this->form->getInput('plz'); ?>
                    </div>

                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('administrative_area_level_1'); ?>
                        </div>
						<?php echo $this->form->getInput('administrative_area_level_1'); ?>
                    </div>
				<?php endif; ?>

                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('country'); ?>
                    </div>
					<?php echo $this->form->getInput('country'); ?>
                </div>
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('sticky', 'params'); ?>
                    </div>
					<?php echo $this->form->getInput('sticky', 'params'); ?>
                </div>

                <div class="control-group">
                    <div id="map-add"
                         title="<?php echo JText::_('COM_HOTSPOTS_MOVE_MARKER_DRAG'); ?>"></div>
                </div>


                <div class="control-group">
                    <div class="col-sm-6">
                        <div class="control-label">
							<?php echo $this->form->getLabel('gmlat'); ?>
                        </div>
						<?php echo $this->form->getInput('gmlat'); ?>
                    </div>
                    <div class="col-sm-6">
                        <div class="control-label">
							<?php echo $this->form->getLabel('gmlng'); ?>
                        </div>
						<?php echo $this->form->getInput('gmlng'); ?>
                    </div>
                </div>
            </fieldset>

			<?php if ($this->user->authorise('core.edit.state', 'com_hotspots')) : ?>
                <fieldset class="adminform">
                    <h2><?php echo JText::_('COM_HOTSPOTS_FIELDSET_PUBLISHING'); ?></h2>

                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('publish_up'); ?>
                        </div>
						<?php echo $this->form->getInput('publish_up'); ?>
                    </div>

                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('publish_down'); ?>
                        </div>
						<?php echo $this->form->getInput('publish_down'); ?>
                    </div>
                    <div class="clearfix"></div>

                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('state'); ?>
                        </div>
						<?php echo $this->form->getInput('state'); ?>
                    </div>

                    <div class="form-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
                        </div>
						<?php echo $this->form->getInput('access'); ?>
                </fieldset>

			<?php endif; ?>

			<?php if (!JFactory::getApplication()->input->getInt('id')) : ?>
				<?php if ($this->showCaptcha): ?>
                    <div class="control-group">
						<?php echo $this->form->getInput('captcha'); ?>
                    </div>
				<?php endif; ?>
			<?php endif; ?>

            <button type="submit" class="btn btn-primary pull-right"
                    onclick="Joomla.submitbutton('hotspot.save'); return false;">
				<?php echo JText::_('COM_HOTSPOTS_SUBMIT'); ?>
            </button>
        </div>
        <div class="clearfix"></div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" id="hotspot-id" name="hotspot-id" value="<?php echo (int) $this->item->id; ?>"/>
        <input type="hidden" name="return" value="<?php echo $this->returnPage; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
    </form>
</div>
