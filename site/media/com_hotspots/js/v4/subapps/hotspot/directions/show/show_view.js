/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("DirectionsApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	Show.DirectionsView = Marionette.ItemView.extend({
		tagName: 'div',
		className: 'hs-directions',


		bindings: {
			'.js-hs-input-dir-desktop': {
				observe: 'origin',
				onGet: 'onGetOrigin'
			},
			'.js-hs-input-dir-phone': {
				observe: 'origin',
				onGet: 'onGetOrigin'
			}
		},
		
		onGetOrigin: function(value, options) {
			if(this.model.get('originDisplayName')) {
				return this.model.get('originDisplayName');
			}
			return  value;
		},

		events: {
			'keypress .js-hs-get-directions input': 'keypressSearchDir',
			'click .hs-quick-dir': 'changeDir',
			'click .js-hs-get-directions .js-hs-close': 'clearDirections',
			'click .js-hs-get-directions .js-hs-search': 'searchDir'
		},

		keypressSearchDir: function(e) {
			if ( e.which === 13 ) {
				this.searchDir(e);
			}
		},

		clearDirections: function(e) {
			HotspotsManager.trigger('directions:clear');
			// Clear the rendered directions
			var directionsDisplay = HotspotsManager.request("direction:renderer");
			$(directionsDisplay.getPanel()).html('');

			// clear the entered value
			this.$el.find('input[type=text]').val('');
		},

		changeDir: function(e) {
			e.stopPropagation();
			$(e.currentTarget).closest('.hs-get-directions').find('.hs-quick-dir').each(function(key, el) {
				$(el).removeClass('active');
			});
			$(e.currentTarget).addClass('active');
		},

		searchDir: function(e) {
			e.stopPropagation();
			var input = this.$el.find('.hs-get-directions input'),
				active = this.$el.find('.hs-quick-dir.active').data('id'),
				origin, destination;

			if(active == 'from') {
				origin = input.val();
				destination = {lat: this.markerModel.get('lat'), lng: this.markerModel.get('lng'), name: ''}
			} else {
				destination = input.val();
				origin = {lat: this.markerModel.get('lat'), lng: this.markerModel.get('lng'), name: ''}

			}
			HotspotsManager.trigger('directions:search', origin, destination);
		},

		template: '#js-hs-item-direction',

		initialize: function (options) {
			this.markerModel = options.markerModel;
		},

		onRender: function(){
			var self = this;

			HotspotsManager.on('directions:directions', function(results, status) {
				self.renderDirections(results, status);
			});

			this.bindValidation();
			this.stickit();
		},

		searchDestination: function() {
			this.model.searchDirections();
		},

		renderDirections: function(results, status) {
			var map = HotspotsManager.request("map:object");
			var directionsDisplay = HotspotsManager.request("direction:renderer");

			directionsDisplay.setPanel(this.$el.find('.hs-directions-results')[0]);

			if (status == google.maps.DirectionsStatus.OK) {
				$(directionsDisplay.getPanel()).html('');
				directionsDisplay.setDirections(results);
			} else if (status == google.maps.DirectionsStatus.NOT_FOUND) {
				directionsDisplay.getPanel().set('html', Joomla.JText._('COM_HOTSPOTS_COULDNT_FIND_LOCATION'));
			} else if (status == google.maps.DirectionsStatus.ZERO_RESULTS) {
				directionsDisplay.getPanel().set('html', Joomla.JText._('COM_HOTSPOTS_ZERO_RESULTS_LOCATION'));
			}
		}
	});
});