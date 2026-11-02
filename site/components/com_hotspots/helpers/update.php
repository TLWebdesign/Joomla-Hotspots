<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       20.01.2015
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Load the category for j2.5
jimport('joomla.application.categories');

/**
 * Class HotspotsHelperUpdate
 *
 * @since  5.0
 */
class HotspotsHelperUpdate
{
	/**
	 * Starting with Hotspots 5 we no longer use the #__hotspots_category table.
	 * We've moved our categories to #__categories. Because of this now we need
	 * to check if the #__hotspots_category table exist, if it does, then we'll add
	 * the categories to #__categories and we'll update the hotspots, kmls, customfields
	 * with the new ids.
	 *
	 * @return void
	 */
	public static function fixCategories()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$allTables = $db->getTableList();

		// Does the table exist?
		$tableNormal = $db->replacePrefix('#__hotspots_categorie');
		$tableExists = in_array($tableNormal, $allTables);

		if ($tableExists)
		{
			$query->select('*')->from('#__hotspots_categorie');
			$db->setQuery($query);

			$categories = $db->loadObjectList();
			$fails = array();
			$mappings = array();

			foreach ($categories as $category)
			{
				$params = new JRegistry($category->params);
				$icon = $category->cat_icon;

				if (substr($icon, 0,  1) == '/')
				{
					$icon = preg_replace('/\//', '', $category->cat_icon, 1);
				}

				$table = JTable::getInstance('Category', 'JTable');
				$randomStuff = '';

				if (self::checkCategoryExists($category->cat_name))
				{
					// Generate a random string to prevent the savign of the category when we don't have
					// unique alias
					$randomStuff = substr(
							str_pad(
								base_convert(
									md5(
										mt_rand() . microtime(true)
									), 16, 36
								), 25, '0'), 0, 10
						) . '-';
				}

				$category_array = array();
				$category_array['title'] = $category->cat_name;
				$category_array['alias'] = $randomStuff . \Joomla\CMS\Filter\OutputFilter::stringURLSafe($category->cat_name); // TLWebdesign patch 2026-11-02: JApplication was removed in Joomla 4
				$category_array['description'] = $category->cat_description;
				$category_array['extension'] = "com_hotspots";
				$category_array['parent'] = 1;
				$category_array['published'] = $category->published;
				$category_array['params'] = json_encode(
					array(
						'icon' => 'media/com_hotspots/images/categories/' . $icon,
						'tile_marker_color' => $params->get('tile_marker_color', '0,0,0'),
						'import_table' => '#__hotspots_categorie',
						'import_id' => (int) $category->id
					)
				);
				$category_array['created_user_id'] = $user->id;
				$category_array['access'] = 1;
				$category_array['language'] = '*';
				$category_array['metadata'] = '{"page_title":"","author":"","robots":"", "tags":null}';
				$rules = '{"core.delete":[],"core.edit":[],"core.edit.state":[]}';
				$table->setRules($rules);
				$table->setLocation(1, 'last-child');

				if (!$table->bind($category_array))
				{
					$fails[] = $category_array;
					continue;
				}

				if (!$table->check())
				{
					$fails[] = $category_array;
					continue;
				}

				if (!$table->store())
				{
					$fails[] = $category_array;
					continue;
				}

				$mappings[$category->id] = $table->id;
			}

			if (count($mappings))
			{
				foreach ($mappings as $key => $mapping)
				{
					// Update the Marker table
					$query->clear();
					$query->update('#__hotspots_marker')->set($db->qn('catid') . '=' . $db->q($mapping))
						->where($db->qn('catid') . '=' . $db->q($key));

					$db->setQuery($query);
					$db->execute();

					// Update the KML table
					$query->clear();
					$query->update('#__hotspots_kmls')->set($db->qn('catid') . '=' . $db->q($mapping))
						->where($db->qn('catid') . '=' . $db->q($key));

					$db->setQuery($query);
					$db->execute();
				}

				// Update custom fields mappings if any
				$query->clear();
				$query->select('id')->from('#__compojoom_customfields')
					->where($db->qn('component') . ' = ' . $db->q('com_hotspots.hotspot'))
					->where($db->qn('show') . ' = ' . $db->q('category'));

				$db->setQuery($query);
				$customfields = $db->loadObjectList();

				foreach ($customfields as $customfield)
				{
					$query->clear();
					$query->select('*')->from('#__compojoom_customfields_cats')
						->where('compojoom_customfields_id = ' . $db->q($customfield->id));
					$db->setQuery($query);

					$rels = $db->loadObjectList();

					foreach ($rels as $rel)
					{
						$query->clear();

						if (isset($mappings[$rel->catid]))
						{
							$query->update('#__compojoom_customfields_cats')
								->set('catid = ' . $db->q($mappings[$rel->catid]))
								->where('compojoom_customfields_id = ' . $rel->compojoom_customfields_id)
								->where('catid = ' . $db->q($rel->catid));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}

			if (count($fails))
			{
				foreach ($fails as $fail)
				{
					JFactory::getApplication()->enqueueMessage('We failed to automatically migrate the following category' . $fail['title']);
				}
			}
			else
			{
				// Update the menu if necessary
				HotspotsHelperMenu::updateMenuTo5();

				// Clean up! Drop the category table
				$db->dropTable('#__hotspots_categorie');
				JFactory::getApplication()->enqueueMessage('Pfew, it seems that we managed to migrate your categories');
			}
		}
	}

	/**
	 * Checks if a category with the same name already exists
	 *
	 * @param   string  $name  - the category name
	 *
	 * @return bool
	 */
	public static function checkCategoryExists($name)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__categories');
		$query->where($db->qn('extension') . '=' . $db->q('com_hotspots'));
		$query->where($db->qn('title') . '=' . $db->q($name));

		$db->setQuery($query);

		$result = $db->loadObject();

		return $result ? true : false;
	}
}
