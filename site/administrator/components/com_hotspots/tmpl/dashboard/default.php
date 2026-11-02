<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2023 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Dashboard\HtmlView $this */

$filter = ['all' => '', 'published' => '1', 'unpublished' => '0'];
$date   = $this->version['date'] !== '' ? HTMLHelper::_('date', $this->version['date'], Text::_('DATE_FORMAT_LC3')) : '';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <?php echo LayoutHelper::render('hotspots.map.map', ['hotspots' => $this->markers, 'params' => ['map_height' => 400]], JPATH_ADMINISTRATOR . '/components/com_hotspots/layouts'); ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <h2 class="card-header h5"><?php echo Text::_('COM_HOTSPOTS_STATS'); ?></h2>
            <table class="table mb-0">
                <tbody>
                <?php foreach ($this->stats as $key => $value) : ?>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_HOTSPOTS_STATS_' . strtoupper($key)); ?></th>
                        <td>
                            <a href="<?php echo Route::_('index.php?option=com_hotspots&view=hotspots&filter[published]=' . $filter[$key]); ?>">
                                <?php echo (int) $value; ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card mb-3">
            <h2 class="card-header h5"><?php echo Text::_('COM_HOTSPOTS_VERSION'); ?></h2>
            <div class="card-body">
                <p class="mb-1"><strong>Hotspots <?php echo $this->escape($this->version['version']); ?></strong> <?php echo $this->escape($date); ?></p>
                <p class="small mb-1">Copyright &copy; 2008&ndash;2023 Daniel Dimitrov / compojoom.com, &copy; 2026 TLWebdesign.</p>
                <p class="small mb-0">
                    Hotspots is free software released under the
                    <a href="https://www.gnu.org/licenses/gpl.html" target="_blank" rel="noopener">GNU General Public License</a>,
                    version 2 or (at your option) any later version.
                </p>
            </div>
        </div>
    </div>
</div>
