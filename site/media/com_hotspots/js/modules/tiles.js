new Class('compojoom.hotspots.modules.tiles', {
	Extends: compojoom.hotspots.modules.hotspot,
	Implements: [Options, Events, compojoom.hotspots.helper],

	isMenuOpen: false,
	fullScreen: false,
	options: {
		mailMap: 1
	},
	xhr: {},

	tiles: {},
	tileMarkers: {},


	initialize: function (options, sb) {
		var self = this;
		this.sb = sb;
		this.setOptions(options);

		var map = this.sb.getMap();

		this.setCustomTiles();

		this.infoWindow = Map.InfoBubble;
		this.infoWindow.setOptions({disableAutoPan: false, minWidth: 200, maxWidth: 320, minHeight: 30, maxHeight: 350});

		/**
		 * if the zoom changes we can safely remove all markers from memory
		 */
		google.maps.event.addListener(map, 'zoom_changed', function () {
			self.cleanUp();
		});

		/**
		 * listen for the mouse position and get information about markers
		 * on that specific tile.
		 */
		google.maps.event.addListener(map, 'mousemove', function (mev) {
			var TILE_SIZE = 256,
				proj = map.getProjection(),
				numTiles = 1 << map.getZoom(),
				worldCoordinate = proj.fromLatLngToPoint(mev.latLng),
				pixelCoordinate = new google.maps.Point(
					worldCoordinate.x * numTiles,
					worldCoordinate.y * numTiles
				),
				tileCoordinate = new google.maps.Point(
					Math.floor(pixelCoordinate.x / TILE_SIZE),
					Math.floor(pixelCoordinate.y / TILE_SIZE)
				),
				coord = tileCoordinate.x + "," + tileCoordinate.y + ',' + map.getZoom();

			if (!self.tiles[coord]) {
				self.getTileInfo(tileCoordinate);
				self.tiles[coord] = true;
			}
		});

		// we need to reset the state of the tile markers when we change between categories
		// or when we search
		if (window.addEventListener) {
			window.addEventListener("hashchange", function () {
				self.cleanUp();
			}, false);
		} else if (window.attachEvent) {
			window.attachEvent("onhashchange", function () {
				self.cleanUp();
			});
		}

	},

	getTileInfo: function (coord) {
		var self = this,
			params = self.getUrlParams(coord);

		// return if we don't have any category to show
		if (self.gup('catid').split(';').contains('-1')) {
			return;
		}
		new Request.JSON({
			url: this.options.baseUrl + '?option=com_hotspots&view=tile&format=json&' + params.join('&'),
			data: 'hs-language=' + Locale.getCurrent().name,
			onComplete: function (data) {
				data.each(function (hotspot, key) {

					// clean up map if we get too much markers in memory
					if (Object.keys(self.tileMarkers).length > 2000) {
						self.cleanUp();
					}

					if (!self.tileMarkers[hotspot.id]) {
						var position = new google.maps.LatLng(hotspot.lat, hotspot.lng);

						var markerOptions = {
							'title': hotspot.nm,
							flat: true,
							icon: new google.maps.MarkerImage(
								self.options.rootUrl + '/media/com_hotspots/images/utils/trans-marker.png',
								new google.maps.Size(7, 7),
								null,
								new google.maps.Point(3, 3)
							)
						}
						var marker = self.tileMarkers[hotspot.id] = new Map.Marker(position, self.sb.getMap(), markerOptions);
						var refInfoWindow = self.createInfoWindow(marker, hotspot);
						marker.addEvent('click', refInfoWindow);
					}
				});

			}
		}).send();
	},

	/**
	 * We need the same params for both the tile + ajax request for markers
	 * @param coord
	 * @return {Array}
	 */
	getUrlParams: function (coord) {
		var zoom = this.sb.getMap().getZoom(),
			categories = this.gup('catid'),
			search = this.gup('search'),
			params = [
				'x=' + coord.x,
				'y=' + coord.y,
				'z=' + zoom,
				'hs-language=' + Locale.getCurrent().name
			];

		if (categories) {
			params.push('cats=' + categories);
		}
		if (search) {
			params.push('search=' + search);
		}

		return params;
	},
	setCustomTiles: function () {
		var self = this,
			customTile = {
				getTileUrl: function (coord, zoom) {
					var normalizedCoord = self.getNormalizedCoord(coord, zoom);

					// return if we have - coordiantes or if we are not showing any categories
					if (!normalizedCoord || self.gup('catid').split(';').contains('-1')) {
						return null;
					}
					var params = self.getUrlParams(normalizedCoord);
					return self.options.baseUrl + "?option=com_hotspots&task=tiles.create&format=png&" + params.join('&');
				},
				tileSize: new google.maps.Size(256, 256),
				name: "MarkersTile"
			},
			customTileMap = new google.maps.ImageMapType(customTile),
			map = this.sb.getMap();

		map.overlayMapTypes.insertAt(0, customTileMap);

//      load new tiles when we load new category, or do a search
		if (window.addEventListener) {
			window.addEventListener("hashchange", function () {
				map.overlayMapTypes.clear();
				map.overlayMapTypes.insertAt(0, customTileMap);
			}, false);
		} else if (window.attachEvent) {
			window.attachEvent('onhashchange', function () {
				map.overlayMapTypes.clear();
				map.overlayMapTypes.insertAt(0, customTileMap);
			});
		}
	},

	/**
	 * Normalizes the coords that tiles repeat across the x axis (horizontally)
	 * like the standard Google map tiles.
	 */
	getNormalizedCoord: function (coord, zoom) {
		var y = coord.y;
		var x = coord.x;

		// tile range in one direction range is dependent on zoom level
		// 0 = 1 tile, 1 = 2 tiles, 2 = 4 tiles, 3 = 8 tiles, etc
		var tileRange = 1 << zoom;

		// don't repeat across y-axis (vertically)
		if (y < 0 || y >= tileRange) {
			return null;
		}

		// repeat across x-axis
		if (x < 0 || x >= tileRange) {
			x = (x % tileRange + tileRange) % tileRange;
		}

		return {
			x: x,
			y: y
		};
	},


	/**
	 * when we start to have too many markers the browser start to feel sluggish.
	 * If we have more than 2000 marker -> destroy them...
	 */
	cleanUp: function () {
		var self = this;

		self.tiles = {};
		Object.each(self.tileMarkers, function (marker, key) {
			marker.destroy();
			delete self.tileMarkers[key];
		});
	}

});