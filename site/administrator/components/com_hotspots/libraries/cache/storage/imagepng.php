<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       21.01.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Imagepng cache storage handler
 *
 * @since  5.0
 */
class HotspotsLibraryCacheStorageImagepng extends JCacheStorageFile
{
	/**
	 * Get cached data from a file by id and group
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data string
	 *
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = false)
	{
		$data = false;

		$path = $this->_getFilePath($id, $group);

		if ($checkTime == false || ($checkTime == true && $this->_checkExpire($id, $group) === true))
		{
			if (file_exists($path))
			{
				$data = @readfile($path);
			}

			return $data;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Store the data to a file by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function store($id, $group, $data)
	{
		$path = $this->_getFilePath($id, $group);

		return imagepng($data, $path);
	}

	/**
	 * Get a cache file path from an id/group pair
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  string   The cache file path
	 *
	 * @since   11.1
	 */
	protected function _getFilePath($id, $group)
	{
		$name = $this->_getCacheId($id, $group);

		$dir = $this->_root . '/' . $group;

		// If the folder doesn't exist try to create it
		if (!is_dir($dir))
		{
			// Make sure the index file is there
			$indexFile = $dir . '/index.html';
			@mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
		}

		// Make sure the folder exists
		if (!is_dir($dir))
		{
			return false;
		}

		return $dir . '/' . $name . '.png';
	}
}
