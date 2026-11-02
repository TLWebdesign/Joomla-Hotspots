<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Controller;

use TLWebdesign\Component\Hotspots\Administrator\Controller\MultimediaController as AdministratorMultimediaController;

\defined('_JEXEC') or die;

/**
 * Image upload endpoint for the frontend marker form; the same as in the administrator.
 * Permissions (core.multimedia.*) are checked in the model.
 */
class MultimediaController extends AdministratorMultimediaController
{
}
