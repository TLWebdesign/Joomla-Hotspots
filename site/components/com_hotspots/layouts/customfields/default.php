<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 * The custom field values of a marker.
 *
 * @var  array  $displayData  customfieldsItem: slug => value (the marker's customfields JSON as array),
 *                            customfieldsConfig: slug => field (CustomfieldsHelper::getFields())
 */

use Joomla\CMS\Language\Text;
use TLWebdesign\Component\Hotspots\Administrator\Helper\CustomfieldsHelper;

\defined('_JEXEC') or die;

$values = (array) ($displayData['customfieldsItem'] ?? []);
$config = (array) ($displayData['customfieldsConfig'] ?? []);

if (!$values || !$config) {
    return;
}
?>
<dl class="hs-customfields dl-horizontal">
    <?php foreach ($config as $slug => $field) : ?>
        <?php
        $value = $values[$slug] ?? null;

        if ($value === null || $value === '' || $value === []) {
            continue;
        }
        ?>
        <dt><?php echo htmlspecialchars(Text::_($field->title), ENT_QUOTES, 'UTF-8'); ?></dt>
        <dd><?php echo CustomfieldsHelper::render($field, $value); ?></dd>
    <?php endforeach; ?>
</dl>
