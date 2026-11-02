<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       31.08.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\Router\RouterBase;

class HotspotsRouter extends RouterBase {

	/**
	 * @inheritDoc
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$appl = JFactory::getApplication();
		$menu = $appl->getMenu();

		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
		}

		$mView  = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
		$mId    = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];

		if (isset($query['view']))
		{
			$view = $query['view'];

			if (empty($query['Itemid']))
			{
				$segments[] = $query['view'];
			}

			unset($query['view']);
		}

		if (($mView == 'hotspot') && (isset($query['id'])) && ($mId == intval($query['id'])))
		{
			unset($query['view']);
			unset($query['catid']);
			unset($query['id']);
		}

		if (isset($query['catid']))
		{
			// If we are routing an article or category where the category id matches the menu catid, don't include the category segment
			if ((($view == 'hotspot') and ($mView != 'category')))
			{
				$segments[] = $query['catid'];
			}

			unset($query['catid']);
		}

		if (isset($query['layout']))
		{
			if ($query['layout'] != 'userhotspots')
			{
				$segments[] = $query['layout'];
			}

			unset($query['layout']);
		}

		if (isset($query['id']))
		{
			if (empty($query['Itemid']))
			{
				$segments[] = $query['id'];
			}
			else
			{
				if (isset($menuItem->query['id']))
				{
					if ($query['id'] != $mId)
					{
						$segments[] = $query['id'];
					}
				}
				else
				{
					$segments[] = $query['id'];
				}
			}

			unset($query['id']);
		}

		return $segments;
	}

	/**
	 * @inheritDoc
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// Get the active menu item.
		$appl = JFactory::getApplication();
		$menu = $appl->getMenu();
		$item = $menu->getActive();

		// Go to single view
		if (isset($item->query['view']) && $item->query['view'] == 'hotspots')
		{
			$vars['view'] = 'hotspot';
		}

		if (isset($item->query['layout']) && $item->query['layout'] == 'userhotspots')
		{
			$vars['layout'] = 'userhotspots';
		}

		if (count($segments) == 1)
		{
			$vars['id'] = $segments[0];

			// Make sure that we don't have a layout as we are editing a Hotspot
			unset($vars['layout']);
		}

		if ($segments[0] == 'edit' || $segments[0] == 'form')
		{
			if (version_compare(JVERSION, '4.0', 'ge'))
			{
				unset($segments[0]);
				$segments = array_values($segments);
			}

			$vars['view'] = 'form';
			$vars['task'] = 'edit';
		}

		if (isset($segments[1]))
		{
			if (count($segments) > 1)
			{
				$cat           = explode(':', $segments[0]);
				$vars['catid'] = $cat[0];
				$id            = explode(':', $segments[1]);
				$vars['id']    = $id[0];
			}
		}

		$segments = array();
		return $vars;
	}
}
