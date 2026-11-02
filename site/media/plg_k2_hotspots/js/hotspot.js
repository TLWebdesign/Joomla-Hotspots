/**
 * Created by DanielDimitrov on 14.07.2014.
 */
var map;
function initializeHotspotMap(center, hotspot) {
	var marker, self=this;
	var lat = hotspot.gmlat;
	var lng = hotspot.gmlng;

	if(!lat && !lat) {
		lat = center.lat;
		lng = center.lng;
	}

	var myLatlng = new google.maps.LatLng(lat,lng);
	var geocoder  = new google.maps.Geocoder();


	var mapOptions = {
		zoom: center.zoom,
		center: myLatlng
	};

	var map = new google.maps.Map(document.getElementById('hotspot-map'),
		mapOptions);

	formActions();

	createMarker(myLatlng);

	google.maps.event.addListener(map, 'click', function (event) {
		createMarker(event.latLng);
		// update the form address and form coordiantes
		updateFormCoordinates(event.latLng);
		if (isSticky()) {
			updateFormAddress(event.latLng);
		}
	});

	function createMarker(position) {
		if(marker) {
			marker.setMap(null);
		}
		marker = new google.maps.Marker({
			position: position,
			map: map,
			draggable: true
		});

		google.maps.event.addListener(marker, 'drag', function() {
			updateFormCoordinates(this.getPosition());
		});

		google.maps.event.addListener(marker, 'dragend', function() {
			if (isSticky()) {
				updateFormAddress(this.getPosition());
			}
		});
	}

	function updateMarkerPosition () {
		var street = jQuery('#hs-street').prop('value');
		var zip = jQuery('#hs-plz').prop('value');
		var town = jQuery('#hs-town').prop('value');
		var country = jQuery('#hs-country').prop('value');

		var address = street + ', ' + zip + ',' + town + ', ' + country;
		geocoder.geocode({
			address:address
		}, function (results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var location = results[0].geometry.location;
				marker.setPosition(location);
				map.setCenter(location);
				if (isSticky()) {
					updateFormCoordinates(location);
				}
			}
		});
	}

	function formActions () {
		if (navigator.geolocation != 'undefined') {
			jQuery('#hotspots-geolocation').on('click', self.geolocation);
		} else {
			jQuery('#hotspots-geolocation').css('display', 'none');
		}

		var addresses = ['#hs-street', '#hs-plz', '#hs-town', '#hs-country'];
		var keyupTimer;
		addresses.each(function (addressElement) {
			jQuery(addressElement).on('keyup', function () {
				console.log('keyup');
				if (isSticky()) {
					clearTimeout(keyupTimer);
					keyupTimer = setTimeout(function () {
						updateMarkerPosition();
					}, 1000);
				}
			});
		});

		var coordinatesFields = ['#hotspot_lat', '#hotspot_lng'];
		coordinatesFields.each(function (field) {
			jQuery(field).on('keyup', function () {
				clearTimeout(keyupTimer);
				keyupTimer = setTimeout(function () {
					var lat = jQuery('#hotspot_lat').prop('value');
					var lng = jQuery('#hotspot_lng').prop('value');
					var latlng = new google.maps.LatLng(lat, lng);
					if (isSticky()) {
						updateFormAddress(latlng);
					}
					updateMarkerFromCoordinates(latlng);
				}, 1000);
			});
		});

	}

	function geolocation () {
		navigator.geolocation.getCurrentPosition(function (position) {
			jQuery('#hotspots-geolocation-info').set('html', '');
			var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
			if (isSticky()) {
				updateFormAddress(latlng);
			}
			updateMarkerFromCoordinates(latlng);
			updateFormCoordinates(latlng);
		}, function () {
			jQuery('#hotspots-geolocation-info').set('html', 'Your browser does not support geolocation');
		});
	}

	function isSticky() {
		return jQuery('#hs-sticky').prop('checked');
	}

	function updateFormCoordinates (position) {
		jQuery('#hotspot_lat').prop('value', position.lat());
		jQuery('#hotspot_lng').prop('value', position.lng());
	}

	function updateFormAddress (latlng) {
		geocoder.geocode({
			'latLng':latlng
		}, function (results, status) {
			var streetNumber = '';
			var streetName = '';
			var country = '';
			var postalCode = '';
			var city = '';
			if (status == google.maps.GeocoderStatus.OK) {
				results[0].address_components.each(function (el) {

					el.types.each(function (type) {
						if (type == 'street_number') {
							streetNumber = el.long_name;
						}

						if (type == 'route') {
							streetName = el.long_name;
						}

						if (type == 'country') {
							country = el.long_name;
						}

						if (type == 'postal_code') {
							postalCode = el.long_name;
						}

						if (type == 'locality') {
							city = el.long_name;
						}
					})
				});

				jQuery('#hs-street').prop('value', streetName + ' ' + streetNumber);

				jQuery('#hs-plz').prop('value', postalCode);
				jQuery('#hs-town').prop('value', city);
				jQuery('#hs-country').prop('value', country);

			}
		});
	}
}


