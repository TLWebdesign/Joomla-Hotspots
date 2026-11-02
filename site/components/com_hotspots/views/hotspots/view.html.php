<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       22.08.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsViewHotspots
 *
 * @since  4.0
 */
class HotspotsViewHotspots extends HotspotsView
{
	/**
	 * Display the map or user hotspots
	 *
	 * @param   string  $tpl  - the template name
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		$menu = JFactory::getApplication()->getMenu()->getActive();

		if (isset($menu->params))
		{
			$params = $menu->params;
			$document = JFactory::getDocument();

			if ($params->get('menu-meta_description'))
			{
				$document->setDescription($params->get('menu-meta_description'));
			}

			if ($params->get('menu-meta_keywords'))
			{
				$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
			}

			if ($params->get('robots'))
			{
				$document->setMetadata('robots', $params->get('robots'));
			}
		}

	}
}
