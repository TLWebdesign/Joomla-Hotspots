/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("DirectionsApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	Show.DirectionsView = Marionette.ItemView.extend({
		tagName: 'div',
		className: 'hs-directions',

		bindings: {
			'.search-location .destination': {
				observe: 'destination',
				onGet: function(value, options) {
					if(this.model.get('destinationDisplayName')) {
						return this.model.get('destinationDisplayName');
					}
					return  value;
				}
			},
			'.js-hs-search-directions input[name=units]': 'units',
			'#js-hs-avoid-highways': {
				observe: 'avoidHighways',
				onSet: function(value, option) {
					return value ? true : false;
				}
			},
			'#js-hs-avoid-tolls': {
				observe: 'avoidTolls',
				onSet: function(value, option) {
					return value ? true : false;
				}
			}
		},

		events: {
			'submit #js-hs-search-address-form': 'search',
			'submit #js-hs-search-directions-form': 'getDirections',
			'click .js-hs-show-directions' : 'showDirections',
			'click .js-hs-get-directions' : 'getDirections',
			'click button.js-hs-directions-search' : 'search',
			'click .hs-close' : 'close',
			'click .js-hs-add-stop': 'addStop',
			'click .js-hs-direction-options': 'showOptions',
			'click .hs-routing-mode div': 'changeMode',
			'click .js-hs-clear-search': 'clearDirections',
			'click .js-hs-clear-address-search': 'clearAddressSearch',
			'click .js-hs-back-address': 'backToAddress'
		},

		selectors: {
			dirInputs: '.js-hs-search-directions input[type=text]'
		},

		ui: {
			address: '.search-location input',
			drag: '.sortable span.hs-drag',
			dirInputs: '.js-hs-search-directions input[type=text]'
		},

		template: '#hs-directions-template',

		clearDirections: function() {
			HotspotsManager.trigger('directions:clear');
			// Clear the rendered directions
			var directionsDisplay = HotspotsManager.request("direction:renderer");
			$(directionsDisplay.getPanel()).html('');

			// Clear the input fields
			var inputs = this.$el.find(this.selectors.dirInputs).toArray();
			_.each(inputs, function(input) {
				$(input).val('');
			});

			inputs.shift().focus();

			// Hide the clear search option
			this.$el.find('.js-hs-clear-search').css('display', 'none');
		},

		backToAddress: function() {
			this.$el.find('.hs-hide').css({'display': 'none'});
			this.$el.find('.search-location').css('display', 'block');
			this.$el.find('.hs-search-results').css('display', 'block');
		},

		clearAddressSearch: function() {
			HotspotsManager.trigger('directions:clearAddress');
			this.$el.find('.hs-search-results').html('');
			this.$el.find('.js-hs-clear-address-search').css('display', 'none');
			this.ui.address.val('').focus();
		},

		showOptions: function(e) {
			var el = $(e.currentTarget),
				container = this.$el.find('.hs-directions-options-container');

			el.toggleClass('fa-angle-double-down').toggleClass('fa-angle-double-up');
			container.toggle();
		},

		close: function(e) {
			var el = $(e.currentTarget),
				row = el.closest('.row-fluid'),
				inputs = this.$el.find(this.selectors.dirInputs);

			if(inputs.length == 2) {
				this.$el.find('.hs-hide').css({'display': 'none'});
				this.$el.find('.search-location').css('display', 'block');
				this.$el.find('.hs-search-results').css('display', 'block');
			} else {
				row.remove();
				$('.js-hs-add-stop').show();
				this.$el.find('.sortable span.hs-drag').last().removeClass('fa-circle-o').addClass('fa-map-marker');
				if(this.$el.find(this.selectors.dirInputs).length == 2) {
					this.$el.find('.js-hs-drag-info').css('display', 'none');
				}
			}
		},

		addStop: function(e) {
			var span = this.$el.find('.sortable span.hs-drag').last();
			span.removeClass('fa-map-marker').addClass('fa-circle-o');

			this.$el.find(this.selectors.dirInputs)
				.closest('.row-fluid')
				.last().after($('#hs-directions-single-row-template').html());

			var inputs = this.$el.find(this.selectors.dirInputs);

			// remove any errors if the user is adding a stop
			this.$el.find('.hs-search-directions .control-group.error').removeClass('error');
			this.$el.find('.hs-search-directions .help-block.error').remove();

			// Show the drag reordering option
			if(inputs.length >= 3) {
				this.$el.find('.js-hs-drag-info').css('display', 'block');
			}

			// Focus on the last field
			inputs.last().focus();

			if(inputs.length == 10) {
				e.currentTarget.hide();
			}
		},

		changeMode: function(e) {
			var modes = this.$el.find('.hs-routing-mode div'),
				mode = $(e.target);
			modes.removeClass('active');
			mode.addClass('active');

			this.model.set('mode', mode.data('type'));
		},

		showDirections: function() {
			var self = this, inputs = this.$el.find(this.selectors.dirInputs),
				origin, destination;
			this.$el.find('.hs-hide').css({'display': 'block'});
			this.$el.find('.search-location').css('display', 'none');
			this.$el.find('.hs-search-results').css('display', 'none');

			origin = inputs.first();
			// Set the focus and then change the value if necessary
			origin.focus();
			if(!origin.val()) {
				if(this.model.get('originDisplayName')) {
					origin.val(this.model.get('originDisplayName'));
				} else {
					origin.val(this.model.get('origin'));
				}
			}

			if(this.model.get('destination')) {
				destination = inputs.last();
				if(this.model.get('destinationDisplayName')) {
					destination.val(this.model.get('destinationDisplayName'));
				} else {
					destination.val(this.model.get('destination'));
				}
			}

			if(this.model.get('origin') || this.model.get('destination')) {
				self.$el.find('.js-hs-clear-search').css('display', 'inline-block');
			}

			$( ".sortable" ).sortable({
				axis: "y",
				stop: function(event, ui) {
					var spans = self.$el.find('.sortable span.hs-drag');
					_.each(spans, function(span, key) {
						$(span).removeClass('fa-map-marker').addClass('fa-circle-o');
					});
					spans.last().removeClass('fa-circle-o').addClass('fa-map-marker');
				}
			});
		},

		searchDestination: function() {
			this.showDirections();
			this.model.searchDirections();
		},

		getDirections: function(e) {
			var locations = this.$el.find('.hs-search-directions input[type=text]').toArray();
			var origin = $(locations.shift()),
				destination = $(locations.pop()),
				waypoints = [];

			e.preventDefault();

			this.model.set('origin', origin.val());
			this.model.set('destination', destination.val());

			// Remove any error classes
			this.$el.find('.hs-search-directions .control-group.error').removeClass('error');
			this.$el.find('.hs-search-directions .help-block.error').remove();


			if(!origin.val()) {
				this.outputDirError(origin, HotspotsConfig.translations.provideStartAddress);
			}

			if(!destination.val()) {
				this.outputDirError(destination, HotspotsConfig.translations.provideEndAddress);
			}

			// Stop the execution here, because we have errors
			if(!origin.val() || !destination.val()) {
				return;
			}

			if(locations.length)
			{
				locations.forEach(function(location) {
					if(location.value) {
						waypoints.push({
							location: location.value,
							stopover: true
						});
					} else {
						$(location).closest('.row-fluid').remove();
					}
				})
			}

			if(waypoints.length) {
				this.model.set('waypoints', waypoints);
			}

			this.$el.find('.js-hs-clear-search').css('display', 'inline-block');

			this.model.searchDirections();
		},

		outputDirError: function(el, errors) {
			var parent = el.parent();

			parent.find('.help-block.error').remove();
			var $errorEl = $('<div>', {class: 'help-block small muted error', text: errors});
			el.parent().append($errorEl).addClass('error');
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
			} else if (status == "REQUEST_DENIED") {
				directionsDisplay.getPanel().set('html', "Request denied by google. Check that your API key is valid for the directions API.");
			}
		},

		search: function(e) {
			e.preventDefault();

			if(this.model.isValid('destination')) {
				this.$el.find('.js-hs-clear-address-search').css('display', 'block');
				var addon = this.$el.find('.search-location .add-on span');
				addon.removeClass('hs-drag fa-map-marker').addClass('fa-spinner fa-spin');

				this.model.searchAddress();
			}
		},

		renderAddress: function(results, status) {
			var renderEl = $('.hs-search-results').css('display', 'block');

			if (status == google.maps.GeocoderStatus.OK) {
				var addon = this.$el.find('.search-location .add-on span');
				addon.removeClass('fa-spinner fa-spin').addClass('hs-drag fa-map-marker');
				var html = Marionette.Renderer.render('#hs-address-result-template',
					{address: results[0].formatted_address});
			}

			renderEl.html(html);
		},

		onRender: function(){
			var self = this;
			HotspotsManager.on('directions:address', function(results, status) {
				self.renderAddress(results, status);
			});

			HotspotsManager.on('directions:directions', function(results, status) {
				self.renderDirections(results, status);
			});

			this.bindValidation();
			this.stickit();
		},


		onValidField: function(attrName, attrValue, model) {
			var el = this.$el.find('.search-location .'+attrName),
				parent = el.parent();
			parent.removeClass('error');
			parent.find('.help-block.error').remove();
		},

		onInvalidField: function(attrName, attrValue, errors, model) {
			var el = this.$el.find('.search-location .'+attrName),
				parent = el.parent();

			parent.find('.help-block.error').remove();
			var $errorEl = $('<div>', {class: 'help-block small muted error', text: errors});
			el.parent().append($errorEl).addClass('error');
		}
	});
});
