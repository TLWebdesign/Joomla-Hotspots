<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       17.05.15
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Class HotspotsJson
 *
 * @since  4.0
 */
class HotspotsJson extends HotspotsView
{
	/**
	 * in our json view we want to set the MIME type to JSON
	 * and the suggested filename .json
	 */
	public function __construct()
	{
		parent::__construct();

		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');

		$app = JFactory::getApplication();
		$app->setHeader('Access-Control-Allow-Origin', '*');
	}
}

