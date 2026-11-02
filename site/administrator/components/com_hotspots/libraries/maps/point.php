<?php
/**
 * @author Daniel Dimitrov - compojoom.com
 * @date: 10.08.12
 *
 * @copyright  Copyright (C) 2008 - 2012 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Reprecents a geographical point on the map
 */
class HotspotsMapPoint
{
    public $x, $y;

    /**
     * @param $x - latitude
     * @param $y - longitude
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "({$this->x},{$this->y})";
    }
}