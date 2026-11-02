<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       23.09.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.file');

/**
 * Class HotspotsModelKml
 *
 * @since  3.0
 */
class HotspotsModelMultimedia extends CompojoomModelMultimedia
{
	protected $context = 'com_hotspots.edit.hotspot.data';

	protected $fieldName = 'picture';

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 */
	public function __construct($config = array())
	{
		$config['type_alias'] = 'com_hotspots.hotspot';

		parent::__construct($config);
	}
}
