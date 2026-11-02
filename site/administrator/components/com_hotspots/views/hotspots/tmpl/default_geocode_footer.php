<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

?>
<script>
    function geocodeLocation(e) {
        var ids = [];

        jQuery("#adminForm input[name='cid[]']:checked").each(function() {
           ids.push(jQuery(this).val())
        })

        // TLWebdesign patch 2026-11-02: send the form token, the controller checks it.
        var data = {cid: ids};
        data[<?php echo json_encode(\Joomla\CMS\Session\Session::getFormToken()); ?>] = 1;

        jQuery.ajax({
            url: 'index.php?option=com_hotspots&task=hotspots.geocode',
            data: data,
            dataType: "json"
        }).done(function(data) {

            jQuery("#geocodeOutput").html(data.message);

            setTimeout(function () {
                window.location.reload()
            }, '5000');
        })
    }
</script>
<button class="btn" type="button"
        onclick="document.getElementById('geocodeModal').close()"
        data-dismiss="modal">
    <?php echo JText::_('JCANCEL'); ?>
</button>
<button class="btn btn-success" type="button" onclick="geocodeLocation(); return;">
    <?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
