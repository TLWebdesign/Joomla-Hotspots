<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       08.01.15
 *
 * @copyright  Copyright (C) 2008 - 2015 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controlleradmin');

/**
 * Class HotspotsControllerhotspots
 *
 * @since  3.0
 */
class HotspotsControllerhotspots extends JControllerAdmin
{
	/**
	 * The constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('unpublish', 'publish');
	}

	/**
	 * Finds out the address of a hotspot(s)
	 *
	 * @return void
	 */
	public function geocode()
	{
		// TLWebdesign patch 2026-11-02: check for request forgeries.
		if (!\Joomla\CMS\Session\Session::checkToken('request'))
		{
			echo json_encode(array('status' => 'FAILURE', 'message' => \Joomla\CMS\Language\Text::_('JINVALID_TOKEN')));
			jexit();
		}

		require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/geocoder/geocoder.php';
		$update = array();
		$geocoder = new HotspotsGeocoder;

		$input = JFactory::getApplication()->input;
		$cid = $input->get('cid', array(), 'array');

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('*')->from($db->qn('#__hotspots_marker'))->where(CompojoomQueryHelper::in('id', $cid, $db));

		$db->setQuery($query);

		$hotspots = $db->loadObjectList();

		foreach ($hotspots as $hotspot)
		{
			if ($hotspot->gmlat > 0 && $hotspot->gmlng > 0)
			{
				$data = $geocoder->reverseGeocode($hotspot->gmlat, $hotspot->gmlng);

				if (is_array($data))
				{
					if ($data['status'] == 'OK')
					{
						$query->clear();
						$address = $data['address'];
						$query->update('#__hotspots_marker');


						if (isset($address['street']))
						{
							$query->set('street = ' . $db->q($address['street']));
						}

						if (isset($address['plz']))
						{
							$query->set('plz = ' . $db->q($address['plz']));
						}

						if (isset($address['town']))
						{
							$query->set('town = ' . $db->q($address['town']));
						}

						if (isset($address['country']))
						{
							$query->set('country = ' . $db->q($address['country']));
						}

						$query->where('id = ' . $db->Quote($hotspot->id));
						$update[] = $query->__toString();
					}
				}
			}
			else
			{
				$address = $hotspot->street . ',' . $hotspot->plz . ',' . $hotspot->town . ',' . $hotspot->country;

				$data = $geocoder->geocode($address);

				if (is_array($data))
				{
					if ($data['status'] == 'OK')
					{
						$update[] = 'UPDATE #__hotspots_marker SET '
							. ' gmlat = ' . $db->Quote($data['location']->lat) . ','
							. ' gmlng = ' . $db->Quote($data['location']->lng)
							. ' WHERE id = ' . $db->Quote($hotspot->id);
					}
				}
			}
		}

		if (count($update))
		{
			// TLWebdesign patch 2026-11-02: execute() throws on Joomla 4+ instead of returning false.
			$status = 'OK';
			$message = JText::sprintf('COM_HOTSPOTS_GEOCODING_COORDINATES_UPDATED', count($update));

			foreach ($update as $query)
			{
				try
				{
					$db->setQuery($query)->execute();
				}
				catch (\RuntimeException $e)
				{
					$status = 'FAILURE';
					$message = JText::sprintf('COM_HOTSPOTS_GEOCODING_FAILURE');
				}
			}
		}
		else
		{
			$status = 'OK';
			$message = JText::_('COM_HOTSPOTS_GEOCODING_NOTHING_TO_UPDATE');
		}

		$response = array(
			'status' => $status,
			'message' => $message
		);
		echo json_encode($response);
		jexit();
	}

	/**
	 * Deletes the hotspots
	 *
	 * @return void
	 */
	public function remove()
	{
		// TLWebdesign patch 2026-11-02: token and permission check, ids as integers, execute() throws on Joomla 4+.
		\Joomla\CMS\Session\Session::checkToken() or jexit(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'));

		$appl = JFactory::getApplication();

		if (!$appl->getIdentity()->authorise('core.delete', 'com_hotspots'))
		{
			$this->setRedirect('index.php?option=com_hotspots&view=hotspots', JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'error');

			return;
		}

		$input = $appl->input;
		$cid = \Joomla\Utilities\ArrayHelper::toInteger($input->get('cid', array(), 'array'));
		$db = JFactory::getDbo();
		$msg = Jtext::_('COM_HOTSPOTS_REMOVE_HOTSPOTS_FAILED');

		// Import the appropriate plugin group.
		\Joomla\CMS\Plugin\PluginHelper::importPlugin('hotspots');

		if (count($cid))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__hotspots_marker'))
				->whereIn($db->quoteName('id'), $cid);

			try
			{
				$db->setQuery($query)->execute();
				$msg = JText::_('COM_HOTSPOTS_REMOVE_HOTSPOTS_SUCCESS');
				$appl->triggerEvent('onAfterHotspotDelete', array('com_hotspots.hotspot', $cid));
			}
			catch (\RuntimeException $e)
			{
				$appl->enqueueMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect('index.php?option=com_hotspots&view=hotspots', $msg);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string $name   The name of the model.
	 * @param   string $prefix The prefix for the PHP class name.
	 *
	 * @return  JModel
	 * @since   1.6
	 */
	public function getModel($name = 'Hotspot', $prefix = 'HotspotsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Publish/unpublish a hotspot
	 *
	 * @return void
	 */
	public function publish()
	{
		// TLWebdesign patch 2026-11-02: check for request forgeries.
		\Joomla\CMS\Session\Session::checkToken() or jexit(\Joomla\CMS\Language\Text::_('JINVALID_TOKEN'));

		$input = JFactory::getApplication()->input;
		$cid = \Joomla\Utilities\ArrayHelper::toInteger($input->get('cid', array(), 'array'));

		if ($this->task == 'publish')
		{
			$publish = 1;
		}
		else
		{
			$publish = 0;
		}

		$model = $this->getModel();

		try
		{
			$model->publish($cid, $publish);

			if ($publish == 1)
			{
				$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
			}
			elseif ($publish == 0)
			{
				$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
			}

			$this->setMessage(JText::plural($ntext, count($cid)));
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
		}

		$link = 'index.php?option=com_hotspots&view=hotspots';
		$this->setRedirect($link);
	}
}
