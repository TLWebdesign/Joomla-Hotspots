/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("MapApp", function(MapApp, HotspotsManager, Backbone, Marionette, $, _){

	var API = {
		show: function() {
			var mapModel = HotspotsManager.request('map:entity');
			// if we are centering by boundaries, we need to fit the bounds
			// otherwise we center by the default center
			if (HotspotsConfig.centerType == 1) {
				if(mapModel.get('boundaries')) {
					MapApp.Show.Controller.fitBounds(mapModel.get('boundaries'));
				} else {
					// sometimes users don't have any hotspots in a category, so we don't have any bounds...
					// Let's use normal center...
					mapModel.setCenter();
				}
			} else {
				mapModel.setCenter();
			}

			MapApp.Show.Controller.show();

			this.tiles();
		},
		markers: function(hotspots) {
			MapApp.Markers.Controller.draw(hotspots);
		},
		weather: function(map) {
			var model = HotspotsManager.request('map:entity');
			if(model.get('weatherLayer'))
			{
				MapApp.Weather && MapApp.Weather.Controller.draw(map);
			}
		},
		weatherToggle: function(map) {
			// In the core version we don't have a Weather Module
			MapApp.Weather && MapApp.Weather.Controller.toggle(map);
		},
		address: function(results, status) {
			MapApp.Markers.Controller.renderAddress(results, status);
		},
		coordinates: function(lat, lng, z)
		{
			var mapModel = HotspotsManager.request('map:entity');
			mapModel.set('center', new google.maps.LatLng(lat, lng));
			mapModel.set('zoom', parseInt(z, 10));

			MapApp.Show.Controller.show();
			this.tiles();
		},
		clearAddress: function() {
			MapApp.Markers.Controller.clearAddressMarker();
		},
		setWidthRestriction: function(type, menuView) {
			MapApp.Show.Controller.setWidthRestriction(type, menuView);
		},
		markerZoomIn: function(model) {
			MapApp.Show.Controller.zoomInOnModel(model);
		},
		markerStreetMap: function(pano) {
			MapApp.Show.Controller.streetView(pano, false);
		},
		markerSelected: function(model) {
			MapApp.Show.Controller.streetView('', true);
			MapApp.Markers.Controller.selected(model);
		},
		markerHighlighted: function(model) {
			MapApp.Markers.Controller.highlighted(model);
		},
		markerDehighlighted: function(model) {
			MapApp.Markers.Controller.dehighlighted(model);
		},
		fetchMarkers: function() {
			MapApp.Markers.Controller.fetchMarkers();
		},
		tiles: function() {
			if(HotspotsConfig.tiles) {
				MapApp.Tiles.Controller.show();
			}
		},
		tilesMarkers: function(collection) {
			MapApp.Tiles.Controller.renderTilesMarkers(collection);
		},
		tileMarkerClosed: function(model) {
			MapApp.Tiles.Controller.tileMarkerClosed(model);
		},
		kmls: function(collection) {
			MapApp.Show.Controller.kmlLayer(collection.toJSON());
		},
		resizeMap: function() {
			MapApp.Show.Controller.resize();
		},
		changeBounds: function(bounds)
		{
			var boundaries = new google.maps.LatLngBounds(
					new google.maps.LatLng(bounds.s, bounds.w),
					new google.maps.LatLng(bounds.n, bounds.e)
			)
			MapApp.Show.Controller.fitBounds(boundaries);
		}
	};

	this.listenTo(HotspotsManager, "map:show", function() {
		API.show();
	});

	this.listenTo(HotspotsManager, 'map:coordinates', function(lat, lng, z) {
		API.coordinates(lat, lng, z);
	});

	this.listenTo(HotspotsManager, "filter:search", function() {
		API.tiles();
	});

	this.listenTo(HotspotsManager, "filter:updated", function(filter) {
		var coordinate = filter.get('center').lat().toFixed(7) + '/' + filter.get('center').lng().toFixed(7) + '/' + filter.get('zoom');

		var cats = '';

		if(!_.isEmpty(filter.get('cats'))) {
			cats = '/cats/' + filter.getCatIds().join(';');
		}

		var hotspot = '';
		if(filter.get('hotspot:selected')) {
			hotspot = '/hotspot/'+filter.get('hotspot:selected');
		}

		var page = '';
		if(filter.get('page')) {
			page = '/page/'+filter.get('page');
		}

		var search = '';
		if(filter.get('search')) {
			search = '/search/'+filter.get('search');
		}

		HotspotsManager.navigate(coordinate + cats + page + search + hotspot);
	});

	this.listenTo(HotspotsManager, "tiles:markers", function(collection) {
		API.tilesMarkers(collection);
	});

	this.listenTo(HotspotsManager, "map:changedposition", function(map) {
		API.weather(map);
	});

	this.listenTo(HotspotsManager, "weather:toggle", function(map) {
		API.weatherToggle(map);
	});

	this.listenTo(HotspotsManager, "tile:marker:closed", function(model) {
		API.tileMarkerClosed(model);
	});

	this.listenTo(HotspotsManager, "kmls:fetched", function(collection) {
		API.kmls(collection);
	});

	this.listenTo(HotspotsManager, "hotspots:fetchedData", function(hotspots) {
		return API.markers(hotspots);
	});

	this.listenTo(HotspotsManager, 'directions:address', function(results, status) {
		API.address(results, status);
	});

	this.listenTo(HotspotsManager, 'directions:clearAddress', function() {
		API.clearAddress();
	});

	this.listenTo(HotspotsManager, 'marker:zoomIn', function(model) {
		API.markerZoomIn(model);
	});

	this.listenTo(HotspotsManager, 'hotspot:selected', function(model) {
		API.markerSelected(model);
	});

	this.listenTo(HotspotsManager, 'marker:highlighted', function(model) {
		API.markerHighlighted(model);
	});

	this.listenTo(HotspotsManager, 'marker:dehighlighted', function(model) {
		API.markerDehighlighted(model);
	});

	this.listenTo(HotspotsManager, 'marker:streetView', function(pano) {
		API.markerStreetMap(pano);
	});

	this.listenTo(HotspotsManager, 'initialize:after', function(options) {
		if(HotspotsManager.getCurrentRoute() === ""){
			HotspotsManager.trigger('map:show');
		}
	});

	this.listenTo(HotspotsManager, 'menu:slide', function(type, menuView) {
		API.setWidthRestriction(type, menuView);
		API.resizeMap();
	});

	this.listenTo(HotspotsManager, 'menu:resized', function(menuView) {
		API.setWidthRestriction(menuView.opened, menuView);
	});

	this.listenTo(HotspotsManager, "filter:changed", function() {
		API.fetchMarkers();
	});

	this.listenTo(HotspotsManager, 'window:resize', function() {
		API.resizeMap();
	});

	this.listenTo(HotspotsManager, 'hotspots:newboundaries', function(bounds) {
		API.changeBounds(bounds);
	});
});