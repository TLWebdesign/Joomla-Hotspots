<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       23.03.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

$json = array();

foreach ((array) $this->kmls as $key => $kml)
{
	$json[] = array(
		'cat' => $kml->catid,
		'file' => JURI::root() . 'media/com_hotspots/kmls/' . $kml->mangled_filename
	);
}

echo json_encode($json);

// Let us exit here and not let plugins screw our response
jexit();