<?php
/**
 * @version        $Id$
 * @copyright      Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package        Joomla.Administrator
 * @subpackage     com_categories
 * @since          1.6
 */
class JFormFieldHotspotsLink extends JFormField
{
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'Hotspotslink';

	/**
	 * Create the form input
	 *
	 * @return string
	 */
	protected function getInput()
	{
		// Load the active plugins and create the dropdown
		$plugins = JPluginHelper::getPlugin('hotspotslinks');
		JPluginHelper::importPlugin('hotspotslinks');

		$options[] = JHtml::_('select.option', 0, JText::_('COM_HOTSPOTS'));

		foreach ($plugins as $value)
		{
			$options[] = JHtml::_('select.option', $value->name, JText::_('PLG_HOTSPOTSLINKS_COM_' . strtoupper($value->name)));
		}

		// Add some javascript to the head
		$document = JFactory::getDocument();
		Jhtml::_('jquery.framework');
		$js = JURI::root() . '/media/com_hotspots/js/fields/hotspotsLink.js';
		$document->addScript($js);
		$domready = 'jQuery(document).ready(function() {
            var options = {fieldId:"#' . $this->id . '",link_to_id:"#' . $this->id . '_id' . '"};
            hotspotsLink(options);

        })';
		$document->addScriptDeclaration($domready);

		//      some output
		$html = array();
		$selected = 0;

		if ($this->value)
		{
			$selected = $this->value;
		}

		$html[] = JHTML::_(
			'select.genericlist', $options, 'jform[params][link_to]', array('class' => 'form-control'),
			'value', 'text', $selected, $this->id
		);

		$link_id = isset($this->form->getValue('params')->link_to_id) ?
			$this->form->getValue('params')->link_to_id : '';

		$html[] = '<div class="clr"></div>';

		$display = ($selected !== 0) ? 'block' : 'none';
		$html[] = '<div id="link_to_plugins" style="display:' . $display . '">';
		$html[] = '<input type="text" id="' . $this->id . '_id'
			. '" class="form-control" name="jform[params][link_to_id]" value="' . $link_id . '" placeholder="' . JText::_('COM_HOTSPOTS_ENTER_ID') . '"/>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
