<?php

/**
 * @author     Daniel Dimitrov
 * @date: 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
?>
<strong><?php echo $this->hotspot->title ?></strong><br/><br/>

<?php $this->loadTemplate('address'); // require_once(JPATH_COMPONENT . '/views/json/tmpl/address.php'); ?>
