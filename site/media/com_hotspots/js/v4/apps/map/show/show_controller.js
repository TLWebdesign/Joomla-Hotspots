/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("MapApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	var Controller = Marionette.Controller.extend({
		tiles: [],
		/**
		 * Adds the google map to the DOM
		 * Also attaches events to the gmap for dragend and zoom_changed
		 */
		show: function () {
			var self = this,
				view = HotspotsManager.request("map:object");

			// Init the map
			google.maps.event.addListenerOnce(view.mapObj, 'tilesloaded', function () {
				// Init the small bounds
				self.lastCenterBounds = self.controlCenterBounds();

				// Let's load the first hotspots
				HotspotsManager.trigger('map:init', view.mapObj);

				// On zoom always trigger changed position
				google.maps.event.addListener(view.mapObj, 'zoom_changed', function () {
					HotspotsManager.trigger('map:changedposition', view.mapObj);
				});

				// Change only if the user move more than 25%
				google.maps.event.addListener(view.mapObj, 'center_changed', function () {
					self.changePosition();
				});

				view.weather();
			});

			google.maps.event.addListener(view.mapObj, 'mousemove', function (mev) {
				var TILE_SIZE = 256,
					proj = view.mapObj.getProjection(),
					numTiles = 1 << view.mapObj.getZoom(),
					worldCoordinate = proj.fromLatLngToPoint(mev.latLng),
					pixelCoordinate = new google.maps.Point(
						worldCoordinate.x * numTiles,
						worldCoordinate.y * numTiles
					),
					tileCoordinate = new google.maps.Point(
						Math.floor(pixelCoordinate.x / TILE_SIZE),
						Math.floor(pixelCoordinate.y / TILE_SIZE)
					),
					coord = tileCoordinate.x + "," + tileCoordinate.y + ',' + view.mapObj.getZoom();

				// Trigger an event only if we haven't been over this tile
				if (!self.tiles[coord]) {
					HotspotsManager.trigger('tiles:mousemove', coord, tileCoordinate);
					self.tiles[coord] = true;
				}
			});

			['trafficLayer', 'transitLayer','bicyclingLayer'].forEach(function(layer) {
				view.setLayer(layer);
			});

			// Now show the map in the DOM
			HotspotsManager.mapRegion.show(view);
		},

		/**
		 * Determines if we need to trigger the changedposition event or not
		 * We check the current map bounds with the last map bounds
		 *
		 * If the last map bounds are outside of the current map bounds
		 * We fire the map:changedposition event
		 */
		changePosition: function() {
			var self = this,
				view = HotspotsManager.request("map:object"),
				// get the normal bounds
				gmbounds = view.mapObj.getBounds(),
				center = view.mapObj.getCenter();

			// check if the smaller bounds are outside the normal bounds
			if (!(gmbounds.contains(self.lastCenterBounds.getNorthEast())
					&& gmbounds.contains(self.lastCenterBounds.getSouthWest()))
				) {
				self.lastCenterBounds = self.controlCenterBounds();
				HotspotsManager.trigger('map:changedposition', view.mapObj);
			}
		},

		/**
		 * We create a smaller bound than the current view.
		 * The idea is: if the user doesn't move too much, then there is no need to
		 * refresh the data from the server all the time. That is why we need to get
		 * the smaller bounds here
		 *
		 * @returns {google.maps.LatLngBounds}
		 */
		controlCenterBounds: function () {
			var view = HotspotsManager.request("map:object"),
				center = view.mapObj.getCenter(),
				b = view.mapObj.getBounds(),
				x = (b.getNorthEast().lat() - center.lat()) / 1.5,
				y = (b.getNorthEast().lng() - center.lng()) / 1.5,
				east = center.lat() + x,
				north = center.lng() + y,
				west = center.lat() - x,
				south = center.lng() - y;

			return new google.maps.LatLngBounds(new google.maps.LatLng(west, south), new google.maps.LatLng(east, north));
		},

		zoomInOnModel: function(model) {
			var view = HotspotsManager.request("map:object");
			var point = {
				lat: model.get('lat'),
				lng: model.get('lng')
			};
			view.zoomOnPoint(point, 15);
		},

		streetView: function(pano, close) {
			var view = HotspotsManager.request("map:object");
			view.streetView(pano, close);
		},

		kmlLayer: function(kmls) {
			var view = HotspotsManager.request("map:object");
			view.showKmlLayers(kmls);
		},
		fitBounds: function(bounds) {
			var view = HotspotsManager.request("map:object");
			view.mapObj.fitBounds(bounds);
		},
		resize: function() {
			var view = HotspotsManager.request("map:object");
			view.resize();
		},
		setWidthRestriction: function(type, menuView) {
			var	mapView = HotspotsManager.request("map:object");
			mapView.setWidthRestriction(type ? menuView.$el.width() : 0 );
		}
	});

	Show.Controller = new Controller();
});

