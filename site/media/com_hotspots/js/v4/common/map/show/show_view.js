/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("Common.MapApp", function (MapApp, HotspotsManager, Backbone, Marionette, $, _) {

	MapApp.MapView = Backbone.View.extend({
		template: '#map-template',
		id: 'map'
	});

	MapApp.Map = Marionette.ItemView.extend({
		tagName: "div",
		template: '#main-map-template',

		id: 'map-container',

		className: 'hs-map-container',

		widthRestriction: 0,

		events: {
			'click .js-hs-fullscreen-toggle': function(e) {
				e.stopPropagation();
				HotspotsManager.trigger('map:fullscreen');

				var span = $(e.currentTarget).find('span');
				if(span.hasClass('fa-expand')) {
					span.removeClass('fa-expand').addClass('fa-compress');
				} else {
					span.removeClass('fa-compress').addClass('fa-expand');
				}

				this.resize();
				google.maps.event.trigger(this.mapObj, 'resize');
			},
			'click .js-hs-geocode-center': 'findUserPosition',
			'click .js-hs-hotspots-share-mail': function() {
				HotspotsManager.trigger('share:mail');
			}
		},

		modelEvents: {
			'change:weatherLayer': function() {
				this.weather();
			},
			'change:trafficLayer': function() {
				this.setLayer('trafficLayer')
			},
			'change:transitLayer': function() {
				this.setLayer('transitLayer')
			},
			'change:bicyclingLayer': function() {
				this.setLayer('bicyclingLayer')
			},
			'change:center': function() {
				this.mapObj.setCenter(this.model.get('center'));
			},
			'change:zoom': function() {
				this.mapObj.setZoom(this.model.get('zoom'));
			}
		},

		kmlLayers: [],

		initialize: function() {
			var self = this;
			this.mapView = new MapApp.MapView();

			this.mapObj = new google.maps.Map(this.mapView.el, this.model.toJSON());

			HotspotsManager.on('directions:directions', function(results, status) {
				self.renderDirections(results, status);
			});

			HotspotsManager.on('directions:clear', function() {
				self.clearDirections();
			});

			this.addCustomButtons();
		},

		addCustomButtons: function() {
			// fullscreen & center
			var topRight = document.createElement('div');
			topRight.innerHTML = $('#hs-fullscreen-template').html();
			topRight.index = -1;
			this.mapObj.controls[google.maps.ControlPosition.TOP_RIGHT].push(topRight);

			// print, send, rss
			var rightBottom = document.createElement('div');
			rightBottom.innerHTML = $('#hs-action-buttons-map-template').html();
			rightBottom.index = -1;
			this.mapObj.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(rightBottom);
		},

		renderDirections: function(results, status) {
			var directionsDisplay = HotspotsManager.request('direction:renderer');
			directionsDisplay.setMap(this.mapObj);
		},

		clearDirections: function() {
			var directionsDisplay = HotspotsManager.request('direction:renderer');
			directionsDisplay.setMap(null);
		},

		onRender: function () {
			this.$el.html(this.mapView.el);
		},

		onShow: function() {
			this.resize();
		},

		weather: function() {
			HotspotsManager.trigger('weather:toggle', this.mapObj);
		},

		/**
		 * Turns on or off the specified layer
		 * @param name
		 */
		setLayer: function(name) {
			if(this.model.get(name)) {
				this.model[name].setMap(this.mapObj);
			}
			else {
				this.model[name].setMap(null);
			}
		},

		setWidthRestriction: function(width) {
			this.widthRestriction = width;
		},

		zoomOnPoint: function(point, zoom) {
			this.mapObj.setCenter(point);
			this.mapObj.setZoom(zoom);
		},

		streetView: function(pano, close) {
			var panorama = this.mapObj.getStreetView();
			if(close){
				// explicitly close
				if(panorama.getVisible()){
					panorama.setVisible( false);
				}
			} else {
				// Toggle the states
				panorama.setPano(pano);
				panorama.setVisible(panorama.getVisible() ? false : true);
			}

		},

		findUserPosition: function(e){
			var self = this;
			$(e.target).removeClass('fa-crosshairs').addClass('fa-refresh fa-spin');
			navigator.geolocation.getCurrentPosition(function (position) {
				var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
					imgUrl = HotspotsConfig.rootUrl + '/media/com_hotspots/images/v4/icons/bluedot_retina.png';
				self.zoomOnPoint(latlng, 15);
				$(e.target).removeClass('fa-refresh fa-spin').addClass('fa-crosshairs');

				// define our custom marker image
				var image = new google.maps.MarkerImage(
					imgUrl,
					null, // size
					null, // origin
					new google.maps.Point( 8, 8 ), // anchor (move to center of marker)
					new google.maps.Size( 17, 17 ) // scaled size (required for Retina display icon)
				);

				new google.maps.Marker({
					flat: true,
					icon: image,
					optimized: false,
					position: latlng,
					map: self.mapObj,
					title: HotspotsConfig.translations.myLocation,
					visible: true
				});

				setTimeout(function() {
					self.$el.find('div.gmnoprint:has(img[src="' + imgUrl + '"])').addClass('hs-my-position-dot')
				}, 2000)


			});
		},

		showKmlLayers: function(kmls) {
			var self = this;
			// walk through the kmls in the layers array and remove them from the map
			_.each(self.kmlLayers, function(layer) {
				layer.setMap(null);
			});
			self.kmlLayers.length = 0;

			_.each(kmls, function (kml) {
				var kmlLayer = new google.maps.KmlLayer(kml.file, {preserveViewport: true});
				kmlLayer.setMap(self.mapObj);

				// add the kmlLayer to the array
				self.kmlLayers.push(kmlLayer);
			});
		}
	});
});