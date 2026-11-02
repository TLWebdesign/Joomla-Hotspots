<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
/**
 * Class HotspotsControllerImport
 *
 * @since  3.0
 */
class HotspotsControllerImport extends HotspotsController
{
	/**
	 * @var array
	 */
	private static $locations = array();

	/**
	 * Handle KML files
	 * echoes a json string
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function kml()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
		$response = array( 'files' => array());
		$requestType = $_SERVER['REQUEST_METHOD'];

		// Handle GET request -> we need to show the files already there
		if ($requestType == 'GET')
		{
			$files = JFolder::files(JPATH_SITE . '/media/com_hotspots/import/kmls/');

			foreach ($files as $file)
			{
				$response['files'][] = array(
					'name' => $file,
					'size' => filesize(JPATH_SITE . '/media/com_hotspots/import/kmls/' . $file),
					'url' => Juri::root() . '/media/com_hotspots/import/kmls/' . $file,
					'deleteUrl' => 'index.php?option=com_hotspots&task=import.kml&action=delete&file=' . $file . '&' . JSession::getFormToken() . '=1',
					'deleteType' => 'DELETE'
				);
			}

			echo json_encode($response);
			JFactory::getApplication()->close();
		}

		// Handle deletes of the files
		if ($requestType == 'DELETE')
		{
			// TLWebdesign patch 2026-11-02: plain file name only, no "../".
			$file = basename(JFactory::getApplication()->input->getString('file'));
			$response = array(
				$file => $file !== '' && $file[0] !== '.' && JFile::delete(JPATH_SITE . '/media/com_hotspots/import/kmls/' . $file)
			);
			echo json_encode($response);
			JFactory::getApplication()->close();
		}

		// If we have a post, then we are dealing with creating files
		if ($requestType == 'POST')
		{
			$this->upload();
			JFactory::getApplication()->close();
		}
	}

	/**
	 * Handle the upload of a KML file
	 *
	 * @return void
	 */
	public function upload()
	{
		$params = JComponentHelper::getParams('com_media');
		$input = JFactory::getApplication()->input;

		// Check for request forgeries
		if (!JSession::checkToken('request'))
		{
			$response = array(
				'status' => '0',
				'error' => JText::_('JINVALID_TOKEN')
			);
			echo json_encode($response);

			return;
		}

		// Get some data from the request
		$file = $input->files->get('files', '', 'array');
		$file = $file[0];

		if ($_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			|| $_SERVER['CONTENT_LENGTH'] > $this->toBytes(ini_get('upload_max_filesize'))
			|| $_SERVER['CONTENT_LENGTH'] > $this->toBytes(ini_get('post_max_size'))
			|| $_SERVER['CONTENT_LENGTH'] > $this->toBytes(ini_get('memory_limit')))
		{
			$response = array(
				'status' => '0',
				'error' => JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE')
			);
			echo json_encode($response);

			return;
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		// Make the filename safe
		$file['name'] = JFile::makeSafe($file['name']);

		if (isset($file['name']))
		{
			// The request is valid
			$err = null;

			$filepath = JPath::clean(JPATH_SITE . '/media/com_hotspots/import/kmls/' . ($file['name']));

			$format = strtolower(JFile::getExt($file['name']));

			if (!in_array($format, array('kml', 'KML')))
			{
				echo json_encode(array('error' => 'not allowed format'));

				return;
			}

			$object_file = new JObject($file);
			$object_file->filepath = $filepath;

			if (!JFile::upload($object_file->tmp_name, $object_file->filepath))
			{
				// Error in upload
				JLog::add('Error on upload: ' . $object_file->filepath, JLog::INFO, 'upload');

				$response = array(
					'status' => '0',
					'error' => JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE')
				);

				echo json_encode($response);

				return;
			}
			else
			{
				$response = array(
					'files' => array(
						array(
							'name' => $object_file->name,
							'size' => $object_file->size,
							'type' => $object_file->type,
							'url' => Juri::root() . '/media/com_hotspots/import/kmls/' . $object_file->name,
							'deleteUrl' => 'index.php?option=com_hotspots&task=import.kml&action=delete&file=' . $object_file->name . '&' . JSession::getFormToken() . '=1'
						)
					)
				);

				echo json_encode($response);

				return;
			}
		}

	}

	/**
	 * Small helper function that properly converts any
	 * configuration options to their byte representation.
	 *
	 * @param   string|integer  $val  The value to be converted to bytes.
	 *
	 * @return integer The calculated bytes value from the input.
	 *
	 * @since 3.3
	 */
	public function toBytes($val)
	{
		switch ($val[strlen($val) - 1])
		{
			case 'M':
			case 'm':
				return (int) $val * 1048576;
			case 'K':
			case 'k':
				return (int) $val * 1024;
			case 'G':
			case 'g':
				return (int) $val * 1073741824;
			default:
				return $val;
		}
	}

	/**
	 * Reads KML files and looks for the Style property. Then creates
	 * an array with available styles
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function kmlmapping()
	{
		$input = JFactory::getApplication()->input;
		$categories = JCategories::getInstance('Hotspots')->get();
		$files = $input->get('kmls', '', 'array');
		$cats = array();
		$kmls = $this->getKMLFiles($files);
		$styleMaps = array();
		$options = array();
		$mappings = array();

		foreach ($kmls as $kml)
		{
			// Find out if we have StyleMaps build an array with styleUrl to styleMap
			if (isset($kml->Document->StyleMap))
			{
				foreach ($kml->Document->StyleMap as $styleMap)
				{
					foreach ($styleMap->Pair as $pair)
					{
						$styleMaps[str_replace('#', '', (string) $pair->styleUrl)] = (string) $styleMap['id'];
					}
				}
			}

			if (isset($kml->Document->Style))
			{
				foreach ($kml->Document->Style as $style)
				{
					if (isset($style->IconStyle))
					{
						$id = (string) $style['id'];

						// If we have a StyleMaps entry, then let's use it instead of the Style itself.
						if (isset($styleMaps[$id]))
						{
							$id = $styleMaps[$id];
						}

						$options[$id] = array(
							'id' => $id,
							'icon' => (string) $style->IconStyle->Icon->href
						);
					}
				}
			}
		}

		$mappings[] = array(
			'id' => 'Default mapping (when no style mapping can be found)',
			'icon' => Juri::root() . '/media/com_hotspots/images/v4/icons/pin.png'
		);

		// Let's build an array with int keys (we need this for our js template)
		foreach ($options as $option)
		{
			$mappings[] = $option;
		}

		foreach ($categories->getChildren(true) as $cat)
		{
			$cats[] = array(
				'id' => $cat->id,
				'title' => $cat->title
			);
		}

		$response = array(
			'mappings' => $mappings,
			'cats' => $cats
		);
		echo json_encode($response);

		JFactory::getApplication()->close();
	}

	/**
	 * Gets an array with KML files (converted to object out of the KML file)
	 *
	 * @param   array  $files  -  array with file names
	 *
	 * @return array
	 */
	private function getKMLFiles($files)
	{
		$path = JPATH_SITE . '/media/com_hotspots/import/kmls/';
		$kmls = array();

		foreach ($files as $file)
		{
			if (file_exists($path . $file))
			{
				libxml_use_internal_errors(true);
				$kmls[] = simplexml_load_file($path . $file);
			}
		}

		return $kmls;
	}



	/**
	 * A recursive function that goes through the available Folders and looks for Placemarks
	 *
	 * @param   object  $obj  - the XML node to look for a folder
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function findLocationsInFolder($obj)
	{
		$input = JFactory::getApplication()->input;
		$styles = $input->get('styles', '', 'array');

		if (isset($obj->Folder))
		{
			foreach ($obj->Folder as $folder)
			{
				foreach ($folder->Placemark as $placemark)
				{
					$location = $this->createLocation($placemark, $styles);

					if ($location)
					{
						self::$locations[] = $location;
					}
				}

				if (isset($folder->Folder))
				{
					$this->findLocationsInFolder($folder);
				}
			}
		}
	}

	/**
	 * Does the import of a KML's locations
	 * Looks for Placemark in first level or in in first level Folder
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function kmlimport()
	{
		$input = JFactory::getApplication()->input;
		$styles = $input->get('styles', '', 'array');
		$files = $input->get('kmls', '', 'array');
		$kmls = $this->getKMLFiles($files);
		$db = JFactory::getDbo();
		$query = $db->getQuery();

		foreach ($kmls as $kml)
		{
			if (isset($kml->Document->Placemark))
			{
				foreach ($kml->Document->Placemark as $placemark)
				{
					$location = $this->createLocation($placemark, $styles);

					if ($location)
					{
						self::$locations[] = $location;
					}
				}
			}

			// Look for Placemarks in a Folder node
			$this->findLocationsInFolder($kml->Document);

			// Some KML files don't have a Document
			$this->findLocationsInFolder($kml);
		}

		foreach (self::$locations as $key => $location)
		{
			foreach ($location as $vkey => $vvalue)
			{
				self::$locations[$key][$vkey] = $db->quote($vvalue);
			}

			self::$locations[$key] = implode(',', self::$locations[$key]);
		}

		$toInsert = array_chunk(self::$locations, 1000);

		foreach ($toInsert as $chunk)
		{
			$query->clear();
			$query->insert('#__hotspots_marker')
				->columns(
					'title, alias, catid, description, description_small, street, plz, town, country, administrative_area_level_1, created, access, asset_id, language, state, gmlat, gmlng, created_by, modified_by, created_by_alias, params, customfields, import_table'
				)
				->values($chunk);
			$db->setQuery($query);

			$db->execute();
		}

		$response = array(
			'imported' => count(self::$locations)
		);

		echo json_encode($response);
		JFactory::getApplication()->close();
	}

    private function generateAlias($title) {
        $alias = rand(0, 10000000) . '-' .JFilterOutput::stringURLSafe($title);

        return $alias;
    }
	/**
	 * Creates a location array out of the Placemark object
	 *
	 * @param   object  $placemark  - the placemark from the KML file
	 * @param   array   $styles     - mapping of KML styles to Hotspots categories
	 *
	 * @return array|bool
	 */
	private function createLocation($placemark, $styles)
	{
		// If the placemark doesn't have a Point node, then we are not dealing with locations
		if (isset($placemark->Point))
		{
			$coordinates = explode(',', $placemark->Point->coordinates);

			if (isset($styles[str_replace('#', '', $placemark->styleUrl)]))
			{
				$catid = $styles[str_replace('#', '', $placemark->styleUrl)];
			}
			else
			{
				// If we don't have a mapping, then use the default option for this location
				$catid = reset($styles);
			}

			$location = array(
				'title' => (string) $placemark->name,
                'alias' => self::generateAlias((string) $placemark->name),
				'catid' => $catid,
				'description' => "",
				'description_small' => (string) $placemark->description,
                "street" => "",
                "plz" => "", "town" => "", "country" => "", "administrative_area_level_1" => "",
				'created' => JFactory::getDate()->toSql(),
				'access' => 1,
                'asset_id' => 0,
				'language' => '*',
				'state' => 1,
				'gmlat' => $coordinates[1],
				'gmlng' => $coordinates[0],
				'created_by' => \Joomla\CMS\Factory::getUser()->id,
				'modified_by' => \Joomla\CMS\Factory::getUser()->id,
				'created_by_alias' => \Joomla\CMS\Factory::getUser()->name,
                'params' => "",
                "customfields" => "",
				'import_table' => 'kml'
			);

			return $location;
		}

		return false;
	}

	/**
	 * Does the import
	 *
	 * @return bool
	 */
	public function import()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$input = JFactory::getApplication()->input;

		$db_max_execution_time = $input->get('db_max_execution_time', 30);

		if ($db_max_execution_time != "30")
		{
			ini_set('max_execution_time', $db_max_execution_time);
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$where = '';

		if ($input->get('sobi2published', 0) == 1)
		{
			$where = " o.state = " . $db->q(1);
		}

		if ($input->get('delete_old', 1) == 1)
		{
			$cats = JCategories::getInstance('Hotspots')->get()->getChildren(true);
			$catsToDelete = array();

			// Get the categories to delete
			foreach ($cats as $cat)
			{
				$params = new JRegistry($cat->params);

				if ($params->get('import_table') == 'sobipro')
				{
					$catsToDelete[$params->get('import_id')] = $cat->id;
				}
			}

			// Now lets actually go over the categories and delete them
			foreach ($catsToDelete as $delete)
			{
				$table = JTable::getInstance('Category', 'JTable');
				$table->extension = 'com_hotspots';
				$table->id = $delete;

				$table->delete($delete, true);
			}

			// Delete the markers
			$query->clear();
			$query->delete('#__hotspots_marker')
				->where('import_table like "%sobipro%"');
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return false;
			}
		}

		$name_fieldid = $input->get('name_fieldid', 1);
		$street_fieldid = $input->get('street_fieldid', 1);
		$zip_fieldid = $input->get('zip_fieldid', 2);
		$town_fieldid = $input->get('town_fieldid', 3);
		$country_fieldid = $input->get('country_fieldid', 6);
		$mail_fieldid = $input->get('mail_fieldid', 7);
		$web_fieldid = $input->get('web_fieldid', 8);
		$phone_fieldid = $input->get('phone_fieldid', 10);
		$description_fieldid = $input->get('description_fieldid', 13);
		$lng_fieldid = $input->get('lng_fieldid', 50);
		$lat_fieldid = $input->get('lat_fieldid', 51);
		$sobi2_link = $input->get('sobi2_link', 1);
		$geomap = $input->get('geomap', 0);

		$query->clear();
		$query->select('c.id AS catid, o.name AS cat_name, c.description, o.state as published')
			->from('#__sobipro_category AS c')
			->leftJoin('#__sobipro_object AS o ON c.id = o.id');

		if ($where)
		{
			$query->where($where);
		}

		$db->setQuery($query);
		$sobiProCats = $db->loadObjectList();

		// Import Categories
		foreach ($sobiProCats as $category)
		{
			$table = JTable::getInstance('Category', 'JTable');
			$row = array();
			$row['title'] = $category->cat_name;
			$row['description'] = $category->description;
			$row['extension'] = 'com_hotspots';
			$row['parent'] = 1;

			$row['published'] = $category->published;
			$row['created_time'] = JFActory::getDate()->toSql();
			$row['params']['import_table'] = 'sobipro';
			$row['params']['import_id'] = $category->catid;

			$table->setLocation(1, 'last-child');
			$rules = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';
			$table->setRules($rules);


			$table->bind($row);
			$table->check();
			$table->store();
		}

		$query->clear();
		$query->select('o.*,d.*')
			->from('#__sobipro_object AS o')
			->leftJoin('#__sobipro_field_data AS d ON o.id = d.sid')
			->where('oType = ' . $db->quote('entry'))
			->where(
				"d.fid IN ($name_fieldid,
                                $street_fieldid,
                                $zip_fieldid,
                                $town_fieldid,
                                $country_fieldid,
                                $mail_fieldid,
                                $web_fieldid,
                                $phone_fieldid,
                                $description_fieldid,
                                $lng_fieldid,
                                $lat_fieldid
                    )"
			);

