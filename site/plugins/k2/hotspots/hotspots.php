<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       11.03.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_hotspots/libraries/geocoder/geocoder.php';

JLoader::discover('HotspotsHelper', JPATH_SITE . '/components/com_hotspots/helpers/');
JLoader::discover('HotspotsModel', JPATH_SITE . '/components/com_hotspots/models/');

// Load the compojoom framework
require_once JPATH_LIBRARIES . '/compojoom/include.php';

// Load the category for j2.5
jimport('joomla.application.categories');

/**
 * Class PlgK2Hotspots
 *
 * @since  3.0
 */
class PlgK2Hotspots extends JPlugin
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
	 * Once the event is saved we need to map it with hotspots
	 *
	 * @param   object  &$row   - the matukio object
	 * @param   bool    $isnew  - is it a new event or an updated event
	 *
	 * @return bool
	 */
	public function onAfterK2Save(&$row, $isnew)
	{
		$input = JFactory::getApplication()->input;
		$hotspot = $input->getArray(
				array(
					'hotspot' => array(
					'street' => 'string',
					'town' => 'string',
					'plz' => 'string',
					'country' => 'string',
					'gmlat' => 'float',
					'gmlng' => 'float',
					'delete' => 'int'
				)
			)
		);

		if (isset($hotspot['hotspot']))
		{
			$hotspot = $hotspot['hotspot'];

			// Handly the sticky flag
			$hotspot['sticky'] = 0;
			$hotspotForParams = $input->get('hotspot', '', 'array');

			if (isset($hotspotForParams['params']['sticky']))
			{
				$hotspot['sticky'] = 1;
			}

			if ($hotspot['delete'])
			{
				$this->deleteHotspot($hotspot, $row);

				return true;
			}

			if ($hotspot['gmlat'] && $hotspot['gmlng'])
			{
				$this->saveHotspot($hotspot, $row);

				return true;
			}
		}

		return true;
	}

	/**
	 * Delete the hotspot and mappigns into the db
	 *
	 * @param   object  $row  - the k2 object
	 *
	 * @return void
	 */
	public function deleteHotspot($row)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables');
		$table = JTable::getInstance('Marker', 'Table');

		$markerId = HotspotsHelperMappings::getMarkerId($row->id, 'com_k2');

		if ($markerId)
		{
			$table->delete($markerId);
			HotspotsHelperMappings::deleteMappings(array($row->id), 'com_k2');
		}

	}

	/**
	 * Saves the hotspot and mappigns into the db
	 *
	 * @param   array   $addressInfo  - array with address information
	 * @param   object  $row          - the k2 object
	 * @param   int     $published    - should we publish or unpublish the event
	 *
	 * @return void
	 */
	public function saveHotspot($addressInfo, $row, $published = 1)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables');
		$model = JModelLegacy::getInstance('Hotspot', 'HotspotsModel');
		$params = $this->params;

		$description = $params->get('description', '');
		$title = $params->get('name', '');
		$categoryMappings = $this->getCategoryMappings();

		$location = array();

		$markerId = HotspotsHelperMappings::getMarkerId($row->id, 'com_k2');

		if ($markerId)
		{
			$location['id'] = $markerId;
		}

		$location = array_merge($location, $addressInfo);

		$location['title'] = HotspotsHelperContent::replacePlaceholders($title, $row);
		$location['catid'] = isset($categoryMappings[$row->catid]) ? $categoryMappings[$row->catid] : $params->get('catid', 1);
		$location['state'] = $row->published;
		$location['language'] = $row->language;
		$location['created'] = $row->created;
		$location['publish_up'] = $row->publish_up;
		$location['publish_down'] = $row->publish_down;

		$location['hotspotText'] = HotspotsHelperContent::replacePlaceholders($description, $row);
		$location['created_by'] = $row->created_by;
		$location['created_by_alias'] = $row->created_by_alias;
		$location['modified'] = $row->modified;
		$location['modified_by'] = $row->modified_by;
		$location['access'] = $row->access;

		if (!$markerId)
		{
			$location['customfields'] = array();
		}

		$location['params'] = array();
		$location['params']['sticky'] = $addressInfo['sticky'];

		if ($params->get('link_to', 0))
		{
			$location['params']['link_to_id'] = $row->id;
			$location['params']['link_to'] = 'k2';
		}

		if ($model->save($location) && !$markerId)
		{
			HotspotsHelperMappings::saveMapping($model->getState('hotspot.id'), $row->id, 'com_k2');
		}
	}

	/**
	 * Get the category mappings specified in the plugin settings
	 *
	 * @return array
	 */
	private function getCategoryMappings()
	{
		$raw = $this->params->get('catmappings');
		$return = array();
		$lines = explode("\n", $raw);

		foreach ($lines as $line)
		{
			if ($line)
			{
				$cats = explode('=', $line);
				$return[(int) $cats[0]] = (int) $cats[1];
			}
		}

		return $return;
	}

	/**
	 * Gets the Hotspot data
	 *
	 * @param   int  $id  - the id of the hotspot
	 *
	 * @return mixed
	 */
	private function getHotspot($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.id as hotspots_id, m.*')
			->from('#__hotspots_marker  AS m')
			->where('m.id = ' . $db->quote($id));

		$db->setQuery($query);
		$hotspot = $db->loadObject();

		if ($hotspot)
		{
			$category = JCategories::getInstance('Hotspots')->get($hotspot->catid);
			$params = new JRegistry($category->params);
			$hotspot->cat_icon = $params->get('icon');
		}

		return $hotspot;
	}

	/**
	 * Ads our plugin fields on the k2 item page
	 *
	 * @param        $item
	 * @param        $type
	 * @param string $tab
	 *
	 * @return JObject
	 */
	public function onRenderAdminForm(&$item, $type, $tab = '')
	{
		static $init = false;

		if (!$init && $type == 'item')
		{
			$street = '';
			$city = '';
			$zip = '';
			$country = '';
			$lat = '';
			$lng = '';
			$hotspot  = new stdClass;
			$center = new stdClass;
			$center->lat = $this->params->get('lat');
			$center->lng = $this->params->get('lng');
			$center->zoom = (int) $this->params->get('zoom');

			$markerId = HotspotsHelperMappings::getMarkerId($item->id, 'com_k2');

			if ($markerId)
			{
				$rawItem = $this->getHotspot($markerId);

				if ($rawItem)
				{
					$hotspot = HotspotsHelperUtils::prepareHotspot($rawItem);

					if ($hotspot)
					{
						$street = $hotspot->street;
						$city = $hotspot->town;
						$zip = $hotspot->plz;
						$country = $hotspot->country;
						$lat = $hotspot->gmlat;
						$lng = $hotspot->gmlng;
					}
				}
			}

			$layout = new CompojoomLayoutFile('k2hotspots.form', JPATH_ROOT . '/plugins/k2/hotspots/layouts');

			$html = $layout->render(
				array(
					'street' => $street,
					'city' => $city,
					'zip' => $zip,
					'country' => $country,
					'lat' => $lat,
					'lng' => $lng,
					'center' => $center,
					'hotspot' => $hotspot
				)
			);

			$plugin = new JObject;
			$plugin->set('name', 'Hotspot map integration');
			$plugin->set('fields', $html);

			$init = true;

			return $plugin;
		}
	}

	/**
	 * Outputs the hotspots map
	 *
	 * @param   object  &$item  - the k2 item object
	 *
	 * @return string
	 */
	public function onK2AfterDisplayContent(&$item)
	{
		$input = JFactory::getApplication()->input;
		$markerId = HotspotsHelperMappings::getMarkerId($item->id, 'com_k2');

		if (!$markerId || ($input->get('view') != 'item') || !$this->params->get('show_map'))
		{
			return false;
		}

		$addressArray = array();
		$hotspot = $this->getHotspot($markerId);
		$frontend = JPATH_BASE . '/components/com_hotspots';
		require_once $frontend . '/includes/defines.php';
		require_once $frontend . '/helpers/route.php';
		JLoader::discover('HotspotsModel', $frontend . '/models');
		JLoader::discover('HotspotsHelper', JPATH_BASE . '/components/com_hotspots/helpers/');


		$this->hotspot = HotspotsHelperUtils::prepareHotspot($hotspot);

		$icon = $this->hotspot->cat_icon;

		if ($this->hotspot->params->get('markerimage'))
		{
			$icon = $this->hotspot->params->get('markerimage');
		}

		$hotspot = array(
			'id' => $this->hotspot->id,
			'latitude' => $this->hotspot->gmlat,
			'longitude' => $this->hotspot->gmlng,
			'title' => $this->hotspot->title,
			'icon' => JURI::root() . $icon,
		);

		if ($this->hotspot->street)
		{
			$addressArray[] = $this->hotspot->street;
		}

		if ($this->hotspot->town)
		{
			$addressArray[] = $this->hotspot->town;
		}

		if ($this->hotspot->plz)
		{
			$addressArray[] = $this->hotspot->plz;
		}

		if ($this->hotspot->country)
		{
			$addressArray[] = $this->hotspot->country;
		}

		ob_start();
		require $frontend . '/views/hotspot/tmpl/default_address.php';
		$address = ob_get_contents();
		ob_end_clean();
		$hotspot['description'] = '<h2>' . $this->hotspot->title . '</h2>'
			. '<div>' . $this->hotspot->description_small . '</div>'
			. '<div>' . preg_replace("@[\\r|\\n|\\t]+@", "", $address) . '</div>';

		$layout = new CompojoomLayoutFile('hotspots.single-map', JPATH_ROOT . '/components/com_hotspots/layouts');

		return $layout->render(
			array(
				'hotspot' => $hotspot,
				'addressArray' => $addressArray,
				'params' => $this->params,
				'frontend' => $frontend
			)
		);
	}
}
