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

/**
 * Error class for html responses
 *
 * @since  1.0
 */
class HotspotsViewApi extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @throws  Exception if used
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		throw new Exception("Raw only", 500);
	}
}
