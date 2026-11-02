/**
 * Created by DanielDimitrov on 24.03.14.
 */

HotspotsManager.module("Entities", function (Entities, HotspotsManager, Backbone, Marionette, $, _) {
	Entities.Map = Backbone.Model.extend(
		{
			defaults: {
				myLocation: '',
				mapStartPosition: HotspotsConfig.mapStartPosition,
				zoom:  HotspotsConfig.mapStartZoom,
				backgroundColor: HotspotsConfig.mapBackgroundColor,
				mapTypeId: google.maps.MapTypeId[{
					0: "ROADMAP",
					1: "ROADMAP",
					2: "SATELLITE",
					3: "HYBRID",
					4: "TERRAIN"
				}[HotspotsConfig.mapType]],
				panControl: HotspotsConfig.panControl,
				panControlOptions: {
					position: google.maps.ControlPosition[HotspotsConfig.panControlPosition]
				},
				zoomControl: HotspotsConfig.zoomControl,
				zoomControlOptions: {
					style: google.maps.ZoomControlStyle[HotspotsConfig.zoomControlStyle],
					position: google.maps.ControlPosition[HotspotsConfig.zoomControlPosition]
				},
				mapTypeControl: HotspotsConfig.mapTypeControl,
				mapTypeControlOptions: {
					style: google.maps.MapTypeControlStyle[HotspotsConfig.mapTypeControlStyle],
					position: google.maps.ControlPosition[HotspotsConfig.mapTypeControlPosition]
				},
				scaleControl: HotspotsConfig.scaleControl,
				streetViewControl: HotspotsConfig.streetViewControl,
				streetViewControlOptions: {
					position: google.maps.ControlPosition[HotspotsConfig.streetViewPosition]
				},
				overviewMapControl: HotspotsConfig.overviewMapControl,
				scrollwheel: HotspotsConfig.scrollwheel,
        gestureHandling: HotspotsConfig.gestureHandling,
				weatherLayer: HotspotsConfig.weatherLayer,
				weatherTemperatureUnit: HotspotsConfig.weatherTemperatureUnit,
				trafficLayer: HotspotsConfig.trafficLayer,
				transitLayer: HotspotsConfig.transitLayer,
				bicyclingLayer: HotspotsConfig.bicyclingLayer,
        fullscreenControl: HotspotsConfig.fullscreenControl
			},

			setCenter: function() {
				var self = this, myLocation = this.getMyLocation();
				if(myLocation) {
					this.geocoder.geocode({
						'address': myLocation
					}, function (results, status) {
						self.set('center', results[0].geometry.location);
					});
				} else {
					var center = this.get('mapStartPosition'), type;

					// Let us find out if we are dealing with coordinates
					if (((center).replace(/\s+/g, '')).match(/^-?\d+\.\d+\,-?\d+\.\d+$/)) {
						center = (center.replace(/\s+/g, '')).split(',');
						for (var i = 0; i < center.length; i++) {
							center[i] = parseFloat(center[i]);
						}
					}

					type = typeof center;
					if (type === 'string') {
						// We have a string for center? Then this is an address, we need to use
						// the geocoder toget the coordinates
						this.geocoder.geocode({'address': center}, function (point) {
							if (!point.length) {
								alert(center + " " + this.options.notFound);
							} else {
								self.set('center', point[0].geometry.location);
							}
						});
					}

					if (type === 'object' && _.isArray(center)) {
						// let us set the center, since we have coordinates
						this.set('center', new google.maps.LatLng(center[0], center[1]));
					}

					// if we should center using the user's location & we have high accuracy set
					// then we use the navigator to tell us the user's position
					if(HotspotsConfig.centerType == 2 && HotspotsConfig.highAccuracy) {
						navigator.geolocation.getCurrentPosition(function (position) {
							self.set('center', new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
						});
					}
				}
			},

			getMyLocation: function() {
				return $.cookie('hs-my-location');
			},

			setMyLocation: function() {
				$.cookie('hs-my-location', this.get('myLocation'), { expires: 7, path: '/' });
			},

			initialize: function () {
				if (HotspotsConfig.styledMaps) {
					try {
						this.set('styles',JSON.parse(HotspotsConfig.styledMaps));
					} catch (exception) {
						console.log('Hotspots error: provided map style is incorrect');
					}
				}

				// Init the layers that we support
				this.trafficLayer = new google.maps.TrafficLayer();

				this.transitLayer = new google.maps.TransitLayer();

				this.bicyclingLayer = new google.maps.BicyclingLayer();

				this.geocoder = new google.maps.Geocoder();

				this.svService = new google.maps.StreetViewService();

				this.on('change:myLocation', this.setMyLocation, this);

				this.set('myLocation', this.getMyLocation());

				this.getBounds();
			},

			/**
			 * Here we determine the default map boundaries based on the
			 * all category bounds
			 */
			getBounds: function() {
				if(HotspotsManager.reqres.hasHandler('category:entities')) {
					var cats = HotspotsManager.request('category:entities'),
						selected = cats.selected,
						south, west, north, east;

					if(!_.isEmpty(selected)) {
						cats = new Backbone.Collection(_.values(selected));
					}

					cats.each(function (cat) {
						var bounds = cat.get('boundaries');
						if(bounds) {
							south = south ? Math.min(south, bounds.south) : bounds.south;
							west = west ? Math.min(west, bounds.west) : bounds.west;
							north = north ?  Math.max(north, bounds.north) : bounds.north;
							east = east ? Math.max(east, bounds.east) : bounds.east;
						}
					}, this);

					if(south && west && north && east) {
						this.set('boundaries', new google.maps.LatLngBounds(
								new google.maps.LatLng(south, west),
								new google.maps.LatLng(north, east)
							)
						);
					}
				}
			}
		}
	);

	var API = {
		model: false,
		getMapEntity: function () {
			if (!this.model) {
				this.model = new Entities.Map()
			}
			return this.model;
		}
	};

	HotspotsManager.reqres.setHandler("map:entity", function () {
		return API.getMapEntity();
	});
});
