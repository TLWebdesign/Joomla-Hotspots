<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2014 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use TLWebdesign\Component\Hotspots\Site\Helper\MapHelper;

/** @var \TLWebdesign\Component\Hotspots\Site\View\Hotspots\HtmlView $this */
?>

<div class="hs-address small muted" itemprop="address" itemscope="" itemtype="https://schema.org/PostalAddress">
	{{address}}
</div>