<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       09.12.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class PlgContentHotspotsanywhere
 *
 * @since  3.0
 */
class PlgContentHotspotsanywhere extends JPlugin
{
	/**
	 * On content prepare event
	 *
	 * @param   string  $context   - the context
	 * @param   object  &$article  - the article object
	 * @param   object  &$params   - the params
	 * @param   int     $page      - the page
	 *
	 * @return bool
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer')
		{
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, 'hotspotsanywhere') === false)
		{
			return true;
		}

		$input = JFactory::getApplication()->input;
		$oldView = $input->getString('view');

		$field = 'text';

		// If we are on the featured page we don't have to execute the plugin if there isn't anything in the introtext
		if ($context == 'com_content.featured')
		{
			$field = 'introtext';
		}

		// Expression to search for (hotspots)
		$regex = '/{hotspotsanywhere+(.*?)}/i';


		$Itemid = 0;

		// Find all instances of plugin and put in $matches for hotspots
		// $matches[0] is full pattern match, $matches[1] contains the settings
		preg_match_all($regex, $article->$field, $matches, PREG_SET_ORDER);

		$hash = '';

		// No matches, skip this
		if (isset($matches[0][1]))
		{
			/**
			 * Syntax options
			 * 1. lat/lng/zoom | Itemid
			 * 2. Itemid
			 */
			$explode = explode('|', $matches[0][1]);

			// If we have more than 2 entries in the array, then we have lat/lng/zoom and itemid
			if (count($explode) > 1)
			{
				$Itemid = trim($explode[1]);
			}

			// Are we dealing with lat/lng/zoom or just Itemid?
			if (strstr($explode[0], '/'))
			{
				$hash = trim($explode[0]);
			}
			else
			{
				$Itemid = trim($explode[0]);
			}
		}

		// Set the itemid in the input. It will be fetched up by Hotspots
		$input->set('HotspotsAnywhereMenuItem', $Itemid);

		// Path to the users component
		$com = JPATH_SITE . '/components/com_hotspots';

		JLoader::register('HotspotsController', $com . '/controller.php');

		require $com . '/hmvc.php';

		// We need to force the hotspots view here
		// Otherwise hotspots can't get the default start categories
		$input->set('view', 'hotspots');

		$config['base_path'] = $com;
		$config['view_path'] = $com . '/views';
		$cont = new HotspotsController($config);

		// Get the view and add the correct template path
		$view = $cont->getView('Hotspots', 'html');
		$view->addTemplatePath($com . '/views/hotspots/tmpl');

		ob_start();
		$view->display();
		$hotspotsAnywhere = ob_get_contents();
		ob_end_clean();

		$replace = $matches[0][0];

		// We need to replace | with \| -> | is a special sign in preg_replace, but we want to treat it like a character
		$article->text = preg_replace('$' . str_replace('|', '\|', $replace) . '$', $hotspotsAnywhere, $article->text, 1);

		if ($hash)
		{
			// Add the hash to the url
			$urlHash = "
			if(!window.location.hash) {
				window.location.hash = '" . trim($hash) . "'
			}
			";
			$document = JFactory::getDocument();
			$document->addScriptDeclaration($urlHash);
		}

		// Go back to the old value for view, we don't want to screw up other components
		$input->set('view', $oldView);

		return true;
	}
}
