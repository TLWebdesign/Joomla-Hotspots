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
        var checkboxes = jQuery("#adminForm input[name='cid[]']");
        var ids = [];
        checkboxes.each(function (checkbox) {
            if (checkbox.checked) {
                ids.push(checkbox.get('value'));
            }
        });

        jQuery("#adminForm input[name='cid[]']:checked").each(function() {
           ids.push(jQuery(this).val())
        })

        jQuery.ajax({
            url: 'index.php?option=com_hotspots&task=hotspots.geocode',
            data:{cid:ids },
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
