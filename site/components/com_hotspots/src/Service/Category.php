<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2013
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Site\Service;

use Joomla\CMS\Categories\Categories;

\defined('_JEXEC') or die;

/**
 * Hotspots categories (Categories::getInstance('Hotspots')).
 */
class Category extends Categories
{
    public function __construct($options = [])
    {
        $options['table']     = '#__hotspots_marker';
        $options['extension'] = 'com_hotspots';
        $options['statefield'] = 'state';

        parent::__construct($options);
    }
}
