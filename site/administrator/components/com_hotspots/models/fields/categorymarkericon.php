<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       04.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * JFormFieldCategorymarkerimage
 *
 * @since  3.0
 */
class JFormFieldCategorymarkericon extends JFormField
{
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'categorymarkericon';

	/**
	 * Constructor
	 *
	 * @param   JForm  $form  - the form
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		// Add some css
		$document = JFactory::getDocument();
		$css = JURI::root() . '/media/com_hotspots/css/fields/markerImage.css';
		JHTML::_('stylesheet', 'media/com_hotspots/css/hotspots-backend.css');
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
		JHtml::_('jquery.framework');

		JHTML::_('script', 'media/com_hotspots/js/lazy/jquery.lazy.js');
		JHTML::_('script', 'media/com_hotspots/backend/js/category.js');

		//        some output
		$html = array();
		$path = JPATH_ROOT . '/media/com_hotspots/images/categories/sample';
		$exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html', 'blank.gif');
		$sampleIcons = JFolder::files($path, '.', false, false, $exclude);

		$selected = 0;
		$image = '';

		if ($this->value)
		{
            $value = $this->value;

            // if saving fails, then the category plugin is not triggered and the value that comes from the post
            // has [sample] in it instead of the real address
            if (strstr($this->value, '[sample]'))
            {
                $value = str_replace('[sample]', '/media/com_hotspots/images/categories/', $value);
            }

            $selected = 1;
			$image = JURI::root() . $value;
		}

		$options = array(
			JHTML::_('select.option', '', JText::_('COM_HOTSPOTS_SELECT')),
			JHTML::_('select.option', 'new', JText::_('COM_HOTSPOTS_UPLOAD_NEW_IMAGE')),
			JHTML::_('select.option', 'sample', JText::_('COM_HOTSPOTS_SELECT_SAMPLE_IMAGE'))
		);

		$html[] = '<div id="category-icon">';

		if ($image)
		{
			$html[] = '<img src="' . $image . '" />';
		}

		$html[] = '</div>';

		$html[] = JHTML::_('select.genericlist', $options, 'select-icon', null, 'value', 'text', $selected);

		$html[] = '<div class="clr"></div>';
		$html[] = '<div id="select-sample-image" style="display: none">';

		foreach ($sampleIcons as $icon)
		{
			$title = explode('.', $icon);

			$path = JURI::root() . 'media/com_hotspots/images/categories/sample/' . $icon;
			$html[] = '<div>';
			$html[] = '<img class="lazy" src="' . JURI::root() . 'media/com_hotspots/images/categories/sample/blank.gif"
             data-src="' . $path . '" title="' . $title[0] . '"/>';
			$html[] = '<span data-id="' . $icon . '"> ' . $title[0] . '</span>';
			$html[] = '</div>';
		}

		$html[] = '</div>';
		$html[] = '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '" />';

		return implode("\n", $html);
	}
}
