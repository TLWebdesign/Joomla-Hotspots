<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2011 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

\defined('_JEXEC') or die;

/**
 * A KML file shown as a layer on the map (#__hotspots_kmls).
 */
class KmlTable extends Table
{
    /**
     * @var  bool
     */
    protected $_supportNullValue = true;

    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__hotspots_kmls', 'hotspots_kml_id', $db, $dispatcher);

        $this->setColumnAlias('published', 'state');
    }

    public function check()
    {
        if (trim((string) $this->title) === '') {
            $this->setError(\Joomla\CMS\Language\Text::_('COM_HOTSPOTS_KML_FILE_TITLE'));

            return false;
        }

        // NOT NULL columns without a default.
        $this->description       ??= '';
        $this->original_filename ??= '';
        $this->mangled_filename  ??= '';
        $this->catid             = (int) $this->catid;

        if (empty($this->created)) {
            $this->created = null;
        }

        return parent::check();
    }

    public function store($updateNulls = true)
    {
        if (!$this->hotspots_kml_id) {
            if (empty($this->created)) {
                $this->created = Factory::getDate()->toSql();
            }

            if (empty($this->created_by)) {
                $this->created_by = (int) Factory::getApplication()->getIdentity()?->id;
            }
        }

        return parent::store($updateNulls);
    }
}
