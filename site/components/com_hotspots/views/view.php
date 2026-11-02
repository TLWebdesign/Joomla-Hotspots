<?php
/***************************************************************
*
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
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

class HotspotsView extends JViewLegacy {
	
	/**
	 * add an init.js file from the media folder 
	 * or from the template folder - only if one exists
	 */
	public function includeJavascript(){
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');
		
		$fileToFind = 'init.js';
		
		$path = JPATH_BASE . '/media/com_hotspots/js/views/' . $this->getName() . '/init.js';
		$uri = 'media/com_hotspots/js/views/' . $this->getName() . '/init.js';
		
		if($jsfile = JPath::find($this->_path['template'], $fileToFind)) {
			$path = $jsfile;
			
			if ($pos = strpos($jsfile, 'templates')) {
				$uri = str_replace('\\', '/', substr($jsfile,$pos));;
			}
		}
		
		if(JFile::exists($path)) {
			$document = JFactory::getDocument();
			
			$script = JFile::read($path);
			$document->addScriptDeclaration($script);
		}
	}
	
	public function includeMootoolsMore() {
		JHtmlBehavior::framework(true);
	}
	
	public function setMootoolsLocale() {
		$document = JFactory::getDocument();
		$language = JFactory::getLanguage();

		$mootoolsLocale = "Locale.use('".$language->getTag()."')";

		$locale = "window.addEvent('domready', function() {
			$mootoolsLocale
		});";
		
		$document->addScriptDeclaration($locale);
	}
}

