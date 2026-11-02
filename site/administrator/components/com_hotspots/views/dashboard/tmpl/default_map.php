<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       30.07.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

function prepareCategories() {
	$categories = HotspotsHelperUtils::getCategoriesInfo();
    $categoriesJson = array();
    foreach ($categories as $category) {
        $categoriesJson[$category->id] = $category;
    }

    return json_encode($categoriesJson);
}

function getHotspots()
{
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);

	$query->select(
		'm.id AS hotspots_id, m.gmlat as latitude,
							m.gmlng as longitude, m.*'
	)
		->from('#__hotspots_marker as m')
		->leftJoin('#__categories AS c ON m.catid = c.id')
		->order('m.created DESC');
	$db->setQuery($query, 0, 25);

	return $db->loadObjectList();
}

function prepareHotspots($hotspots)
{
	$json = array();

	foreach ($hotspots as $hotspot)
	{
		$json[] = HotspotsHelperUtils::prepareHotspot($hotspot);
	}

	return json_encode($json);
}

$doc = \Joomla\CMS\Factory::getDocument();

JHtml::script('JTOOLBAR_EDIT');

$apiKey = HotspotsHelperSettings::get('api_key', "");

if ($apiKey) {

	$doc->addScriptDeclaration(
		"
                var categories = " . prepareCategories() . ";
                var latesthotspots = " . prepareHotspots(getHotspots()) . ";
                
                function initMap() {
                    var startPos = {lat: 10, lng: 10};
                    
                    var map = new google.maps.Map(document.getElementById('map_canvas'), {
                      zoom: 4,
                      center: startPos
                    });
                    
                    var bounds = new google.maps.LatLngBounds()
                    latesthotspots.map(hotspot => {
                      const position = {lat: parseFloat(hotspot.latitude), lng: parseFloat(hotspot.longitude)}
                    
                      bounds.extend(position)
                      const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: hotspot.title,
                        icon: categories[hotspot.catid] && categories[hotspot.catid].cat_icon
                      });
                      
                      marker.addListener('click', function() {
                        window.location.href = hotspot.link;
                      });
                })
                
                map.fitBounds(bounds)

  }
  "
	);
    $doc->addScript(HotspotsHelperUtils::getGmapsUrl("initMap"), array(), array('defer' => true, 'async' => "async"));

}


?>

<?php if($apiKey) : ?>

<div class="mod_hotspots">
	<div style="height: 300px;">

		<div id="map_canvas" class="map_canvas"
		     style="height:300px;"></div>

	</div>
</div>
<?php else: ?>
<div class="alert alert-error"><?php echo JText::sprintf("COM_HOTSPOTS_YOU_NEED_API_KEY", "https://compojoom.com/documentation/hotspots?view=book&page=142:API%20Key"); ?></div>
<?php endif; ?>
