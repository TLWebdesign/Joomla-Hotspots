<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

\defined('_JEXEC') or die;

/**
 * Custom field definitions (#__hotspots_customfields). The categories of a field are in
 * #__hotspots_customfields_cats, saved by the CustomfieldModel.
 */
class CustomfieldTable extends Table
{
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->setColumnAlias('published', 'enabled');

        parent::__construct('#__hotspots_customfields', 'id', $db, $dispatcher);
    }

    public function check()
    {
        $this->title = trim((string) $this->title);

        if ($this->title === '') {
            $this->setError(Text::_('COM_HOTSPOTS_CUSTOM_FIELDS_ERR_TITLE'));

            return false;
        }

        $this->slug = ApplicationHelper::stringURLSafe(trim((string) $this->slug) !== '' ? $this->slug : $this->title);
        $this->slug = str_replace('-', '_', $this->slug);

        if ($this->slug === '') {
            $this->slug = 'field_' . Factory::getDate()->format('YmdHis');
        }

        $this->component = (string) ($this->component ?: 'com_hotspots.hotspot');

        // Slugs are the keys in the marker's customfields JSON, so they must be unique per component.
        $db    = $this->getDatabase();
        $id    = (int) $this->id;
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__hotspots_customfields'))
            ->where($db->quoteName('slug') . ' = :slug')
            ->where($db->quoteName('component') . ' = :component')
            ->where($db->quoteName('id') . ' != :id')
            ->bind(':slug', $this->slug)
            ->bind(':component', $this->component)
            ->bind(':id', $id, ParameterType::INTEGER);

        if ((int) $db->setQuery($query)->loadResult() > 0) {
            $this->setError(Text::_('COM_HOTSPOTS_CUSTOM_FIELDS_ERR_SLUG_EXISTS'));

            return false;
        }

        $this->show       = $this->show === 'category' ? 'category' : 'all';
        $this->type        = preg_replace('/[^a-z0-9_]/i', '', (string) $this->type) ?: 'text';
        $this->options     = (string) ($this->options ?? '');
        $this->default     = (string) ($this->default ?? '');
        $this->allow_empty = (int) $this->allow_empty;
        $this->params      = (string) ($this->params ?? '');

        return parent::check();
    }

    public function store($updateNulls = true)
    {
        $date = Factory::getDate()->toSql();
        $user = Factory::getApplication()->getIdentity();

        if ((int) $this->id) {
            $this->modified_on = $date;
            $this->modified_by = (int) $user?->id;
        } else {
            $this->created_on = $date;
            $this->created_by = (int) $user?->id;
        }

        return parent::store($updateNulls);
    }

    public function delete($pk = null)
    {
        $id = (int) ($pk ?? $this->id);

        if (!parent::delete($pk)) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__hotspots_customfields_cats'))
            ->where($db->quoteName('customfield_id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        return true;
    }
}
