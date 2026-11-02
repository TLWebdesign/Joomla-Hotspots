/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("SettingsApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	Show.SettingsView = Marionette.ItemView.extend({
		tagName: 'div',
		className: 'hs-settings',

		bindings: {
			'input[name=mylocation]' : 'myLocation',
			'input[name=weather]' : 'weatherLayer',
			'input[name=traffic]': 'trafficLayer',
			'input[name=transit]': 'transitLayer',
			'input[name=bicycle]': 'bicyclingLayer',
		},

		events: {
			'click .js-hs-mylocation' : 'findMyLocation'
		},

		template: '#hs-settings-template',

		onRender: function(){
			Backbone.Stickit.addHandler({
				selector: 'input[type=checkbox].js-hs-stickit',
				update: function($el, val, model, options) {
					if ($el.length > 1) {
						// There are multiple checkboxes so we need to go through them and check
						// any that have value attributes that match what's in the array of `val`s.
						val || (val = []);
						$el.each(function(i, el) {
							var checkbox = Backbone.$(el);
							var checked = _.indexOf(val, checkbox.val()) > -1;
							checkbox.prop('checked', checked);
						});
					} else {
						var checked = (val === 1) ? val : val === $el.val();
						$el.prop('checked', checked);
					}
				}
			});
			this.stickit();
		},

		findMyLocation: function() {
			var self = this;
			navigator.geolocation.getCurrentPosition(function (position) {
				var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

				self.model.geocoder.geocode({latLng:latlng}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						self.model.set('myLocation', results[0].formatted_address)
					}
				});
			});
		}
	});
});