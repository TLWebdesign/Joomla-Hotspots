/**
 * Simple Google map with markers for the layout hotspots.map.map.
 *
 * @copyright  (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
window.HotspotsSimpleMap = {
    init: function () {
        document.querySelectorAll('.hotspots-simple-map').forEach(function (element) {
            var markers = {};
            var data = JSON.parse(element.getAttribute('data-markers') || '[]');
            var map = new google.maps.Map(element, {zoom: 4, center: {lat: 10, lng: 10}});
            var bounds = new google.maps.LatLngBounds();
            var infoWindow = new google.maps.InfoWindow();

            data.forEach(function (hotspot) {
                var position = {lat: hotspot.lat, lng: hotspot.lng};
                var marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: hotspot.title,
                    icon: hotspot.icon || null
                });

                bounds.extend(position);
                markers[hotspot.id] = {marker: marker, hotspot: hotspot};

                if (hotspot.link) {
                    marker.addListener('click', function () {
                        window.location.href = hotspot.link;
                    });
                }
            });

            if (data.length > 1) {
                map.fitBounds(bounds);
            } else if (data.length === 1) {
                map.setCenter(bounds.getCenter());
                map.setZoom(15);
            }

            window.addEventListener('onHotspotClick', function (event) {
                var item = markers[event.detail.id];

                if (!item) {
                    return;
                }

                var content = document.createElement('div');
                var title = document.createElement('h2');
                title.textContent = item.hotspot.title;
                content.appendChild(title);

                var description = document.createElement('div');
                // The short description is HTML from the marker editor.
                description.innerHTML = item.hotspot.description;
                content.appendChild(description);

                if (item.hotspot.link) {
                    var link = document.createElement('a');
                    link.href = item.hotspot.link;
                    link.textContent = Joomla.Text._('COM_HOTSPOTS_READ_MORE');
                    content.appendChild(link);
                }

                map.setZoom(17);
                map.panTo(item.marker.getPosition());
                infoWindow.setContent(content);
                infoWindow.open(map, item.marker);
            });
        });
    }
};
