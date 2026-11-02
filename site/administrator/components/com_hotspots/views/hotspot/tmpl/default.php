<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       31.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$doc = JFactory::getDocument();
$doc->addScript(HotspotsHelperUtils::getGmapsUrl());

JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHTML::_('script', 'media/com_hotspots/js/submit-hotspots.js');

JHTML::_('stylesheet', 'media/com_hotspots/css/hotspots-backend.css');

HotspotsHelperUtils::getJsLocalization();
$options = HotspotsHelperUtils::getJSVariables(true);

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'cpanel', '', '');

if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
?>
    <script type="text/javascript">
        window.hotspotsConf = <?php echo json_encode($options, JSON_UNESCAPED_UNICODE); ?>
    </script>

    <form role="form"
          action="<?php echo JRoute::_('index.php?option=com_hotspots&view=hotspot&id=' . (int)$this->hotspot->id); ?>"
          method="post" class="form-horizontal form-validate"
          name="adminForm" id="adminForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-sm-8">
                <div class="box-info">
                    <h2><?php echo empty($this->hotspot->id) ? JText::_('COM_HOTSPOTS_NEW_HOTSPOT') : JText::sprintf('COM_HOTSPOTS_EDIT_HOTSPOT', $this->hotspot->id); ?></h2>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('title'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('title'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('alias'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('alias'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('catid'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('catid'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('state'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('state'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 compojoom-control-label">
                            <?php echo JText::_('JFIELD_ACCESS_LABEL'); ?>
                        </label>

                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('access'); ?>
                        </div>
                    </div>

                    <?php if ($this->canDo->get('core.admin')): ?>
                        <div class="form-group">
                            <label class="col-sm-2 compojoom-control-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></label>

                            <div class="col-sm-10">
                                <div class="blank">
                                    <button type="button" class="btn btn-default"
                                            onclick="document.location.href='#access-rules';">
                                        <?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('language'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('language'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('id'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('id'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('picture'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('picture'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $this->form->getLabel('hotspotText'); ?>
                        <div class="col-sm-10">
                            <?php echo $this->form->getInput('hotspotText'); ?>
                        </div>
                    </div>
                    <div class="clr"></div>

                    <div id="custom-fields" class="text-right"></div>

                    <h2><?php echo JText::_('COM_HOTSPOTS_LOCATION_DETAILS'); ?></h2>

                    <div id="hotspots-geolocation-info"></div>
                    <div id="hotspots-geolocation">
                        <img src="<?php echo JURI::root() ?>/media/com_hotspots/images/utils/person.png"
                             alt="find my location"
                             title="<?php echo JText::_('COM_HOTSPOTS_FIND_MY_LOCATION'); ?>"/>
                    </div>
                    <div>

                        <div class="form-group">
                            <?php echo $this->form->getLabel('street'); ?>
                            <div class="col-sm-9">
                                <?php echo $this->form->getInput('street'); ?>
                            </div>
                        </div>

                        <?php if (HotspotsHelperSettings::get('user_interface', 1) == 0) : ?>

                            <div class="form-group">
                                <?php echo $this->form->getLabel('plz'); ?>
                                <div class="col-sm-10">
                                    <?php echo $this->form->getInput('plz'); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php echo $this->form->getLabel('town'); ?>
                                <div class="col-sm-10">
                                    <?php echo $this->form->getInput('town'); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <?php echo $this->form->getLabel('town'); ?>
                                <div class="col-sm-10">
                                    <?php echo $this->form->getInput('town'); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $this->form->getLabel('plz'); ?>
                                <div class="col-sm-10">
                                    <?php echo $this->form->getInput('plz'); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <?php echo $this->form->getLabel('administrative_area_level_1'); ?>
                            <div class="col-sm-10">
                                <?php echo $this->form->getInput('administrative_area_level_1'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php echo $this->form->getLabel('country'); ?>
                            <div class="col-sm-10">
                                <?php echo $this->form->getInput('country'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php echo $this->form->getLabel('sticky', 'params'); ?>
                            <div class="col-sm-10">
                                <?php echo $this->form->getInput('sticky', 'params'); ?>
                            </div>
                        </div>

                        <div id="map-add"
                             title="<?php echo JText::_('COM_HOTSPOTS_MOVE_MARKER_DRAG'); ?>"></div>

                        <div class="row offset2">
                            <div class="col-sm-6">
                                <?php echo $this->form->getLabel('gmlat'); ?>
                                <?php echo $this->form->getInput('gmlat'); ?>
                            </div>
                            <div class="col-sm-6">
                                <?php echo $this->form->getLabel('gmlng'); ?>
                                <?php echo $this->form->getInput('gmlng'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="box-info full">
                    <?php if (version_compare(JVERSION, '4.0', 'lt')): ?>
                        <ul class="nav nav-tabs nav-justified">
                            <li class="active">
                                <a data-toggle="tab" href="#publishing">
                                    <?php echo JText::_('COM_HOTSPOTS_FIELDSET_PUBLISHING'); ?>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#hs-custom">
                                    <?php echo JText::_('COM_HOTSPOTS_MARKER_FIELDSET_OPTIONS'); ?>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="publishing" class="tab-pane active">
                                <div class="form-group">
                                    <?php echo $this->form->getLabel('created_by'); ?>
                                    <div class="col-sm-8">
                                        <?php echo $this->form->getInput('created_by'); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <?php echo $this->form->getLabel('created_by_alias'); ?>
                                    <div class="col-sm-8">
                                        <?php echo $this->form->getInput('created_by_alias'); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <?php echo $this->form->getLabel('created'); ?>
                                    <div class="col-sm-8">
                                        <?php echo $this->form->getInput('created'); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <?php echo $this->form->getLabel('publish_up'); ?>
                                    <div class="col-sm-8">
                                        <?php echo $this->form->getInput('publish_up'); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <?php echo $this->form->getLabel('publish_down'); ?>
                                    <div class="col-sm-8">
                                        <?php echo $this->form->getInput('publish_down'); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <?php echo $this->form->getLabel('modified_by'); ?>
                                    <div class="col-sm-8">
                                        <?php echo $this->form->getInput('modified_by'); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <?php echo $this->form->getLabel('modified'); ?>
                                    <div class="col-sm-8">
                                        <?php echo $this->form->getInput('modified'); ?>
                                    </div>
                                </div>
                            </div>
                            <div id="hs-custom" class="tab-pane">
                                <?php echo $this->loadTemplate('params'); ?>
                            </div>
                        </div>

                    <?php else: ?>

                    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'publishing')); ?>

                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_HOTSPOTS_FIELDSET_PUBLISHING')); ?>
                    <div id="publishing" class="tab-pane active">
                        <div class="form-group">
                            <?php echo $this->form->getLabel('created_by'); ?>
                            <div class="col-sm-12">
                                <?php echo $this->form->getInput('created_by'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $this->form->getLabel('created_by_alias'); ?>
                            <div class="col-sm-12">
                                <?php echo $this->form->getInput('created_by_alias'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $this->form->getLabel('created'); ?>
                            <div class="col-sm-12">
                                <?php echo $this->form->getInput('created'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $this->form->getLabel('publish_up'); ?>
                            <div class="col-sm-12">
                                <?php echo $this->form->getInput('publish_up'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $this->form->getLabel('publish_down'); ?>
                            <div class="col-sm-12">
                                <?php echo $this->form->getInput('publish_down'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $this->form->getLabel('modified_by'); ?>
                            <div class="col-sm-12">
                                <?php echo $this->form->getInput('modified_by'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $this->form->getLabel('modified'); ?>
                            <div class="col-sm-12">
                                <?php echo $this->form->getInput('modified'); ?>
                            </div>
                        </div>
                    </div>

                    <?php echo HTMLHelper::_('uitab.endTab'); ?>

                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'marker', Text::_('COM_HOTSPOTS_FIELDSET_PUBLISHING')); ?>
                    <div id="hs-custom" class="tab-pane">
                        <?php echo $this->loadTemplate('params'); ?>
                    </div>

                    <?php echo HTMLHelper::_('uitab.endTab'); ?>

                    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

                </div>

                <?php endif; ?>
            </div>
        </div>
        </div>

        <?php if ($this->canDo->get('core.admin')): ?>
            <div class="row">
                <div class="col-sm-12">
                    <div id="access-rules" class="box-info">
                        <h2><?php echo JText::_('COM_HOTSPOTS_FIELDSET_RULES'); ?></h2>
                        <?php echo $this->form->getInput('rules'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" id="hotspot-id" name="hotspot-id" value="<?php echo (int)$this->hotspot->id; ?>"/>
        <?php echo JHTML::_('form.token'); ?>
    </form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
