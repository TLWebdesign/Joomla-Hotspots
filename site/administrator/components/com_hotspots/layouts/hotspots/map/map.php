<?php
/**
 * @package    com_hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       06.05.2015
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_LIBRARIES . '/compojoom/include.php';

// Load language

CompojoomLanguage::load('com_hotspots', JPATH_ADMINISTRATOR);
CompojoomLanguage::load('com_hotspots.sys', JPATH_ADMINISTRATOR);
CompojoomLanguage::load('com_hotspots', JPATH_SITE);

$document = JFactory::getDocument();

HotspotsHelperUtils::getJsLocalization();
CompojoomHtmlBehavior::jquery();

if (HotspotsHelperSettings::get('emulate_bootstrap', 1))
{
    JHTML::_('stylesheet', 'media/lib_compojoom/css/bootstrap-232.css');
}

JHTML::_('stylesheet', 'media/com_hotspots/css/hotspot.css');

JHTML::stylesheet('media/lib_compojoom/third/font-awesome/css/font-awesome.min.css');
;

$hotspots = $displayData['hotspots'];
$params       = $displayData['params'];
$width        = $params->get('map_width', '') ? $params->get('map_width') . 'px' : '100%';
$width        = $params->get('map_height', '') ? $params->get('map_height') . 'px' : '300px';

$document->addScript(HotspotsHelperUtils::getGmapsUrl(), array(), array('defer' => true, 'async' => "async"));

$apiKey = HotspotsHelperSettings::get('api_key', "");
JText::script('COM_HOTSPOTS_READ_MORE');


if ($apiKey) {

    $document->addScriptDeclaration(
        "
                var latesthotspots = " . json_encode($hotspots) . ";
                var markers = {}
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
                        content: hotspot.description_small,
                        link: hotspot.link,
                        map: map,
                        title: hotspot.title,
                        icon: hotspot.icon
                      });
                      
                      markers[hotspot.hotspots_id] = marker
                      
                      marker.addListener('click', function() {
                        window.location.href = hotspot.link;
                      });
                      
                     
                })
                
                 window.addEventListener('onHotspotClick', function(e) {
                 var id = e.detail.id
                 var clickedMarker = markers[id]
                        map.setZoom(17);
                        map.panTo(clickedMarker.position);
                        
                        var contentString  = '<h2>'+clickedMarker.title + '</h2>' + clickedMarker.content + 
                        '<a href=\"' + clickedMarker.link + '\">' + Joomla.JText._('COM_HOTSPOTS_READ_MORE') + '</a>'
                          const infowindow = new google.maps.InfoWindow({
                            content: contentString,
                          });

                        infowindow.open(map, clickedMarker);
                      })
                      
                map.fitBounds(bounds)
  }
                  jQuery(document).ready(function() {
                    initMap()
                })
  "
    );
}
?>
<div style="height: 300px;">

		<div id="map_canvas" class="map_canvas"
		     style="height:300px;"></div>

	</div>
