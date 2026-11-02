<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace TLWebdesign\Component\Hotspots\Administrator\Extension;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

\defined('_JEXEC') or die;

/**
 * Hotspots component: markers on a Google map, grouped in Joomla categories (extension com_hotspots).
 */
class HotspotsComponent extends MVCComponent implements BootableExtensionInterface, CategoryServiceInterface, RouterServiceInterface
{
    use CategoryServiceTrait;
    use HTMLRegistryAwareTrait;
    use RouterServiceTrait;

    public function boot(ContainerInterface $container): void
    {
    }

    /**
     * Used by the category manager to count the markers in a category.
     */
    protected function getTableNameForSection(?string $section = null)
    {
        return 'hotspots_marker';
    }

    protected function getStateColumnForSection(?string $section = null)
    {
        return 'state';
    }
}
