<?php
/**
 * @package    MatukioEvents
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-09-28
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe, Daniel Dimitrov. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

use Joomla\String\StringHelper;

/**
 * API Hotspots
 *
 * @since  6.1.2
 */
class HotspotsApiMarker extends HotspotsApi
{
	/**
	 * Get all markers
	 *
	 * index.php?option=com_hotspots&view=api&request=marker
	 *
	 * @return  array
	 *
	 * @since   6.1.2
	 */
	public function _()
	{
		$offset = $this->input->getInt('offset', 0);
		$limit  = $this->input->getInt('limit', 100);

		$markers = $this->loadHotspots($offset, $limit);

		return array(
			'markers' => $markers,
			'count'   => count($markers),
		);
	}

	/**
	 * Load Hotspot Markers
	 *
	 * @param   int $offset Offset
	 * @param   int $limit  Limit
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function loadHotspots($offset = 0, $limit = 100)
	{
		$query        = $this->db->getQuery(true);
		$onlyLoggedIn = $this->input->getBool('onlyLoggedIn', false);

		$query
			->select('*')
			->from('#__hotspots_marker')
			->where('state = 1');

		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

		$query->where('access IN (' . $groups . ')');

		// Load hotspots of only the logged in user
		if ($onlyLoggedIn)
		{
			$user = JFactory::getUser();

			if ($user->id)
			{
				$query->where('created_by = ' . $this->db->q($user->id));
			} else {
				return $this->getError(403, 'The user that is requesting his hotspots is not logged in.');
			}
		}

		$this->db->setQuery($query, $offset, $limit);

		return $this->db->loadObjectList();
	}

	/**
	 * Save an Hotspot
	 *
	 * POST to index.php?option=com_hotspots&view=api&request=marker&task=save
	 *
	 * Auth is done by Joomla
	 *
	 * @return  array  Error Message|Id of the new / updated Hotspot
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save()
	{
		/*
		 * TLWebdesign patch 2026-11-02: disabled. Nothing on the site uses this endpoint (it served Compojoom's
		 * mobile app). It had no CSRF check and bound raw request data to the table (stored XSS, setting
		 * state, created_by and access freely). Markers are saved through the normal form (task=hotspot.save).
		 */
		return $this->getError(403, 'This API task is disabled.');

		$user = JFactory::getUser();

		$data   = $this->input->get('data', '', 'raw');
		$images = $this->input->get('images', '', 'raw');

		if (empty($data))
		{
			return $this->getError(404, 'Error No data for field creation');
		}

		if (empty($images))
		{
			$images = array();
		}

		$table = JTable::getInstance('Marker', 'Table');

		$hotspot = json_decode($data, true);
		$images  = json_decode($images);

		$isOwn = true;
		$isNew = empty($hotspot->id);

		if (empty($hotspot['created_by']))
		{
			$hotspot['created_by'] = $user->id;
		}
		else if ($hotspot['created_by'] != $user->id)
		{
			$isOwn = false;
		}

		if (empty($hotspot['id']) && !HotspotsHelperSecurity::authorise('create'))
		{
			return $this->getError(403, 'No permission to save / update hotspot.');
		}
		else if (!empty($hotspot['id']) && $isOwn && !HotspotsHelperSecurity::authorise('edit.own', $hotspot))
		{
			return $this->getError(403, 'No permission to edit own hotspot.');
		}
		else if (!empty($hotspot['id']) && !HotspotsHelperSecurity::authorise('edit', $hotspot))
		{
			return $this->getError(403, 'No permission to edit not own hotspot.');
		}

		// Import the appropriate plugin group.
		JPluginHelper::importPlugin('hotspots');

		$appl = JFactory::getApplication();

		if (!$table->bind($hotspot))
		{
			return array('error', $table->getError());
		}

		$appl->triggerEvent('onContentBeforeSave', array('com_hotspots.hotspot', $hotspot));

		// Alias Handling
		if (empty($table->alias))
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$table->alias = JFilterOutput::stringURLUnicodeSlug($table->title);
			}
			else
			{
				$table->alias = JFilterOutput::stringURLSafe($table->title);
			}
		}

		$hotspotTable = JTable::getInstance('Marker', 'Table');

		while ($hotspotTable->load(array('alias' => $table->alias, 'catid' => $table->catid)))
		{
			$table->alias = StringHelper::increment($table->alias, 'dash');
		}

		if (!$table->store())
		{
			return array('error', $table->getError());
		}

		$appl->triggerEvent('onContentAfterSave', array('com_hotspots.hotspot', $hotspot, $isNew));

		$table->checkIn();

		$hotspotId  = $table->id;
		$targetPath = JPATH_CACHE . '/com_hotspots.multimedia/lib_compojoom.multimedia/';

		if (!JFolder::exists($targetPath))
		{
			JFolder::create($targetPath);
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_hotspots/models');
		JPluginHelper::importPlugin('hotspots');

		/** @var HotspotsModelMultimedia $multimediaModel */
		$multimediaModel = JModelLegacy::getInstance('Multimedia', 'HotspotsModel');

		// Save images first
		foreach ($images as $image)
		{
			/*
			 * TLWebdesign patch 2026-11-02: the file name and content come from the request.
			 * Without these checks "../" or a .php name writes any file on the server.
			 */
			$fileName = JFile::makeSafe(basename((string) ($image->filename ?? '')));
			$content  = (string) ($image->content ?? '');

			if ($fileName === '' || $fileName[0] === '.'
				|| !in_array(strtolower(JFile::getExt($fileName)), array('jpg', 'jpeg', 'png', 'gif', 'webp'), true)
				|| strpos($content, ',') === false)
			{
				continue;
			}

			list(, $content) = explode(',', $content, 2);
			$content = base64_decode($content, true);

			if ($content === false || @getimagesizefromstring($content) === false)
			{
				continue;
			}

			// We use file put content here
			$imageFile = $targetPath . '/' . $fileName;
			file_put_contents($imageFile, $content);

			$meta = array();

			$meta[$fileName]['title']       = $fileName;
			$meta[$fileName]['description'] = '';

			$appl->triggerEvent('onMultimediaBeforeUpload', array($table->get('id'), array($fileName), $meta));

			$multimediaModel->uploadPermanent($hotspotId, array($fileName), $meta);

			$appl->triggerEvent('onMultimediaAfterUpload', array($table->get('id'), array($fileName), $meta));
		}

		return array('id' => $hotspotId);
	}
}
