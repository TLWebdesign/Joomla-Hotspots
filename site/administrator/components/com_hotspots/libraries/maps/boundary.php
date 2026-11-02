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
 * represents a geographical area
 */
class HotspotsMapBoundary
{
    public $x, $y, $width, $height;

    /**
     * @param $x
     * @param $y
     * @param $width
     * @param $height
     */
    public function __construct($x, $y, $width, $height)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "({$this->x},{$this->y},{$this->width},{$this->height})";
    }
}