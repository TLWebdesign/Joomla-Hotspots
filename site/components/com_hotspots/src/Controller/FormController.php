<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Controller;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

/**
 * Opens the frontend marker form (form.add, form.edit). Saving goes through hotspot.save.
 */
class FormController extends HotspotController
{
    public function save($key = null, $urlVar = 'a_id')
    {
        throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
    }

    public function edit($key = null, $urlVar = 'a_id')
    {
        // Links use "id": hold that id for the form.
        if (!$this->input->getInt($urlVar) && ($id = $this->input->getInt('id'))) {
            $this->input->set($urlVar, $id);
        }

        return parent::edit('id', $urlVar);
    }

    public function delete(): void
    {
        throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
    }

    public function publish(): void
    {
        throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
    }
}
