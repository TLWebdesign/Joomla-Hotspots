<?php
/**
 * @author     Daniel Dimitrov
 * @date       : 19.04.2013
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */


defined('_JEXEC') or die('Restricted access');

ob_start();
require 'description_raw.php';
$description = ob_get_contents();
ob_end_clean();

$json = array(
	'id' => $this->hotspot->id,
	'description' => preg_replace("@[\\r|\\n|\\t]+@", '', $description),
	'readmore' => $this->hotspot->link,
	'latitude' => $this->hotspot->gmlat,
	'longitude' => $this->hotspot->gmlng
);

echo json_encode($json);
jexit();