		if ($geomap)
		{
			$query->select(array('g.latitude', 'g.longitude'))
				->leftJoin('#__sobipro_field_geo AS g ON o.id = g.sid');
		}

		$db->setQuery($query);
		$spObjects = $db->loadObjectList();

		foreach ($spObjects as $o)
		{
			if ($o->fid == $name_fieldid)
			{
				$hotspots[$o->id]['id'] = $o->id;
				$hotspots[$o->id]['owner'] = $o->owner;
				$hotspots[$o->id]['name'] = $o->baseData;
			}

			if ($o->fid == $lng_fieldid)
			{
				$hotspots[$o->id]['lng'] = (float) $o->baseData;

				if ($geomap)
				{
					if ($o->longitude)
					{
						$hotspots[$o->id]['lng'] = (float) $o->longitude;
					}
				}
			}

			if ($o->fid == $lat_fieldid)
			{
				$hotspots[$o->id]['lat'] = (float) $o->baseData;

				if ($geomap)
				{
					if ($o->latitude)
					{
						$hotspots[$o->id]['lat'] = (float) $o->latitude;
					}
				}
			}

			if ($o->fid == $street_fieldid)
			{
				$hotspots[$o->id]['street'] = $o->baseData;
			}

			if ($o->fid == $zip_fieldid)
			{
				$hotspots[$o->id]['plz'] = $o->baseData;
			}

			if ($o->fid == $town_fieldid)
			{
				$hotspots[$o->id]['town'] = $o->baseData;
			}

			if ($o->fid == $country_fieldid)
			{
				$hotspots[$o->id]['country'] = $o->baseData;
			}

			if ($o->fid == $mail_fieldid)
			{
				$hotspots[$o->id]['mail'] = $o->baseData;
			}

			if ($o->fid == $web_fieldid)
			{
				// Check if the field is base64 - if not just add the data
				$data = base64_decode($o->baseData, true);

				if ($data)
				{
					$data = unserialize(base64_decode($o->baseData));
					$hotspots[$o->id]['web'] = $data['protocol'] . '://' . $data['url'];
				}
				else
				{
					$hotspots[$o->id]['web'] = $o->baseData;
				}
			}

			if ($o->fid == $phone_fieldid)
			{
				$hotspots[$o->id]['phone'] = $o->baseData;
			}

			if ($o->fid == $description_fieldid)
			{
				$hotspots[$o->id]['description'] = $o->baseData;
			}
		}

