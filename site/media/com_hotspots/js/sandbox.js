new Class('compojoom.hotspots.sandbox', {
	Extends: Map,
	Implements: [Options, Events, compojoom.hotspots.helper],
	options: {
		mapStartZoom: 10
	},

	eventsMap: [
		{
			host: window,
			events: {
				hotspotsSearchAddress: null,
				hotspotsSearchDirection: null,
				directionFound: null,
				directionDestroy: null,
				zoomToMarkers: null,
				searchTabActive: null,
				panoramaResize: null
			}
		}
	],

	mapListeners: {},
	initialize: function (mapContainer, options) {
		this.setOptions(options);

		this.parent(mapContainer, {
			zoom: this.options.mapStartZoom.toInt()
		});

		this.getBounds();
		this.setMapOptions();
		this.initDirections();

		this.exportAllEvents();

		this.setCenter();

		/**
		 * If the user is centering on a city with a string(ex. Paris, France) instead of
		 * coordinates our setCenter function is making a geocoding request to
		 * the google servers - this takes time and the idle request is fired
		 * too soon. That is why when the user uses a string to center the map
		 * we will use a tilesloaded event instead
		 */
		var event = 'idle';
		if (this.options.centerType == 0) {
			if (!((this.options.mapStartPosition).replace(/\s+/g, '')).match(/^\d+\.\d+\,\d+\.\d+$/)) {
				event = 'tilesloaded';
			}
		}

		google.maps.event.addListenerOnce(this.getMap(), event, function () {
			//send the dispatch signal!
			window.fireEvent('hotspotsDispatch');

			this.mapListeners.zoom_changed = google.maps.event.addListener(this.getMap(), 'zoom_changed', function () {
				window.fireEvent('hotspotsDispatch');
			});

			this.mapListeners.dragend = google.maps.event.addListener(this.getMap(), 'dragend', function () {
				window.fireEvent('hotspotsDispatch');
			});

			if(this.options.highAccuracy) {
				navigator.geolocation.getCurrentPosition(function (position) {
					var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
					this.getMap().setZoom(this.options.customUserZoom);
					this.getMap().setCenter(latlng);
					window.fireEvent('hotspotsDispatch');
				}.bind(this), function () {
					alert(this.translate('COM_HOTSPOTS_GEOLOCATION_NO_SUPPORT', 'Your browser does not support geolocation'));
				}.bind(this));
			}
		}.bind(this));

	},

	onPanoramaResize: function () {
		var panorama = this.getMap().getStreetView();
		if (panorama.getVisible()) {
			google.maps.event.trigger(panorama, 'resize');
		}
	},

	onHotspotsSearchAddress: function (location) {
		this.getGeocoder().geocode({
			'address': location
		}, function (results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				this.getMap().setCenter(results[0].geometry.location);
				var marker = new google.maps.Marker({
					map: this.getMap(),
					position: results[0].geometry.location
				});
			}
			window.fireEvent('hotspotsSearchAddressResponse', [results, status]);
		}.bind(this));

	},
	onHotspotsSearchDirection: function (departure, arrival) {
		var self = this;

		var request = {
			origin: departure,
			destination: arrival,
			travelMode: google.maps.DirectionsTravelMode.DRIVING
		};
		// set the map each time we are searching for direction
		this.directionsDisplay.setMap(this.getMap());

		this.directions.route(request, function (result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				self.directionsDisplay.setDirections(result);
				window.fireEvent('directionFound');
			} else if (status == google.maps.DirectionsStatus.NOT_FOUND) {
				self.directionsDisplay.getPanel().set('html', self.locale.locationNotFound);
			} else if (status == google.maps.DirectionsStatus.ZERO_RESULTS) {
				self.directionsDisplay.getPanel().set('html', self.locale.locationZeroResults);
			}
		});
	},
	onDirectionFound: function () {
		var panorama = this.getMap().getStreetView();
		if (panorama.getVisible()) {
			google.maps.event.trigger(panorama, 'closeclick');
		}
	},
	onDirectionDestroy: function () {
		this.directionsDisplay.setMap(null)
	},

	onZoomToMarkers: function () {
		var catIds = this.gup('catid').split(';');

//      we need to set start values
		if (this.options.categories[catIds[0]]) {
			var catCoords = this.options.categories[catIds[0]].boundaries,
				south = catCoords.south,
				west = catCoords.west,
				north = catCoords.north,
				east = catCoords.east;

			//        if we are loading hotspots from more than 1 categories
			//        we need to determine the proper boundaries
			catIds.each(function (catId) {
				var category = this.options.categories[catId].boundaries;
				south = Math.min(south, category.south);
				west = Math.min(west, category.west);
				north = Math.max(north, category.north);
				east = Math.max(east, category.east);
			}, this);

			this.fitBounds(
				new google.maps.LatLngBounds(
					new google.maps.LatLng(south, west),
					new google.maps.LatLng(north, east)
				)
			);
		} else {
			// if we don't have any bounds selected let us trick the map and set it to world view
			this.fitBounds(
				new google.maps.LatLngBounds(
					new google.maps.LatLng(-80, -160),
					new google.maps.LatLng(80, 160)
				)
			)
		}
	},
	onSearchTabActive: function () {
		this.markers.each(function (marker) {
			marker.destroy();
		});
		this.markers.empty();
	},

	setMapOptions: function () {
		google.maps.visualRefresh = this.options.visualRefresh;
		var mapOptions = {
			panControl : this.options.panControl,
			panControlOptions: {
				position: google.maps.ControlPosition[this.options.panControlPosition]
			},
			zoomControl : this.options.zoomControl,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle[this.options.zoomControlStyle],
				position: google.maps.ControlPosition[this.options.zoomControlPosition]
			},
			mapTypeControl : this.options.mapTypeControl,
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle[this.options.mapTypeControlStyle],
				position: google.maps.ControlPosition[this.options.mapTypeControlPosition]
			},
			scaleControl : this.options.scaleControl,
			streetViewControl : this.options.streetViewControl,
			streetViewControlOptions: {
				position:  google.maps.ControlPosition[this.options.streetViewPosition]
			},
			overviewMapControl : this.options.overviewMapControl,
			scrollwheel: this.options.scrollwheel

		};

		// add the styled maps option
		if (this.options.styledMaps) {
			try {
				mapOptions.styles = JSON.decode(this.options.styledMaps);
			} catch (exception) {
				console.log('Hotspots error: provided map style is incorrect');
			}
		}

		this.getMap().setOptions(mapOptions);
		this.setMapType();

		if(this.options.trafficLayer == 1) {
			var trafficLayer = new google.maps.TrafficLayer();
			trafficLayer.setMap(this.getMap());
		}

		if(this.options.transitLayer == 1) {
			var transitLayer = new google.maps.TransitLayer();
			transitLayer.setMap(this.getMap());
		}

		if(this.options.bicyclingLayer == 1) {
			var bikeLayer = new google.maps.BicyclingLayer();
			bikeLayer.setMap(this.getMap());
		}
	},

	initDirections: function () {
		if (this.options.getDirections) {
			this.directions = new google.maps.DirectionsService();
			this.directionsDisplay = new google.maps.DirectionsRenderer({draggable:this.options.draggableDirections});

			this.directionsDisplay.setPanel(document.id('directions-display'));
		}
	},


	setMapType: function () {
		this.getMap().setMapTypeId(google.maps.MapTypeId[{
			0: "ROADMAP",
			1: "ROADMAP",
			2: "SATELLITE",
			3: "HYBRID",
			4: "TERRAIN"
		}[this.options.mapType]]);
	},

	setCenter: function () {
//		if we have a center & zoom variable in the url, use them
		if (this.gup('c') && this.gup('z')) {
			var c = decodeURI(this.gup('c')).replace('(','').replace(')','').replace(' ', '').split(',');
			c = c.map(function(el) {
				return el.toFloat();
			});
			this.parent(c);
			this.setZoom(this.gup('z').toInt());
		} else {
			// otherwise use the default zoom and center settings
			if (this.options.centerType == 1) {
				this.onZoomToMarkers();
			} else {
				/**
				 * check if the center is a string or a lat|long
				 * If it is lat/long make an array of it and
				 * make sure that each member of the array is
				 * a Float variable
				 */
				var center = this.options.mapStartPosition;
				if (((center).replace(/\s+/g, '')).match(/^-?\d+\.\d+\,-?\d+\.\d+$/)) {
					center = (center.replace(/\s+/g, '')).split(',');
					for (var i = 0; i < center.length; i++) {
						center[i] = parseFloat(center[i]);
					}
				}

				this.parent(center);
				this.setZoom(this.options.mapStartZoom.toInt());
			}
		}
	},

	getStaticMapParams: function () {
		var icon = '',
			map = this.getMap(),
			markers = this.markers,
			params = [],
			markersArray = [],
			self = this;

		params.push("center=" + map.getCenter().lat().toFixed(6) + "," + map.getCenter().lng().toFixed(6));
		params.push("zoom=" + map.getZoom());


		Object.each(markers, function (marker, key) {
			if (self.getMap().getBounds().contains(marker.getPosition()) && markersArray.length < 70) {
				markersArray.push(marker.getPosition().lat().toFixed(6) + "," + marker.getPosition().lng().toFixed(6));

				// Set a category icon
				if(!icon && !self.options.categories[marker.options.catid].cat_icon.contains('localhost')) {
					icon = self.options.categories[marker.options.catid].cat_icon;
				}
			}
		});

		if (markersArray.length) {
			params.push("markers=icon:" + encodeURI(icon) + '|' + markersArray.join("|"));
		}

		var type = {
			'roadmap': 'roadmap',
			'satellite': 'satellite',
			'hybrid': 'hybrid',
			'terrain': 'terrain'}[map.getMapTypeId()];

		params.push('maptype=' + type);

		params.push("size=" + this.options.staticMapWidth + "x" + this.options.staticMapHeight);

		params.push('sensor=false');
		return params.join('&');
	}

});