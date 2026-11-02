<?php
/**
 * @version		$Id: article.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
require_once(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables/marker.php');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Supports a modal article picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldModal_Hotspot extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Modal_Hotspot';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$modalId = 'Hotspot_' . $this->id;

		$db	= JFactory::getDBO();
		$db->setQuery(
			'SELECT title' .
			' FROM #__hotspots_marker' .
			' WHERE id = '.(int) $this->value
		);
		$title = $db->loadResult();

		if (empty($title)) {
			$title = JText::_('COM_HOTSPOTS_SELECT_HOTSPOT');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		//		$script = array();
		$script[] = '	function selectHotspot(id, title, object) {';
		$script[] = '		document.getElementById("'.$this->id.'_id").value = id;';
		$script[] = '		document.getElementById("'.$this->id.'_name").value = title;';
		$script[] = '        jQuery("#ModalSelect' . $modalId . '").modal("hide");';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		
		$link = 'index.php?option=com_hotspots&amp;view=hotspots&layout=element&amp;tmpl=component&amp;object=' . $this->name;
		
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
		$html[] = '<button class="btn btn-primary" data-toggle="modal" data-target="#ModalSelect' . $modalId . '" data-bs-toggle="modal" data-bs-target="#ModalSelect' . $modalId . '"
 						type="button">'.JText::_('COM_HOTSPOTS_SELECT_HOTSPOT').'</button>';
		$html[] = '</div>';

		$html[] = HTMLHelper::_(
			'bootstrap.renderModal',
			'ModalSelect' . $modalId,
			array(
				'title'       => JText::_('COM_HOTSPOTS_SELECT_HOTSPOT'),
				'url'         => $link,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => 70,
				'modalWidth'  => 80,
				'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
					. Text::_('COM_HOTSPOTS_SELECT_HOTSPOT') . '</button>',
			)
		);
		
		// The active article id field.
		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return  implode("\n", $html);
	}
}
