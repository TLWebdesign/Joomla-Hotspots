<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       04.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

use \Joomla\String\StringHelper;

JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers');
/**
 * Class plgContentHotspots
 *
 * @since  3.0
 */
class PlgContentHotspotscategory extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  - the subject
	 * @param   array   $params    - the params
	 */
	public function __construct(&$subject, $params)
	{
		$jlang = JFactory::getLanguage();
		$jlang->load('com_hotspots', JPATH_SITE, 'en-GB', true);
		$jlang->load('com_hotspots', JPATH_SITE, $jlang->getDefault(), true);
		$jlang->load('com_hotspots', JPATH_SITE, null, true);

		parent::__construct($subject, $params);
	}

	/**
	 * On content prepare event
	 *
	 * @param   string  $context  - the context
	 * @param   object  $table    - the category object
	 * @param   object  $isNew    - new category?
	 *
	 * @return bool
	 */
	public function onContentBeforeSave($context, $table, $isNew)
	{
		if ($context !== 'com_categories.category' || $table->extension !== 'com_hotspots')
		{
			return true;
		}

		$params = new JRegistry($table->params);

		if (!$params)
		{
			return false;
		}

		$icon = $params->get('icon');

		if (strstr($icon, '[upload]'))
		{
			$params->set('icon', str_replace('[upload]', '', $icon));
		}

		if (strstr($icon, '[sample]'))
		{
			$newImage = $this->copySampleImage(str_replace('[sample]', '', $icon));
			$params->set('icon', $newImage);
		}

		$params->set('tile_marker_color', hotspotsHelperColor::hex2rgb($params->get('tile_marker_color')));

		$table->params = $params->toString();

		return true;
	}

	/**
	 * Copies the sample image to the appropriate location
	 *
	 * @param   string  $image  - path to the image
	 *
	 * @return bool|string
	 */
	private function copySampleImage($image)
	{
		$appl = JFactory::getApplication();
		$user = JFactory::getUser();
		$sampleImagePath = JPATH_ROOT . '/media/com_hotspots/images/categories/' . $image;
		$newImageName = time() . '_' . StringHelper::substr($user->name, 0, 1) . '_' . preg_replace('#(sample|_shadow|\/)#', '', $image);

		$moveTo = JPATH_ROOT . '/media/com_hotspots/images/categories/' . $newImageName;

		if (JFile::copy($sampleImagePath, $moveTo))
		{
			// On some servers the image has wrong permissions, that is why we set them here
			chmod($moveTo, 0644);

			return 'media/com_hotspots/images/categories/' . $newImageName;
		}
		else
		{
			$msg = JText::sprintf('COM_HOTSPOTS_SAMPLE_IMAGED_COPY_FAILED', $image);
		}

		$appl->enqueueMessage($msg);

		return false;
	}
}
