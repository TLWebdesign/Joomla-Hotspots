<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       25.08.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class HotspotsHelperTour
 *
 * @since  4.0
 */
class HotspotsHelperTour
{
	/**
	 * Creates the tour object
	 *
	 * @return stdClass
	 */
	public static function tour()
	{
		$tour = new stdClass;
		$tour->id = 'hotspots-tour';
		$tour->onEnd = 'cookie';
		$tour->onClose = 'cookie';

		$steps[] = array(
			'title' => JText::_('COM_HOTSPOTS_TOUR_WELCOME_LABEL'),
			'content' => HotspotsHelperSettings::get('welcome_text'),
			'target' => "js-hs-main-app",
			'placement' => "top",
			'xOffset' => 'center',
			'zindex' => 9999,
			'arrowOffset' => 'center',
			'onShow' => 'startEnd'
		);

		if (HotspotsHelperSettings::get('show_tabs_hotspots', 1))
		{
			if (HotspotsHelperSettings::get('show_tabs_title', 1))
			{
				$steps[] = array(
					'title' => JText::_('COM_HOTSPOTS_TOUR_HOTSPOTS_TAB_LABEL'),
					'content' => JText::_('COM_HOTSPOTS_TOUR_HOTSPOTS_TAB_DESC'),
					'target' => "#js-tabs-region li[data-name='hotspots']",
					'placement' => "bottom",
					'zindex' => 9999,
					'xOffset' => 'center',
					'arrowOffset' => 'center',
					'onShow' => 'hotspotsTab',
				);
			}

			$steps[] = array(
				'title' => JText::_('COM_HOTSPOTS_TOUR_FILTER_LABEL'),
				'content' => JText::_('COM_HOTSPOTS_TOUR_FILTER_DESC'),
				'target' => "#js-hs-tab-filter-region",
				'placement' => "bottom",
				'xOffset' => 'center',
				'arrowOffset' => 'center',
				'zindex' => 9999,
				'showPrevButton' => true,
				'onShow' => 'hotspotsTab'
			);
		}

		if (HotspotsHelperSettings::get('find_position', 1) || HotspotsHelperSettings::get('resize_map', 1))
		{
			$title = HotspotsHelperSettings::get('find_position', 1) ? JText::_('COM_HOTSPOTS_CENTER') : '';
			$description = HotspotsHelperSettings::get('find_position', 1) ? JText::_('COM_HOTSPOTS_TOUR_CENTER_BUTTON_DESC') : '';

			if (HotspotsHelperSettings::get('resize_map', 1))
			{
				if (strlen($title))
				{
					$title .= ' & ';
				}

				$title .= JText::_('COM_HOTSPOTS_FULLSCREEN');
				$description .= JText::_('COM_HOTSPOTS_TOUR_FULLSCREEN_BUTTON_DESC');
			}

			$steps[] = array(
				'title' => $title,
				'content' => $description,
				'target' => ".hs-buttons-map",
				'placement' => "left",
				'yOffset' => -20,
				'zindex' => 9999,
				'showPrevButton' => true
			);
		}

		if (HotspotsHelperSettings::get('show_tabs_directions'))
		{
			$steps[] = array(
				'title' => JText::_('COM_HOTSPOTS_TOUR_ADDRESS_LABEL'),
				'content' => JText::_('COM_HOTSPOTS_TOUR_ADDRESS_DESC'),
				'target' => "#js-hs-search-address-form",
				'placement' => "bottom",
				'xOffset' => 'center',
				'arrowOffset' => 'center',
				'zindex' => 9999,
				'showPrevButton' => true,
				'onShow' => 'directionsTab'
			);
			$steps[] = array(
				'title' => JText::_('COM_HOTSPOTS_TOUR_DIRECTION_LABEL'),
				'content' => JText::_('COM_HOTSPOTS_TOUR_DIRECTION_DESC'),
				'target' => "#js-hs-search-address-form .js-hs-show-directions",
				'placement' => "bottom",
				'xOffset' => -250,
				'arrowOffset' => 250,
				'zindex' => 9999,
				'showPrevButton' => true,
				'nextOnTargetClick' => true,
				'onShow' => 'directionsTab'
			);
		}

		$steps[] = array(
			'title' => JText::_('COM_HOTSPOTS_TOUR_END_LABEL'),
			'content' => JText::_('COM_HOTSPOTS_TOUR_END_DESC'),
			'target' => "#js-hs-main-app",
			'placement' => "top",
			'zindex' => 9999,
			'showPrevButton' => true,
			'xOffset' => 'center',
			'arrowOffset' => 'center',
			'onShow' => 'startEnd'
		);

		foreach ($steps as $step)
		{
			$tour->steps[] = (object) $step;
		}

		$tour->i18n = array(
			'nextBtn' => JText::_('COM_HOTSPOTS_NEXT_BTN'),
			'prevBtn' => JText::_('COM_HOTSPOTS_PREV_BTN'),
			'doneBtn' => JText::_('COM_HOTSPOTS_DONE_BTN')
		);

		return $tour;
	}

	/**
	 * Creates the tour object and saves it to a file on the server
	 *
	 * @return string - the path to the file
	 */
	public static function addTour()
	{
		$tour = "var HotspotsTour = " . json_encode(self::tour()) . ';';

		$file = 'media/com_hotspots/cache/tour/' . md5($tour) . '.js';

		if (!file_exists(JPATH_ROOT . '/' . $file))
		{
			JFile::write(JPATH_ROOT . '/' . $file, $tour);
		}

		return $file;
	}
}
