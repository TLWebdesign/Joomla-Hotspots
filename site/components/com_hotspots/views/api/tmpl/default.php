<?php
/**
 * @package    MatukioEvents
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-09-28
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe, Daniel Dimitrov. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/api/api.php';

$input = JFactory::getApplication()->input;

// Request - only a name, no special chars
$request = $input->getCmd('request');

if (empty($request))
{
	throw new Exception("Please specify a request", 404);
}

$path = JPATH_COMPONENT . '/api/' . $request . '.php';

if (!file_exists($path))
{
	throw new Exception("Request " . $request . " not found", 404);
}

require_once $path;

$classname = "HotspotsApi" . $request;

/** @var MatukioApi $api */
$api = new $classname;
$api->execute($input->getCmd('task', ''));
