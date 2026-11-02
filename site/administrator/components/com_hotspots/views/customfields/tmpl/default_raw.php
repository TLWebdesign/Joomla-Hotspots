<?php
/**
 * @package    Com_Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       27.01.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

?>

<?php if($this->form->getGroup('customfields')) : ?>
	<?php foreach($this->form->getGroup('customfields') as $custom) : ?>
		<div class="form-group">
			<?php $custom->labelclass .= ' col-sm-2 compojoom-control-label'; ?>
			<?php echo $custom->label; ?>
			<div class="col-sm-10">
				<?php echo $custom->input; ?>
			</div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>