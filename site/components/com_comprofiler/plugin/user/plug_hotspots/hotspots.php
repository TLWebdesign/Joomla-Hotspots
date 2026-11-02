<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       05.03.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$_PLUGINS->registerFunction('onAfterUserRegistration', 'addHotspot', 'gethotspotsTab');
//$_PLUGINS->registerFunction('onAfterUserUpdate', 'updateHotspot', 'gethotspotsTab');
//$_PLUGINS->registerFunction('onAfterUpdateUser', 'updateHotspot', 'gethotspotsTab');

require_once JPATH_ADMINISTRATOR . '/components/com_hotspots/libraries/geocoder/geocoder.php';


/**
 * Class gethotspotsTab
 *
 * @since  3.5.5
 */
class gethotspotsTab extends cbTabHandler
{
	private $hotspots = null;

    /**
     * Our constructor function.
     * Loads the language
     * and checks if Hotspots is installed and if not returns error text
     *
     * @return string
     */
    public function __constructor()
    {
        $this->cbTabHandler();
        $language = JFactory::getLanguage();
        $language->load('plg_plug_hotspots', JPATH_ADMINISTRATOR);

        // Check if hotspots is installed
        if (!file_exists(JPATH_BASE . '/components/com_hotspots/includes/defines.php'))
        {
            return JText::_('PLG_CBHOTSPOTS_HOTSPOTS_NOT_INSTALLED');
        }
    }


    /**
	 * Updates the Hotspot once the user is updated
	 *
	 * @param   object  $user  - the user object that is being edited
	 *
	 * @return bool
	 */
	public function updateHotspot($user)
	{
		// If the user is Blocked, let's unpublish/delete him
		if ($user->block || $user->approved != 1 || $user->confirmed != 1 || $user->banned)
		{
			// Let us check if the user has already a hotspot
			$markerId = $this->getMarkerId($user);

			if ($markerId)
			{
				$this->removeHotspot($markerId, $user);
			}

			return false;
		}

		// If geocode is not on, check to see that we don't have a hotspot
		// and unpublish/delete it
		if (!$this->isGeocodeOn($user))
		{
			// Let us check if the user has already a hotspot
			$markerId = $this->getMarkerId($user);

			if ($markerId)
			{
				$this->removeHotspot($markerId, $user);
			}

			return false;
		}

		$address = $this->getUserAddress($user);
		$coordinates = $this->getCoordinates($address);

		if ($coordinates)
		{
			$this->saveHotspot($coordinates, $address, $user, true);
		}

		return true;
	}

	/**
	 * Removes the user info from the hotspots tables
	 *
	 * @param   int     $markerId  - the marker id
	 * @param   object  $user      - the user object
	 *
	 * @return void
	 */
	private function removeHotspot($markerId, $user)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables');
		$markerTable = JTable::getInstance('Marker', 'Table');