		$query->clear();
		$query->select('*')
			->from('#__sobipro_object AS o')
			->leftJoin('#__sobipro_field_option_selected AS s ON o.id = s.sid')
			->where('oType = ' . $db->quote('entry'))
			->where('s.fid = ' . $db->quote($country_fieldid));
		$db->setQuery($query);
		$spObjects = $db->loadObjectList();

		foreach ($spObjects as $o)
		{
			$hotspots[$o->id]['country'] = $o->optValue;
		}

		$query->clear();
		$query->select('o.id, o.nid, r.*')
			->from('#__sobipro_object AS o')
			->leftJoin('#__sobipro_relations AS r ON o.id = r.id')
			->where('o.oType = ' . $db->quote('entry'))
			->group('o.id');
		$db->setQuery($query);
		$spObjects = $db->loadObjectList();

		foreach ($spObjects as $o)
		{
			if (isset($hotspots[$o->id]))
			{
				$hotspots[$o->id]['catid'] = $o->pid;
			}
		}

		$query->clear();

		// Get the category mappings
		$cats = JCategories::getInstance('Hotspots')->get()->getChildren();
		$sobiMapping = array();

		// Create the sobiPRO to Joomla mapping
		foreach ($cats as $cat)
		{
			$params = new JRegistry($cat->params);

			if ($params->get('import_table') == 'sobipro')
			{
				$sobiMapping[$params->get('import_id')] = $cat->id;
			}
		}

