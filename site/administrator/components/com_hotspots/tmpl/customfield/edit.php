<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2026 TLWebdesign. Based on Hotspots by compojoom.com, (C) 2008-2015
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

\defined('_JEXEC') or die;

/** @var \TLWebdesign\Component\Hotspots\Administrator\View\Customfield\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useScript('keepalive')->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_hotspots&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="customfield-form" class="form-validate">
    <div class="main-card p-4">
        <?php echo $this->form->renderFieldset('details'); ?>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
