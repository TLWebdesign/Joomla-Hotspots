/**
 * Created by DanielDimitrov on 24.03.2015.
 */
HotspotsManager.module("HotspotMapApp", function(HotspotMapApp, HotspotsManager, Backbone, Marionette, $, _) {

	var API = {
		show: function () {
			HotspotMapApp.Show.Controller.show();
		},
		markers: function(marker) {
			HotspotMapApp.Marker.Controller.draw(marker);
		}
		//markerZoomIn: function(model) {
		//	HotspotMapApp.Show.Controller.zoomInOnModel(model);
		//}
	};

	this.listenTo(HotspotsManager, "map:show", function() {
		API.show();
	});

	//this.listenTo(HotspotsManager, 'marker:zoomIn', function(model) {
	//	API.markerZoomIn(model);
	//});

	this.listenTo(HotspotsManager, 'initialize:after', function (options) {
		HotspotsManager.trigger('map:show');

		var marker = HotspotsManager.request('marker:entity');
		marker.set(HotspotsMarker);
		API.markers(marker);
	});
});