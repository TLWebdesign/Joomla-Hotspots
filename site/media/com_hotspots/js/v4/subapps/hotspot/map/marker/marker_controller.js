/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("HotspotMapApp.Marker", function (Marker, HotspotsManager, Backbone, Marionette, $, _) {



	Marker.Controller = {
		places: false,
		render: false,
		addressMarker: '',

		draw: function (marker) {

			var map = HotspotsManager.request("map:object");
			var markerView = new Marker.MarkerView({
				model: marker,
				map: map.mapObj
			});

			markerView.render();
		},

		fetchMarkers: function () {
			var entities = HotspotsManager.request('hotspots:entities');
			entities.done(function (hotspots) {
				HotspotsManager.trigger('hotspots:fetchedData', hotspots);
			});
		},

		renderAddress: function(results, status) {
			var map = HotspotsManager.request("map:object");

			if (status == google.maps.GeocoderStatus.OK) {

				map.mapObj.setCenter(results[0].geometry.location);
				this.addressMarker = new google.maps.Marker({
					map: map.mapObj,
					position: results[0].geometry.location
				});
			}
		},

		clearAddressMarker: function(){
			this.addressMarker.setMap(null);
		}
	};

});