		$hInsert = array();

		foreach ($hotspots as $value)
		{
			$params = array();
			$descriptionSmall = array();

			if (!isset($value['street']))
			{
				$value['street'] = '';
			}

			if ($value['phone'])
			{
				$descriptionSmall[] = $value['phone'] . '<br />';
			}

			if ($value['mail'])
			{
				$mail = unserialize(base64_decode($value['mail']));
				$descriptionSmall[] = '<a href="mailto:' . $mail->url . '">' . ($mail->label ? $mail->label : $mail->url) . '</a><br />';
			}

			$params['sticky'] = 0;

			if ($sobi2_link)
			{
				$params['link_to'] = 'sobipro';
				$params['link_to_id'] = $value['id'];
			}

			if (!isset($value['lat']))
			{
				$value['lat'] = 0;
			}

			if (!isset($value['lng']))
			{
				$value['lng'] = 0;
			}

			$hInsert[] = $db->quote($value['title'])
				. ',' . $db->quote($value['owner'])
				. ',' . $db->quote(date('Y-m-d H:i:s'))
				. ',' . $db->quote($sobiMapping[$value['catid']])
				. ',' . $db->quote($value['plz'])
				. ',' . $db->quote($value['town'])
				. ',' . $db->quote($value['street'])
				. ',' . $db->quote($value['country'])
				. ',' . $db->quote($value['lat'])
				. ',' . $db->quote($value['lng'])
				. ',' . $db->quote(1)
				. ',' . $db->quote(implode('', $descriptionSmall) . $value['description'])
				. ',' . $db->quote(json_encode($params))
				. ',' . $db->quote('sobipro')
				. ',' . $db->quote($value['id']);
		}

		$toInsert = array_chunk($hInsert, 1000);

		foreach ($toInsert as $chunk)
		{
			$query->clear();
			$query->insert('#__hotspots_marker')
				->columns(
					'title,created_by,created, catid, plz, town, street,
									country, gmlat, gmlng, access, description_small, params, import_table, import_id'
				)
				->values($chunk);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return false;
			}
		}

		$this->setRedirect('index.php?option=com_hotspots&view=hotspots', JText::_('COM_HOTSPOTS_SOBIPRO_SUCCESSFULL_IMPORT'));
	}

	/**
	 * Import from Phocamaps
	 *
	 * @return bool
	 */
	public function importFromPhoca()
	{
		JLoader::discover('HotspotsImporter', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/importers');

		$importer = new HotspotsImporterPhoca;

		if ($importer->import())
		{
			$this->setRedirect('index.php?option=com_hotspots&view=hotspots', JText::_('COM_HOTSPOTS_IMPORT_PHOCAMAPS_WAS_SUCCESSFULL'));
		}

		return false;
	}
}
