<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Table;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

\defined('_JEXEC') or die;

/**
 * Images attached to markers (#__compojoom_multimedia, type_alias com_hotspots.hotspot).
 */
class MultimediaTable extends Table
{
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__compojoom_multimedia', 'compojoom_multimedia_id', $db, $dispatcher);
    }

    public function check()
    {
        $this->title       = (string) ($this->title ?? '');
        $this->description = (string) ($this->description ?? '');
        $this->params      = (string) ($this->params ?? '');
        $this->mime_type   = (string) ($this->mime_type ?: 'application/octet-stream');

        return parent::check();
    }
}
