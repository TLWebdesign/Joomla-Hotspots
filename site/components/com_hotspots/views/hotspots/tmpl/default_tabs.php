<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       20.10.2014
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

?>

<script type="text/template" id="tabs-template">
	<section id="js-hs-tabs" class="hs-tabs <?php echo HotspotsHelperSettings::get('show_tabs_title', 1) ? '' : 'hide' ?>"></section>
	<section id="js-hs-tabs-content" class="hs-tabs-content"></section>
</script>


<?php
// Well, it turns out that jquery ui tabs can load resources per ajax
// we've learned that the hard way...
// That is why our href needs to have the complete url and not only the hash
?>
<script type="text/html" id="tab-template">
	<a href="<?php echo Juri::getInstance()->toString(); ?>#tabs-{{id}}">{{{tabName}}}</a>
</script>

<script type="text/html" id="tab-content-template">
	<div id="hs-tab-content-region-{{ id }}" class="hs-tab-content-region">{{ content }}</div>
</script>