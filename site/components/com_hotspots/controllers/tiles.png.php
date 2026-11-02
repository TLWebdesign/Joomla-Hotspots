<?php
/**
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 * @date       23.03.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/maps/utility.php';

/**
 * Class HotspotsControllerTiles
 *
 * @since  3.5
 */
class HotspotsControllerTiles extends JControllerLegacy
{
	/**
	 * Create the tile png file
	 *
	 * @return void
	 */
	public function create()
	{
		$input = JFactory::getApplication()->input;
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$inputCats = $input->getString('cats', '', 'get');
		$search = $input->getString('search', '', 'get');
		$lang = $input->getString('hs-language', 'en-GB', 'get');
		$x = $input->getInt('x', 0, 'get');
		$y = $input->getInt('y', 0, 'get');
		$z = $input->getInt('z', 0, 'get');
		$filled = array();
		$cats = array();

		$tile = $this->getModel('Tile', 'HotspotsModel');

		$categories = JCategories::getInstance('Hotspots')->get()->getChildren(true);

		foreach ($categories as $category)
		{
			$cats[$category->id] = $category;
		}

		// Create an unique file name for this tile
		$cacheFile = $x . '_' . $y . '_'
			. $z . '_' . md5($x . '|' . $y . '|' . $z . '|' . $groups . '|' . $inputCats . '|' . $search . '|' . $lang);

		$cacheObject = new HotspotsLibraryCacheStorageImagepng(array('cachebase' => JPATH_CACHE));

		// Check if the file already exists
		if (!$cacheObject->get($cacheFile, 'com_hotspots.tiles'))
		{
			// Create a new image & make it transparent
			$im = imagecreate(HotspotsMapUtility::TILE_SIZE, HotspotsMapUtility::TILE_SIZE);
			$trans = imagecolorallocate($im, 0, 0, 255);
			imagecolortransparent($im, $trans);

			$rows = $tile->getItems();
			$cache = array();

			foreach ($rows as $row)
			{
				$point = HotspotsMapUtility::getPixelOffsetInTile($row['lat'], $row['lng'], $z);

				$catId = $row['catid'];

				// Let's create a cache that stores the GD resource with the dot for our hotspots
				if (!isset($cache[$catId]))
				{
					if (isset($cats[$catId]))
					{
						$registry = new JRegistry($cats[$catId]->params);
						$params = $registry->toArray();

						$rgb = array(0, 0, 0);

						if (isset($params['tile_marker_color']))
						{
							$rgb = explode(',', $params['tile_marker_color']);
						}

						$cache[$catId] = $this->getFilterDot($rgb);
					}
					else
					{
						$rgb = array(0, 0, 0);
						$cache[$catId] = $this->getFilterDot($rgb);
					}
				}

				// Draw our dots at the specified coordinates
				if (!isset($filled["{$point->x},{$point->y}"]))
				{
					$x = ($point->x > 251) ? 251 : $point->x;
					$y = ($point->y > 251) ? 251 : $point->y;

					imagecopy($im, $cache[$catId], $x, $y, 0, 0, 5, 5);
					$filled["{$point->x},{$point->y}"] = 1;
				}
			}


			$this->sendHeaders();
			$cacheObject->store($cacheFile, 'com_hotspots.tiles', $im);
			$cacheObject->get($cacheFile, 'com_hotspots.tiles');
		}
		else
		{
			// Output the existing image to the browser
			$this->sendHeaders();
			$cacheObject->get($cacheFile, 'com_hotspots.tiles');
		}

		jexit();
	}

	/**
	 * Creates a dot out of our png and applies the filter with the color that we want
	 * We create an image from png, because if we draw the dot with php it looks so pixelated...
	 *
	 * @param   array  $rgb  - red, green, blue value
	 *
	 * @return resource
	 */
	private function getFilterDot($rgb)
	{
		$filterDot = imagecreate(5, 5);
		$dot = imagecreatefrompng(JPATH_SITE . '/media/com_hotspots/images/utils/dot.png');
		imagecopy($filterDot, $dot, 0, 0, 0, 0, 5, 5);
		imagefilter($filterDot, IMG_FILTER_COLORIZE, $rgb[0], $rgb[1], $rgb[2]);

		return $filterDot;
	}

	/**
	 * Send the necessary headers to identify the returned resource as image
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	private function sendHeaders()
	{
        $appl = JFactory::getApplication("site");
		$appl->clearHeaders();
		$appl->allowCache(true);
		$appl->setHeader('Content-Type', 'image/png', true);
		$appl->sendHeaders();
	}
}
