<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// TODO: Deprecate this file since we can use popup button to raise batch modal.

HTMLHelper::_('behavior.core');

$id    = isset($displayData['id']) ? $displayData['id'] : '';
$title = $displayData['title'];
Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
Text::script('ERROR');
$message = "{'error': [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
$alert = "Joomla.renderMessages(" . $message . ")";
?>
<script>
    function showGeocodeModal() {
        if (document.adminForm.boxchecked.value==0){
            <?php echo $alert; ?>
        }
        else {
            <?php if(version_compare(JVERSION, '4', 'lt')) : ?>
                jQuery( '#geocodeModal' ).modal('show')
            <?php else : ?>
                document.getElementById('geocodeModal').open();
            <?php endif; ?>

            return true;
        }
    }
</script>
<button<?php echo $id; ?> type="button" data-bs-toggle="modal" onclick="showGeocodeModal()" class="btn">
    <span class="icon-square" aria-hidden="true"></span>
    <?php echo $title; ?>
</button>
