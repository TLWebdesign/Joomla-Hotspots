<?php
/***************************************************************
*  Copyright notice
*
*  Copyright 2011 Daniel Dimitrov. (http://compojoom.com)
*  All rights reserved
*
*  This script is part of the Hotspots project. The Hotspots project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_hotspots/tables/marker.php');

class JElementHotspot extends JElement {

	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Hotspot';

	public function fetchElement($name, $value, &$node, $control_name) {
		$appl = JFactory::getApplication();

		$db = & JFactory::getDBO();
		$doc = & JFactory::getDocument();
		$template = $appl->getTemplate();
		$fieldName = $control_name . '[' . $name . ']';
		$hotspot = JTable::getInstance('marker', 'Table');
		if ($value) {
			$hotspot->load($value);
		} else {
			$hotspot->title = JText::_('COM_HOTSPOTS_SELECT_HOTSPOT');
		}

		$js = "
		
		function selectHotspot(id, name, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = name;
			document.getElementById('sbox-window').close();
		}";
		$doc->addScriptDeclaration($js);

		$link = 'index.php?option=com_hotspots&amp;controller=all&layout=element&amp;tmpl=component&amp;object=' . $name;

		JHTML::_('behavior.modal', 'a.modal');
		// This is ugly, but it is the only way to override the menu generated javascript function
		$html = '<script language="javascript" type="text/javascript">
				<!--
				function submitbutton(pressbutton) {
							var form = document.adminForm;
							var type = form.type.value;

							if (pressbutton == "cancelItem") {
								submitform( pressbutton );
								return;
							}
							if ( trim( form.name.value ) == "" ){
								alert( "' .  JText::_( 'COM_HOTSPOTS_ITEM_MUST_HAVE_A_TITLE', true ) . '");
							} else if( document.getElementById("id_id").value == 0 ){
								alert( "' . JText::_('COM_HOTSPOTS_PLEASE_SELECT_HOTSPOT', true ) . '");
							} else {
								submitform( pressbutton );
							}
						}
				//-->
				</script>';
		$html .= "\n" . '<div style="float: left;"><input style="background: #ffffff;" type="text" id="' . $name . '_name" value="' . htmlspecialchars($hotspot->title, ENT_QUOTES, 'UTF-8') . '" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="' . JText::_('COM_HOTSPOTS_SELECT_HOTSPOT') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">' . JText::_('COM_HOTSPOTS_SELECT') . '</a></div></div>' . "\n";
		$html .= "\n" . '<input type="hidden" id="' . $name . '_id" name="' . $fieldName . '" value="' . (int) $value . '" />';

		return $html;
	}
}
