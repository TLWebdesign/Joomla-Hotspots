new Class('compojoom.hotspots.modules.latesthotspots', {
	Extends: compojoom.hotspots.modules.hotspot,
	Implements: [Options, Events, compojoom.hotspots.helper],

	isMenuOpen: false,
	fullScreen: false,
	options: {
		mailMap: 1
	},
	xhr: {},


	initialize: function (hotspots, options, sb) {
		this.parent(options, sb);
		var self = this;
		window.addEvent('hotspotsDispatch:once', function () {
			window.fireEvent('hotspotsLoadedHotspots', hotspots);
			self.sb.zoomToMarkers();
		})

	},

	infoWindowToolbarActions: function (hotspot) {
		var self = this, readmore = '', container = '', admin = '';

		var links = new Element('div', {
			id: 'hotspots-links'
		});

		readmore = new Element('span', {
			'class': 'link'
		}).adopt(new Element('a', {
				href: hotspot.readmore,
				html: this.translate('COM_HOTSPOTS_READ_MORE', 'Read more'),
				target: '_blank'
			}));

		admin = new Element('span', {
			'class': 'link'
		}).adopt(new Element('a', {
				href: this.options.baseUrl + 'administrator/index.php?option=com_hotspots&task=hotspot.edit&id=' + hotspot.id,
				html: this.translate('JTOOLBAR_EDIT', 'Edit'),
				target: '_blank'
			}));


		container = new Element('div', {
			id: 'hotspots-container'

		}).adopt([
				links.adopt([readmore, admin])
			]);

		return container;
	},

	createInfoWindow: function (marker, hotspot) {
		return (function () {
			var container = this.infoWindowToolbarActions(hotspot),
				self = this;
			new Request.JSON({
				url: self.options.rootUrl + 'index.php?option=com_hotspots&view=hotspot&id=' + hotspot.id + '&format=raw',
				onRequest: function () {
					var container = new Element('div', {
						style: 'position: absolute; top: 50%',
						html: self.translate('COM_HOTSPOTS_LOADING_DATA', 'Loading data')
					});

					self.infoWindow.setOptions({
						'content': container,
						'position': new google.maps.LatLng(hotspot.latitude, hotspot.longitude)
					});
					self.infoWindow.open(self.sb.getMap(), marker.markerObj);
				},
				onSuccess: function (json) {
					var text = new Element('div', {
						style: 'width: 300px; height: 140px; overflow-y: auto;',
						html: json.description
					});
					text.adopt(self.infoWindowToolbarActions(json));
					self.infoWindow.setContent(text);
					self.infoWindow.open(self.getCurrentMapObj(), marker.markerObj);
					google.maps.event.trigger(self.infoWindow.infoWindowObj, 'content_changed');

				},
				onFailure: function (xhr) {
					var content = new Element('div', {
						html: this.translate('COM_HOTSPOTS_SOMETHING_IS_WRONG', 'Something is wrong')
							+ '<br /> Server response: ' + xhr.status + ' "' + xhr.statusText + '"'
							+ '<br /> If problem persist contact administrator'
					});
					self.infoWindow.setContent(content);
					google.maps.event.trigger(self.infoWindow, 'content_changed');
				}
			}).send();
		}.bind(this));
	},

	onHotspotsLoadedHotspots: function (locations) {

		var hotspots = locations.hotspots, self = this;

		// make sure that our object that contains the markers dosn't get out of hand...
		if (Object.keys(this.sb.markers).length > 2000) {
			Object.each(this.sb.markers, function (marker, key) {
				marker.destroy();
				delete self.sb.markers[key];
			});
		}

		Object.each(this.sb.markers, function (marker, key) {
				diff.each(function (value) {
					if (marker.options.catid == value) {
						marker.destroy();
						delete self.sb.markers[key];
					}
				});
			}
		);


		Object.each(hotspots, function (category, key) {
			Object.each(category, function (hotspot) {
				var position = new google.maps.LatLng(hotspot.latitude, hotspot.longitude);
				var category = this.options.categories.filter(function(item) {
					return item.id==key;
				})[0];

				var icon = new google.maps.MarkerImage((hotspot.icon) ? hotspot.icon : (category.cat_icon));

				var markerOptions = {
					'title': hotspot.title,
					'icon': icon
				};

				var marker = this.sb.createMarker(position, markerOptions, key, hotspot.id);

				var refInfoWindow = this.createInfoWindow(marker, hotspot);

				marker.addEvent('click', refInfoWindow);
			}, this);
		}, this);

	}
});