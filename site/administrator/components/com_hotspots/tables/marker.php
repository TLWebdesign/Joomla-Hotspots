<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       27.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper;
use \Joomla\CMS\Table\Table;
/**
 * Class TableMarker
 *
 * @since  1.0
 */
class TableMarker extends Table
{
	/**
	 * The constructor
	 *
	 * @param   JDatabaseDriver &$db - the db object
	 *
	 * @since   5.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__hotspots_marker', 'id', $db);
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean $updateNulls True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5.0
	 */
	public function store($updateNulls = true)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

        $this->modified    = $date->toSql();
        $this->modified_by = $user->get('id');

        // New Marker. Created and created_by field can be set by the user,
        // so we don't touch either of these if they are set.
        if (!intval($this->created))
        {
            $this->created = $date->toSql();
        }

        if (empty($this->created_by))
        {
            $this->created_by = $user->get('id');
        }

        // Created by alias should be non null value
        if (empty($this->created_by_alias)) {
            $this->created_by_alias = "";
        }

		// Verify that the alias is unique
		$table = JTable::getInstance('Marker', 'Table', array('dbo', $this->getDbo()));

		if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_ARTICLE_UNIQUE_ALIAS'));

			return false;
		}

		$result = parent::store($updateNulls);


		if (!$this->_trackAssets)
		{
			$this->clearAssets();
		}

		return $result;
	}

	/**
	 * Clear the assets if necessary
	 *
	 * @return bool
	 *
	 * @since version
	 */
	private function clearAssets()
	{
		// Get the asset name
		$name  = $this->_getAssetName();
		$asset = self::getInstance('Asset');

		if ($asset->loadByName($name))
		{
			if (!$asset->delete())
			{
				$this->setError($asset->getError());

				return false;
			}
		}
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed $array  An associative array or object to bind to the JTable instance.
	 * @param   mixed $ignore An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5.0
	 */
	public function bind($array, $ignore = '')
	{
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['hotspotText']))
		{
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos  = preg_match($pattern, $array['hotspotText']);

			if ($tagPos == 0)
			{
				$this->description_small = $array['hotspotText'];
				$this->description       = '';
			}
			else
			{
				list ($this->description_small, $this->description) = preg_split($pattern, $array['hotspotText'], 2);
			}
		}

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['customfields']) && is_array($array['customfields']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['customfields']);
			$array['customfields'] = (string) $registry;
		}

		// Bind the rules & decide if we have to track assets for this entry or not
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->_trackAssets = false;

			foreach ($array['rules'] as $rule)
			{
				if (count($rule))
				{
					$this->_trackAssets = true;
					break;
				}
			}

			if ($this->_trackAssets)
			{
				$rules = new JAccessRules($array['rules']);
				$this->setRules($rules);
			}
			else
			{
				$this->asset_id = 0;
			}
		}
		else
		{
			$this->_trackAssets = false;
			$this->asset_id     = 0;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   5.0
	 */
	public function check()
	{
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		self::generateAlias();

        // Set publish_up to null if not set
        if (!$this->publish_up)
        {
            $this->publish_up = null;
        }

        // Set publish_down to null if not set
        if (!$this->publish_down)
        {
            $this->publish_down = null;
        }

        // Check the publish down date is not earlier than publish up.
        if (!is_null($this->publish_up) && !is_null($this->publish_down) && $this->publish_down < $this->publish_up)
        {
            // Swap the dates.
            $temp = $this->publish_up;
            $this->publish_up = $this->publish_down;
            $this->publish_down = $temp;
        }

		return true;
	}

    /**
     * Try to generate an alias if no alias is provided
     *
     * @return mixed
     */
	public function generateAlias() {
        if ($this->alias === null || $this->alias === '')
        {
            if (JFactory::getConfig()->get('unicodeslugs') == 1)
            {
                $this->alias = JFilterOutput::stringURLUnicodeSlug($this->title);
            }
            else
            {
                $this->alias = JFilterOutput::stringURLSafe($this->title);
            }

            $table = JTable::getInstance('Marker', 'Table');

            if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)))
            {
                $msg = JText::_('COM_HOTSPOTS_SAVE_WARNING');
            }

            list($title, $alias) = $this->generateNewTitle($this->catid, $this->alias, $this->title);
            $this->alias = $alias;

            if (isset($msg))
            {
                JFactory::getApplication()->enqueueMessage($msg, 'warning');
            }
        }
        else {
            $this->alias = JApplicationHelper::stringURLSafe($this->alias, $this->language);
        }

        return $this->alias;
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $category_id  The id of the category.
     * @param   string   $alias        The alias.
     * @param   string   $title        The title.
     *
     * @return	array  Contains the modified title and alias.
     *
     * @since	1.7
     */
    protected function generateNewTitle($category_id, $alias, $title)
    {
        // Alter the title & alias
        $table = JTable::getInstance('Marker', 'Table', array('dbo', $this->getDbo()));

        while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
        {
            $title = StringHelper::increment($title);
            $alias = StringHelper::increment($alias, 'dash');
        }

        return array($title, $alias);
    }

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   5.0
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_hotspots.hotspot.' . (int) $this->$k;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   JTable  $table A JTable object for the asset parent.
	 * @param   integer $id    Id to look up
	 *
	 * @return  integer
	 *
	 * @since   5.0
	 */
	protected function _getAssetParentId(?JTable $table = null, $id = null) // TLWebdesign patch 2026-11-02: explicit nullable for PHP 8.4
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_hotspots');

		return $asset->id;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table. The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed   $pks    An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer $state  The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer $userId The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		\Joomla\Utilities\ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('state') . ' = ' . (int) $state)
			->where('(' . $where . ')' . $checkin);
		$this->_db->setQuery($query);

		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}
}