		if ($this->params->get('delete', 0))
		{
			// Delete the marker
			$markerTable->delete($markerId);

			// Delete the mappings
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__hotspots_mappings')
				->where($db->qn('marker_id') . '=' . $db->q($markerId))
				->where($db->qn('foreign_id') . '=' . $db->q($user->id))
				->where($db->qn('component') . '=' . $db->q('com_comprofiler'));

			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			// We need to unpupblish the hotspot
			$this->saveHotspot(false, array(), $user, true, 0);
		}
	}

	/**
	 * Checks to see if the Geocoding is allowed
	 *
	 * @param   object  $user  - the user
	 *
	 * @return bool
	 */
	private function isGeocodeOn($user)
	{
		$params = $this->params;
		$geocode = $params->get('geocode', 0);

		if ($geocode == 1)
		{
			return true;
		}

		if ($geocode == 2)
		{
			$field = $params->get('geocode_field');

			if (property_exists($user, $field))
			{
				return $user->$field ? true : false;
			}
		}

		return false;
	}

	/**
	 * Creates a new Hotspot once the user is updated
	 *
	 * @param   object  $user  - the user object that is being edited
	 *
	 * @return bool
	 */
	public function addHotspot($user)
	{
		if (!$this->isGeocodeOn($user))
		{
			return false;
		}

		$address = $this->getUserAddress($user);
		$coordinates = $this->getCoordinates($address);

		if ($coordinates)
		{
			$this->saveHotspot($coordinates, $address, $user);
		}

		return true;
	}

	/**
	 * Get the user address out of the user object
	 *
	 * @param   object  $user  - the user object
	 *
	 * @return array
	 */
	private function getUserAddress($user)
	{
		$params = $this->params;

		$address = array();

		$fields = array(
			'street' => $params->get('address', 'cb_address'),
			'city' => $params->get('city', 'cb_city'),
			'state' => $params->get('state', 'cb_state'),
			'country' => $params->get('country', 'cb_country'),
			'zip' => $params->get('zipcode', 'cb_zipcode')
		);

		foreach ($fields as $key => $field)
		{
			if (property_exists($user, $field))
			{
				$address[$key] = $user->$field;
			}
		}

		return $address;
	}

	/**
	 * Get the coordinates for an address
	 *
	 * @param   array  $address  - street, zip, country - whatever we have
	 *
	 * @return bool
	 */
	private function getCoordinates($address)
	{
		if (count($address))
		{
			$geocoder = new HotspotsGeocoder;
			$coordinates = $geocoder->geocode(implode(',', $address));

			if ($coordinates['status'] == 'OK')
			{
				return $coordinates['location'];
			}
		}

		return false;
	}

	/**
	 * Get the markerID if we have an entry in hotspots_mappings
	 *
	 * @param   object  $user  - the user object
	 *
	 * @return int
	 */
	private function getMarkerId($user)
	{
		$db = JFactory::getDbo();
		$markerId = 0;
		$query = $db->getQuery(true);
		$query->select('marker_id')->from('#__hotspots_mappings')
			->where('foreign_id = ' . $db->q($user->id))
			->where('component = ' . $db->q('com_comprofiler'));

		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			$markerId = $row->marker_id;
		}

		return $markerId;
	}

	/**
	 * Save the hotspot in the db
	 *
	 * @param   object|false  $coordinates  - object with lat & lng properties
	 * @param   array         $address      - address array with street, zip, country
	 * @param   object        $user         - the user object
	 * @param   bool          $update       - are we updating a hotspot?
	 * @param   int           $published    - determines whether to publish or unpublish the plugin
	 *
	 * @return void
	 */
	private function saveHotspot($coordinates, $address, $user, $update = false, $published = 1)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables');
		$table = JTable::getInstance('Marker', 'Table');
		$params = $this->params;
		$description = $params->get('description', '');
		$title = $params->get('name', '');
		$location = array();
		$markerId = 0;

		if ($update)
		{
			$markerId = $this->getMarkerId($user);

			if ($markerId)
			{
				$location['id'] = $markerId;
			}
		}

		$location['street'] = isset($address['street']) ? $address['street'] : "";
		$location['plz'] = isset($address['zip']) ? $address['zip'] : "";
		$location['administrative_area_level_1'] = isset($address['administrative_area_level_1']) ? $address['administrative_area_level_1'] : "";
		$location['town'] = isset($address['city']) ? $address['city'] : "";
		$location['country'] = isset($address['country']) ? $address['country'] : "";

		if ($coordinates)
		{
			$location['gmlat'] = $coordinates->lat;
			$location['gmlng'] = $coordinates->lng;
		}

		$location['title'] = $this->replacePlaceholders($title, $user);
		$location['catid'] = $params->get('category', 1);
		$location['state'] = $published;
		$location['language'] = '*';

		$location['hotspotText'] = $this->replacePlaceholders($description, $user);
		$location['created_by'] = $user->id;

		$location['modified'] = \Joomla\CMS\Factory::getDate()->toSql();
		$location['params'] = array();
		$location['customfields'] = $this->getMappings($user);

        $table->bind($location);
		$table->check();

		if(!$markerId) {
			$table->created = \Joomla\CMS\Factory::getDate()->toSql();
		}

		if ($table->store() && !$markerId)
		{

			$mappings = JTable::getInstance('Mappings', 'HotspotsTable');

			$mappings->bind(
				array(
					'marker_id' => $table->id,
					'foreign_id' => $user->id,
					'component' => 'com_comprofiler'
				)
			);

			$mappings->store();
		}
	}

	/**
	 * Gets the mappings between the hotspots fields and theCB fields
	 *
	 * @param   object  $user  - the user object
	 *
	 * @return array
	 */
	private function getMappings($user)
	{
		$param = $this->params->get('custom_fields', '');
		$customFields = array();

		if ($param != '')
		{
			$mappings = explode("\n", $param);

			foreach ($mappings as $mapping)
			{
				$fields = explode('=', $mapping);

				if (property_exists($user, $fields[1]))
				{
					$customFields[$fields[0]] = $user->$fields[1];
				}
			}
		}

		return $customFields;
	}

	/**
	 * Replaces placeholder in the text with matches from the user object
	 *
	 * @param   string  $text  - the text to replace
	 * @param   object  $user  - the user object
	 *
	 * @return mixed
	 */
	private function replacePlaceholders($text, $user)
	{
		// Look for any variables in the Text
		preg_match_all('/{\w+}/', $text, $matches);

		if (count($matches))
		{
			foreach ($matches[0] as $value)
			{
				$property = str_replace('{', '', (str_replace('}', '', $value)));

				if (property_exists($user, $property))
				{
					$text = str_replace($value, $user->$property, $text);
				}
			}
		}

		return $text;
	}

	/**
	 * Get the user Hotspots
	 *
	 * @return mixed|null
	 */
	private function getHotspots()
	{
		if ($this->hotspots == null)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$params = $this->params;

			$hslimit = $params->get('hslimit', "15");
			$query->select(
				array('m.id AS hotspots_id', 'm.catid', 'm.title',
					'm.description_small', 'm.params',
					'm.gmlat as latitude', 'm.gmlng as longitude', 'm.created', 'c.params AS cat_params')
			)
				->from('#__hotspots_marker AS m')
				->leftJoin('#__categories AS c ON m.catid = c.id')
				->where('m.created_by = ' . $db->quote($this->user->id))
				->where('m.state = 1')
				->order('m.created DESC');

			$db->setQuery($query, 0, $hslimit);

			$this->hotspots = $db->loadObjectList();
		}

		return $this->hotspots;
	}

	/**
	 * Creates a list with the hotspots
	 *
	 * @return string
	 */
	public function getSpotsList()
	{
		$params = $this->params;

		$hsname = $params->get('hsname', "1");

		if ($hsname == 1)
		{
			$name = $this->user->name;
		}
		else
		{
			$name = $this->user->username;
		}

		$markrows = $this->getHotspots();

		$newspots = "<div id='new-spots'>";
		$newspots .= JText::_('PLG_CBHOTSPOTS_NEWEST_SPOTS_FROM') . " <strong>" . $name . ":</strong>";
		$newspots .= "<br /><br />\n";

		for ($j = 0; $j < count($markrows); $j++)
		{
			$markrow = & $markrows[$j];
			$newspots .= '<span data-id="' . $markrow->hotspots_id . '">' . $markrow->title . '</span>';
		}

		$newspots .= "</div>";

		return $newspots;
	}

	/**
	 * Displays the map with the hotspots
	 *
	 * @param   moscomprofilerTabs  $tab   - the tab
	 * @param   moscomprofilerUser  $user  - the user
	 * @param   int                 $ui    - ui
	 *
	 * @return mixed|string
	 */
	public function getDisplayTab($tab, $user, $ui)
	{
		global $my;

        $language = JFactory::getLanguage();
        $language->load('plg_plug_hotspots', JPATH_ADMINISTRATOR);

        JLoader::discover('HotspotsHelper', JPATH_BASE . '/components/com_hotspots/helpers');
		require_once JPATH_BASE . '/components/com_hotspots/includes/defines.php';

		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_hotspots/models');
		$this->user = $user;
		$html = '';

        $markers = $this->prepareHotspots($this->getHotspots());
		$document = JFactory::getDocument();
		$document->addStylesheet(JURI::root() . 'components/com_comprofiler/plugin/user/plug_hotspots/hotspots.css');

        $layout = new JLayoutFile('hotspots.map.map', JPATH_ADMINISTRATOR . '/components/com_hotspots/layouts');

        $map = $layout->render(array('hotspots' => $markers, "params" => $this->params ));

		JHTML::_('script', 'components/com_comprofiler/plugin/user/plug_hotspots/HotspotsCB.js');

		$html .= '<div id="hotspots">';
		$html .= '<div id="map-holder">';
		$html .= $map;
		$html .= "</div>";
		$html .= $this->getSpotsList();
		$html .= '<div style="clear:both"></div>';
		$html .= "</div>";

		return $html;
	}

	/**
	 * Prepare the Hotspots for output
	 *
	 * @param   array  $hotspots  - array with the hotspots
	 *
	 * @return array
	 */
	private function prepareHotspots($hotspots)
	{
		$markers = array();

		foreach ($hotspots as $hotspot)
		{
			$value = HotspotsHelperUtils::prepareHotspot($hotspot);
			$cat = new JRegistry($value->cat_params);
			$value->cat_params = $cat;
			$markers[] = $value;
		}

		foreach ($markers as $key => $marker)
		{
			if ($marker->params->get('markerimage'))
			{
				$markers[$key]->icon = HOTSPOTS_PICTURE_CATEGORIES_PATH . $marker->params->get('markerimage');
			}
			else
			{
				$markers[$key]->icon = JUri::root() . $marker->cat_params->get('icon');
			}
		}

		return $markers;
	}
}
