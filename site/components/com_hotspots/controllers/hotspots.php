<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       02.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * Class HotspotsControllerHotspots
 *
 * @since  3
 */
class HotspotsControllerHotspots extends JControllerAdmin
{
	protected $view_list = "userhotspots";

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = 'Hotspot', $prefix = 'HotspotsModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Creates the rss feed
	 *
	 * @return void
	 */
	public function rss()
	{
		jimport('joomla.filesystem.folder');
		require_once JPATH_COMPONENT_ADMINISTRATOR . "/libraries/rss/feedcreator.php";

		$rss = new UniversalFeedCreator;
		$folderPath = JPATH_CACHE . '/com_hotspots/rss';
		$folderExists = JFolder::exists($folderPath);

		if (!$folderExists)
		{
			JFolder::create($folderPath);
		}

		$rss->useCached("RSS2.0", $folderPath . '/hotspotsfeed.xml');

		$rss->title = JURI::Base() . " - " . JTEXT::_('Newest Hotspots');
		$rss->description = JTEXT::_('New Hotspots at') . ' ' . JURI::Base();
		$rss->link = JURI::Base();

		$image = new FeedImage;
		$image->title = JURI::Base() . " " . "Hotspots";
		$image->url = HotspotsHelperSettings::get('rss_logopath', JURI::Base() . "media/com_hotspots/images/utils/logo.jpg");
		$image->link = JURI::Base();
		$image->description = JTEXT::_('Feed provided by') . " " . JURI::Base() . ". " . JTEXT::_('Click to visit');
		$rss->image = $image;
		$hs_show_address = HotspotsHelperSettings::get('show_address', 1);
		$hs_show_address_country = HotspotsHelperSettings::get('show_address_country', 0);
		$hs_show_author = HotspotsHelperSettings::get('show_author', 1);
		$hs_show_detailpage = HotspotsHelperSettings::get('hotspot_detailpage', 1);
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__hotspots_marker WHERE state = 1 ORDER BY created DESC", 0, HotspotsHelperSettings::get('rss_limit', "100"));
		$rows = $db->loadObjectList();

		if ($rows != null)
		{
			foreach ($rows as $row)
			{
				$row->hotspots_id = $row->id;
				$name = htmlspecialchars("$row->title");
				$street = htmlspecialchars("$row->street");
				$plz = htmlspecialchars("$row->plz");
				$town = htmlspecialchars("$row->town");
				$country = htmlspecialchars("$row->country");

				if ($hs_show_address == "1")
				{
					if ($hs_show_address_country == "1")
					{
						$adress = "$street, $plz $town<br />$country<br /><br />";
					}
					else
					{
						$adress = "$street, $plz $town<br /><br />";
					}
				}

				if ($hs_show_detailpage == "1")
				{
					$mlink = HotspotsHelperUtils::createLink($row);
				}

				$author = '';

				if ($hs_show_author == "1")
				{
					if ($row->created_by_alias)
					{
						$author = $row->created_by_alias;
					}
					elseif ($row->created_by)
					{
						$user = JFactory::getUser($row->created_by);

						if (HotspotsHelperSettings::get('use_name', 1))
						{
							$author = $user->name;
						}
						else
						{
							$author = $user->username;
						}
					}
				}

				if (substr(ltrim($mlink), 0, 7) != 'http://')
				{
					$uri = JURI::getInstance();
					$base = $uri->toString(array('scheme', 'host', 'port'));
					$mlink = $base . $mlink;
				}

				$rss_item = new FeedItem;
				$rss_item->title = $name;
				$rss_item->link = $mlink;
				$rss_item->description = $adress . $row->description_small;
				$rss_item->date = JFactory::getDate($row->created)->toRFC822();
				$rss_item->source = JURI::Base();
				$rss_item->author = $author;
				$rss->addItem($rss_item);
			}
		}

		$rss->cssStyleSheet = "http://www.w3.org/2000/08/w3c-synd/style.css";

		if (HotspotsHelperSettings::get('rss_type', 0) == 0)
		{
			$rss->saveFeed("RSS2.0", $folderPath . '/hotspotsfeed.xml');
		}
		elseif (HotspotsHelperSettings::get('rss_type', 0) == 1)
		{
			$rss->saveFeed("RSS1.0", $folderPath . '/hotspotsfeed.xml');
		}
		else
		{
			$rss->saveFeed("ATOM", $folderPath . '/hotspotsfeed.xml');
		}
	}

	/**
	 * Deletes a single hotspot from the database. First checks if there is an entry in the Database.
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function delete ()
	{
		// Check for request forgeries
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		// Import the appropriate plugin group.
		JPluginHelper::importPlugin('hotspots');

		$appl = JFactory::getApplication();

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!count($cid))
		{
			// We have no id, so exit
			return false;
		}

		$model = $this->getModel();
		$hotspot = $model->getItem($cid[0]);

		$canDelete = HotspotsHelperSecurity::authorise('delete', $hotspot);

		if ($canDelete)
		{
			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}

			$appl->triggerEvent('onAfterHotspotDelete', array('com_hotspots.hotspot', $cid));
		}

		$link = JRoute::_('index.php?option=com_hotspots&view=userhotspots', false);

		$this->setRedirect($link);
	}
}
