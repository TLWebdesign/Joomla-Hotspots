<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('params');
foreach ($fieldSets as $name => $fieldSet) :
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	endif;
	?>

		<?php foreach ($this->form->getFieldset($name) as $field) : ?>
		<div class="form-group">
			<?php echo $field->label; ?>
			<div class="col-sm-12">
				<?php echo $field->input; ?>
			</div>
		</div>
		<?php endforeach; ?>

<?php endforeach; ?>
