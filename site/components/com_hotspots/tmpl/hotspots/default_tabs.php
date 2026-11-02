<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2014 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Hotspots\HtmlView $this */
?>

<script type="text/template" id="tabs-template">
	<section id="js-hs-tabs" class="hs-tabs <?php echo $this->settings->get('show_tabs_title', 1) ? '' : 'hide' ?>"></section>
	<section id="js-hs-tabs-content" class="hs-tabs-content"></section>
</script>


<?php
// Well, it turns out that jquery ui tabs can load resources per ajax
// we've learned that the hard way...
// That is why our href needs to have the complete url and not only the hash
?>
<script type="text/html" id="tab-template">
	<a href="<?php echo $this->escape(Uri::getInstance()->toString()); ?>#tabs-{{id}}">{{{tabName}}}</a>
</script>

<script type="text/html" id="tab-content-template">
	<div id="hs-tab-content-region-{{ id }}" class="hs-tab-content-region">{{ content }}</div>
</script>