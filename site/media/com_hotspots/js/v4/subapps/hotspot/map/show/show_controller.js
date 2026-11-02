/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("HotspotMapApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	var Controller = Marionette.Controller.extend({
		show: function () {
			var	view = HotspotsManager.request("map:object"),
				marker = HotspotsManager.request('marker:entity'),
				mapModel = HotspotsManager.request('map:entity');

			mapModel.set('center', new google.maps.LatLng(marker.get('lat'), marker.get('lng')));
			mapModel.set('zoom', HotspotsConfig.mapStartZoomSingle);


			['trafficLayer', 'transitLayer','bicyclingLayer'].forEach(function(layer) {
				view.setLayer(layer);
			});

			var region = new Marionette.Region( {el: "#js-hs-main-app"});
			// Now show the map in the DOM
			region.show(view);
		},

		zoomInOnModel: function(model) {
			var view = HotspotsManager.request("map:object");
			var point = {
				lat: model.get('lat'),
				lng: model.get('lng')
			};
			view.zoomOnPoint(point, 15);
		}
	});

	Show.Controller = new Controller();
});

