/**
 * Created by DanielDimitrov on 08.04.2014.
 */
/**
 * Created by DanielDimitrov on 26.02.14.
 */
HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){

	Entities.Direction = Backbone.Model.extend({
		defaults: {
			origin: '',
			originDisplayName: '',
			destination: '',
			destinationDisplayName: '',
			waypoints: [],
			mode: 'DRIVING',
			units: HotspotsConfig.directionsUnit,
			avoidHighways: false,
			avoidTolls: false
		},

		validation: {
			origin: {
				required: true,
				message: HotspotsConfig.translations.enterValidAddress
			}
		},

		initialize: function() {
			this.on('change:origin', function(model, value) {
				var self = this;
				if(typeof value === 'object') {
					this.set('origin', new google.maps.LatLng(value.lat, value.lng), {silent: true});
					if (value.name.length) {
						this.set('originDisplayName', value.name, {silent: true});
					} else {
						// if the marker doesn't have any address, let us try to find it out out of the
						// lat & lng coordinates
						var geocoder = new google.maps.Geocoder();
						geocoder.geocode({
							location: {lat:value.lat, lng: value.lng}
						}, function (results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								self.set('originDisplayName', results[0].formatted_address, {silent: true});
							}
						});
					}
				} else {
					this.set('originDisplayName', value);
				}
			});

			this.on('change:destination', function(model, value) {
				var self = this;
				if(typeof value === 'object') {
					this.set('destination', new google.maps.LatLng(value.lat, value.lng), {silent: true});
					if (value.name.length) {
						this.set('destinationDisplayName', value.name, {silent: true});
					} else {
						// if the marker doesn't have any address, let us try to find it out out of the
						// lat & lng coordinates
						var geocoder = new google.maps.Geocoder();
						geocoder.geocode({
							location: {lat:value.lat, lng: value.lng}
						}, function (results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								self.set('destinationDisplayName', results[0].formatted_address, {silent: true});
							}
						});
					}
				} else {
					this.set('destinationDisplayName', value);
				}
			});
		},

		searchAddress: function() {
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({
				'address': this.get('destination')
			}, function (results, status) {
				HotspotsManager.trigger('directions:address', results, status);
			});
		},

		searchDirections: function() {
			var directions = new google.maps.DirectionsService();

			var request = {
				origin: this.get('origin'),
				destination: this.get('destination'),
				waypoints: this.get('waypoints'),
				travelMode: google.maps.DirectionsTravelMode[this.get('mode')],
				unitSystem: google.maps.UnitSystem[this.get('units')],
				avoidHighways: this.get('avoidHighways'),
				avoidTolls: this.get('avoidTolls')
			};

			directions.route(request, function (result, status) {
				HotspotsManager.trigger('directions:directions', result, status);
			});
		}
	});


	var API = {
		renderer: false,
		entity: false,
		getDirectionEntity: function() {
			if(!this.entity) {
				this.entity  = new Entities.Direction();
			}
			return this.entity;
		},
		getDirectionRenderer: function() {
			if(!this.renderer) {
				this.renderer = new google.maps.DirectionsRenderer({draggable:true});
			}
			return this.renderer;
		}
	};

	HotspotsManager.reqres.setHandler("direction:entity", function(){
		return API.getDirectionEntity();
	});

	HotspotsManager.reqres.setHandler("direction:renderer", function(){
		return API.getDirectionRenderer();
	});
});
