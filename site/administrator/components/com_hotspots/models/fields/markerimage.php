<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       01.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.0
 */
class JFormFieldMarkerImage extends JFormField
{
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'Markerimage';

	/**
	 * Constructor
	 *
	 * @param   JForm  $form  - the form
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		//      add some css
		$document = JFactory::getDocument();
		$css = JURI::root() . '/media/com_hotspots/css/fields/markerImage.css';
		$document->addStyleSheet($css);
	}

	/**
	 * Creates the output
	 *
	 * @return string
	 */
	protected function getInput()
	{
		jimport('joomla.filesystem.folder');

		// Add some javascript to the head
		$document = JFactory::getDocument();
        // Add some javascript to the head
        JHtml::_('jquery.framework');

        JHTML::_('script', 'media/com_hotspots/js/lazy/jquery.lazy.js');
        JHTML::_('script', 'media/com_hotspots/js/fields/markerImage.js');

		$domready = 'jQuery(document).ready(function() {
            var options = {fieldId:"#' . $this->id . '"}
            initMarkerImage(options);

        })';

		$document->addScriptDeclaration($domready);

		//        some output
		$html = array();
		$path = JPATH_ROOT . '/media/com_hotspots/images/categories/sample';
		$exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html', 'blank.gif');
		$sampleIcons = JFolder::files($path, '.', false, false, $exclude);

		$selected = 0;
		$image = '';

		if ($this->value)
		{
			$selected = 1;
			$image = JURI::root() . 'media/com_hotspots/images/categories/' . $this->value;
		}

		$options = array(
			JHTML::_('select.option', '0', JText::_('COM_HOTSPOTS_SAME_MARKER_AS_CATEGORY')),
			JHTML::_('select.option', '1', JText::_('COM_HOTSPOTS_CUSTOM_MARKER'))
		);

		$html[] = '<div id="current-icon">';

		if ($image)
		{
			$html[] = '<img src="' . $image . '" />';
		}

		$html[] = '</div>';

		$html[] = JHTML::_('select.genericlist', $options, 'marker-image',  array('class' => 'form-control'), 'value', 'text', $selected);

		$html[] = '<div class="clr"></div>';
		$html[] = '<div id="sample-image">';

		foreach ($sampleIcons as $icon)
		{
			$title = explode('.', $icon);

			$path = JURI::root() . 'media/com_hotspots/images/categories/sample/' . $icon;
			$html[] = '<div>';
			$html[] = '<img src="' . JURI::root() . 'media/com_hotspots/images/categories/sample/blank.gif"
             data-src="' . $path . '" title="' . $title[0] . '" class="lazy"/>';
			$html[] = '<span data-id="' . $icon . '"> ' . $title[0] . '</span>';
			$html[] = '</div>';
		}

		$html[] = '</div>';

		$html[] = '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '" />';

		return implode("\n", $html);
	}
}
