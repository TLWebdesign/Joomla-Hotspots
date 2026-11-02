new Class('compojoom.hotspots.modules.hotspot', {
	Implements: [Options, Events, compojoom.hotspots.helper],

	isMenuOpen: false,
	fullScreen: false,
	options: {
		mailMap: 1
	},
	xhr: {},

	eventsMap: [
		{
			host: window,
			events: {
				hotspotsLoadCategory: 'getHotspots',
				hotspotsSearchHotspot: null,
				listHotspots: null,
				hotspotsLoadedHotspots: null,
				openUniqueInfoWindow: 'openUniqueInfoWindow',
				hotspotsResize: null,
				menuClose: null,
				resize: null,
			}
		}
	],
	lastCenterBounds: '',
	//keeps track of the marker that has an InfoWindow open
	openedMarkerId: 0,

	initialize: function (options, sb) {
		var self = this;
		this.sb = sb;
		this.setOptions(options);

		this.infoWindow = Map.InfoBubble;
		this.infoWindow.setOptions({disableAutoPan: false, minWidth: 200, maxWidth: 320, minHeight: 30, maxHeight: 350});

		this.createXhr();
		this.exportAllEvents();

		// reset the last center in case the zoom changes
		google.maps.event.addListener(this.sb.getMap(), 'zoom_changed', function () {
			self.lastCenterBounds = '';
		});

		if (window.addEventListener) {
			window.addEventListener("hashchange", function () {
				self.openedMarkerId = 0;
				self.lastCenterBounds = '';
			}, false);
		} else if (window.attachEvent) {
			//IE8...
			window.attachEvent("onhashchange", function () {
				self.openedMarkerId = 0;
				self.lastCenterBounds = '';
			}, false);
		}

		this.infoWindow.addEvent('closeclick', function () {
			self.openedMarkerId = 0;
		});
	},

	/**
	 * reset the lastCenterBounds if the window dimensions change
	 */
	onHotspotsResize: function () {
		this.lastCenterBounds = '';
		google.maps.event.trigger(this.infoWindow.infoWindowObj, 'content_changed');
	},

	onResize: function() {
		google.maps.event.trigger(this.infoWindow.infoWindowObj, 'content_changed');
	},

	createXhr: function () {
		this.xhr = new Request.JSON({
			link: 'cancel',
			data: 'hs-language=' + Locale.getCurrent().name,
			onRequest: function () {
				window.fireEvent('hotspotsLoaderStart');
			}.bind(this),
			onSuccess: function (data) {
				window.fireEvent('hotspotsLoadedHotspots', data);
				window.fireEvent('parseHotspotsList', data);
			}.bind(this),
			onComplete: function () {
				window.fireEvent('hotspotsLoaderStop');
			}.bind(this),
			onFailure: function () {
				window.fireEvent('hotspotsLoaderStop');
			},
			onCancel: function () {
				window.fireEvent('hotspotsLoaderStop');
			}
		});
	},

	controlCenterBounds: function () {
		var center = this.sb.getMap().getCenter(),
			b = this.sb.getMap().getBounds(),
			x = (b.getNorthEast().lat() - center.lat()) / 1.5,
			y = (b.getNorthEast().lng() - center.lng()) / 1.5,
			east = center.lat() + x,
			north = center.lng() + y,
			west = center.lat() - x,
			south = center.lng() - y;

		return new google.maps.LatLngBounds(new google.maps.LatLng(west, south), new google.maps.LatLng(east, north));
	},

	/**
	 * This function executes the ajax request that takes the data for
	 * the markers on the map.
	 * We also determine on the base of the lastCenterBounds if the user
	 * has moved the map enough for us to create a new request or not
	 *
	 * deleteCat is changed to true whenever we try to load the next/privous pages
	 * with markers
	 *
	 * @param params
	 */
	getHotspots: function (params) {
		var self = this,
			gmbounds = this.sb.getMap().getBounds(),
			offset = 0,
			categories = this.gup('catid');

		self.deleteCat = false;

		if (typeof params == 'undefined') {
			params = {};
		}

		/**
		 * if params has a categories, then we need to reset the lastBounds
		 * and set the deleteCat flag
		 */
		if (typeof params.categories != 'undefined') {
			categories = params.categories;
			self.deleteCat = params.categories;
			self.lastCenterBounds = '';
		}

		if (!this.lastCenterBounds) {
			this.lastCenterBounds = this.controlCenterBounds();
		} else if (
			!(gmbounds.contains(this.lastCenterBounds.getNorthEast())
				&& gmbounds.contains(this.lastCenterBounds.getSouthWest()))
			) {
			this.lastCenterBounds = this.controlCenterBounds();
		} else {
			return;
		}

		if (typeof params.offset != 'undefined') {
			offset = params.offset;
		}
		var query = [
			'option=com_hotspots',
			'view=json',
			'task=gethotspots',
			'cat=' + categories,
			'level=' + this.sb.getMap().getZoom(),
			'ne=' + gmbounds.getNorthEast().toUrlValue(),
			'sw=' + gmbounds.getSouthWest().toUrlValue(),
			'offset=' + offset,
			'format=raw'
		];

		this.xhr.setOptions({
			url: this.options.baseUrl + "?" + query.join('&')
		}).send();
	},

	oldSearchWord: null,

	xhrSearch: null,
	createXhrSearch: function() {
		if(!this.xhrSearch) {
			this.xhrSearch = new Request.JSON({
				method: 'post',
				link: 'cancel',
				onRequest: function () {
					window.fireEvent('hotspotsLoaderStart');
				}.bind(this),
				onFailure: function () {
					window.fireEvent('hotspotsLoaderStop');
				},
				onComplete: function () {
					window.fireEvent('hotspotsLoaderStop');
				}.bind(this),
				onCancel: function(){
					window.fireEvent('hotspotsLoaderStop');
				}
			});
		}

		return this.xhrSearch;
	},

	onHotspotsSearchHotspot: function (searchWord, offset) {
		var self = this,
			bounds = this.sb.getMap().getBounds(),
			request = this.createXhrSearch(),
			query = [
				'option=com_hotspots',
				'task=json.search',
				'z=' + this.sb.getMap().getZoom(),
				'ne=' + bounds.getNorthEast().toUrlValue(),
				'sw=' + bounds.getSouthWest().toUrlValue(),
				'c=' + this.sb.getMap().getCenter().toUrlValue(),
				'offset=' + (offset ? offset : 0),
				'fs=' + (this.oldSearchWord !== searchWord ? 1 : 0),
				'format=raw'
			];

		this.oldSearchWord = searchWord;
		request.setOptions({
			url: this.options.baseUrl + "?" + query.join('&'),
			data: 'hs-language=' + Locale.getCurrent().name + '&search=' + searchWord,

			onSuccess: function (data) {
				if (!data.length) {
					data = [data];
				}

				// if we have boundaries, then we have results not in the current map view
				if(typeof data[0].boundaries != 'undefined')
				{
					// Remove the events as we don't want to trigger them when the map changes the bounds this time
					google.maps.event.removeListener(self.sb.mapListeners.zoom_changed);
					google.maps.event.removeListener(self.sb.mapListeners.dragend);

					self.sb.getMap().fitBounds(
						new google.maps.LatLngBounds(
							new google.maps.LatLng(data[0].boundaries.s,data[0].boundaries.w),
							new google.maps.LatLng(data[0].boundaries.n,data[0].boundaries.e)
						)
					);

					// Re-add the listeners
					self.sb.mapListeners.zoom_changed = google.maps.event.addListener(self.sb.getMap(), 'zoom_changed', function () {
						window.fireEvent('hotspotsDispatch');
					});

					self.sb.mapListeners.dragend = google.maps.event.addListener(self.sb.getMap(), 'dragend', function () {
						window.fireEvent('hotspotsDispatch');
					});
				}

				window.fireEvent('hotspotsLoadedHotspots', data);
				window.fireEvent('searchList', data);
			}
		}).send();
	},

	onHotspotsLoadedHotspots: function (locations) {

		var hotspots = locations.hotspots, self = this;

		// make sure that our object that contains the markers doesn't get out of hand...
		if (!self.deleteCat) {
			Object.each(this.sb.markers, function (marker, key) {
				if (marker.options.id != self.openedMarkerId) {
					marker.destroy();
					delete self.sb.markers[key];
				}
			});
		} else {
			Object.each(this.sb.markers, function (marker, key) {
				if (marker.options.id != self.openedMarkerId) {
					if (marker.options.catid == self.deleteCat) {
						marker.destroy();
						delete self.sb.markers[key];
					}
				}
			});
		}

		Object.each(hotspots, function (category, key) {

			Object.each(category, function (hotspot) {
				var position = new google.maps.LatLng(hotspot.latitude, hotspot.longitude);

				var category = this.options.categories[key];

				var icon = {
					'url': hotspot.icon ? hotspot.icon : category.cat_icon
				};

				var markerOptions = {
					'title': hotspot.title,
					'icon': icon
				};

				// create the marker only if it doesn't exist
				if (!this.sb.markers[hotspot.id]) {
					var marker = this.sb.createMarker(position, markerOptions, key, hotspot.id);
					var refInfoWindow = this.createInfoWindow(marker, hotspot);
					marker.addEvent('click', refInfoWindow);
				}

			}, this);
		}, this);

	},

	openUniqueInfoWindow: function (id) {
		this.sb.markers[id].fireEvent('click');
	},

	createInfoWindow: function (marker, hotspot) {
		return (function () {
			var	self = this;
			new Request.JSON({
				url: self.options.baseUrl + '?option=com_hotspots&view=hotspot&id=' + hotspot.id + '&format=raw',
				data: 'hs-language=' + Locale.getCurrent().name,
				onRequest: function () {
					var container = new Element('div', {
						style: 'position: absolute; top: 50%',
						html: self.translate('COM_HOTSPOTS_LOADING_DATA', 'Loading data')
					});

					self.infoWindow.setOptions({
						'content': container
					});
					self.infoWindow.open(self.getCurrentMapObj(), marker.markerObj);

					self.openedMarkerId = hotspot.id;
					self.infoWindowStatus = true;
				},
				onSuccess: function (json) {
					var text = new Element('div', {
						html: json.description
					});
					self.infoWindowToolbarActions(json).inject(text);

					self.infoWindow.setContent(text);
					self.infoWindow.open(self.getCurrentMapObj(), marker.markerObj);
					google.maps.event.trigger(self.infoWindow, 'content_changed');
				},
				onFailure: function (xhr) {
					var content = new Element('div', {
						html: self.translate('COM_HOTSPOTS_SOMETHING_IS_WRONG', 'Something is wrong')
							+ '<br /> Server response: ' + xhr.status + ' "' + xhr.statusText + '"'
							+ '<br /> If problem persist contact administrator'
					});
					self.infoWindow.setContent(content);
					google.maps.event.trigger(self.infoWindow, 'content_changed');

				}
			}).send();
		}.bind(this));
	},

	getCurrentMapObj: function () {
		var panorama = this.sb.getMap().getStreetView();
		return (panorama.getVisible()) ? panorama : this.sb.getMap();
	},

	infoWindowToolbarActions: function (hotspot) {
		var self = this;
		var readmore = '';
		var direction = '';
		var formDirection = '';
		var container = '';

		var links = new Element('div', {
			id: 'hotspots-links'
		});

		if (this.options.showDirections.toInt()) {

			direction = new Element('span', {
				id: 'getDirections',
				html: this.translate('COM_HOTSPOTS_JS_DIRECTIONS'),
				'class': 'link',
				events: {
					click: function () {
						document.id('direction-form').setStyle('display', 'block');
						google.maps.event.trigger(self.infoWindow.infoWindowObj, 'content_changed');
					}
				}
			});
		}

		var zoom = '';
		if (this.sb.getZoom() < 10 && this.options.showZoomButton.toInt()) {
			zoom = new Element('span', {
				'html': this.translate('COM_HOTSPOTS_ZOOM', 'zoom'),
				'class': 'link',
				events: {
					click: function () {
						self.sb.getMap().setCenter(new google.maps.LatLng(hotspot.latitude, hotspot.longitude));
						self.sb.setZoom(15);
					}
				}
			});
		}

		if (hotspot.readmore) {
			readmore = new Element('span', {
				'class': 'link'
			}).adopt(new Element('a', {
					href: hotspot.readmore,
					html: this.translate('COM_HOTSPOTS_READ_MORE', 'Read more')
				}));
		}

		if (this.options.showDirections.toInt()) {
			formDirection = new Element('form', {
				id: 'direction-form',
				'onsubmit': 'return false;'
			}).adopt([
					new Element('span', {
						'id': 'control-to',
						'class': 'link',
						html: this.translate('COM_HOTSPOTS_TO', 'to'),
						events: {
							click: function () {

								self.changeClass(this);
								this.set('class', 'active');

							}
						}
					}),
					new Element('span', {
						id: 'control-from',
						'class': 'active',
						html: this.translate('COM_HOTSPOTS_FROM', 'From'),
						events: {
							click: function () {
								self.changeClass(this);
								this.set('class', 'active');
							}
						}
					}),
					new Element('div', {
						'class': 'summaryLocation'
					}).adopt([
							new Element('input', {
								id: 'to'
							}),
							new Element('button', {
								id: 'hotspots-submit',
								'class': 'sexybutton',
								type: 'submit',
								events: {
									click: function () {
										self.getDirectionsFromInfoWindow();
									}
								}
							}).adopt(
									[new Element('span', {
										'html': '<span>' + this.translate('COM_HOTSPOTS_SUBMIT', 'Submit') + '</span>'
									})]),
							new Element('span', {
								'html': this.translate('COM_HOTSPOTS_CANCEL', 'Cancel'),
								'class': 'link',
								events: {
									click: function () {
										document.id('direction-form').setStyle('display', 'none');
										google.maps.event.trigger(self.infoWindow.infoWindowObj, 'content_changed');
									}
								}
							})
						])
				]);
		}

		if (direction || zoom || readmore || formDirection) {
			container = new Element('div', {
				id: 'hotspots-container'

			}).adopt([
					links.adopt([direction, zoom, readmore]),
					formDirection
				]);
		} else {
			//			create just a dummy container
			container = new Element('div');
		}

		return container;
	},

	getDirectionsFromInfoWindow: function () {
		var self = this;
		this.sb.getGeocoder().geocode({
			'latLng': self.infoWindow.getPosition()
		}, function (results, status) {
			var location = self.infoWindow.getPosition();
			var tabTo = document.id('control-to');
			var address = document.id('to').value;
			var departure = '';
			var arrival = '';

			if (status == google.maps.GeocoderStatus.OK) {
				location = results[0].formatted_address;
			}
			var forms = $$('.hotspots-tab-content form')
			forms.removeClass('active');
			document.id('search-directions').addClass('active');

			var actions = $$('.search-actions span');
			actions.removeClass('active');
			actions.each(function (action) {
				if (action.get('data-id') == 'search-directions') {
					action.addClass('active')
				}
			});

			if (tabTo.get('class') == 'active') {
				departure = self.infoWindow.getPosition();
				arrival = address;
				document.id('directions-arrival').set('value', arrival);
				document.id('directions-departure').set('value', location);
			} else {
				departure = address;
				arrival = self.infoWindow.getPosition();
				document.id('directions-arrival').set('value', location);
				document.id('directions-departure').set('value', departure);
			}
			document.id('directions-arrival').fireEvent('blur');
			document.id('directions-departure').fireEvent('blur');
			self.infoWindow.close();
			window.fireEvent('hotspotsSearchDirection', [departure, arrival])
			window.fireEvent('menuOpen', 'search');
		});
	}
});