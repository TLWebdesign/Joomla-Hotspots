new Class('compojoom.hotspots.modules.hotspot', {
	Implements:[Options, Events, compojoom.hotspots.helper],

	initialize: function(hotspot,options, sb) {
		this.setOptions(options);
		this.sb = sb;

		window.addEvent('hotspotsDispatch:once', function() {
			this.infoWindow = Map.InfoBubble;
			this.infoWindow.setOptions({disableAutoPan: true, minWidth: 200, minHeight: 30, maxHeight: 350});
			this.createHotspot(hotspot);
		}.bind(this));
	},

	createHotspot: function(hotspot) {
		var position = new google.maps.LatLng(hotspot.latitude, hotspot.longitude);

		var markerOptions = {
			'title': hotspot.title
		};

		if(hotspot.icon.length) {
			markerOptions.icon = new google.maps.MarkerImage(hotspot.icon);
		}

		var marker = this.sb.createMarker(position, markerOptions);
		marker.setId(hotspot.id);
		var refInfoWindow = this.createInfoWindow(marker, hotspot);

		marker.addEvent('click', refInfoWindow);

		this.sb.setMapType();
		this.sb.setZoom(this.options.mapStartZoom.toInt());
	},

	createInfoWindow: function(marker, hotspot) {
		return (function() {
			var container = this.infoWindowToolbarActions(hotspot);
			var text = new Element('div', {
				style: 'width: 300px;',
				html: hotspot.description
			});
			container.inject(text);
			this.infoWindow.setOptions({
				'content': text ,
				'position':new google.maps.LatLng(hotspot.latitude, hotspot.longitude )
			});

			this.infoWindow.open(this.sb.getMap(),marker.markerObj);

		}.bind(this));
	},

	infoWindowToolbarActions: function(hotspot) {
		var self = this;
		var direction = '';
		var zoom = '';
		var container = '';

		var links = new Element('div', {
			id: 'hotspots-links'
		});

		if(this.options.showDirections.toInt()) {
			direction = new Element('span', {
				id: 'getDirections',
				html: this.translate('COM_HOTSPOTS_JS_DIRECTIONS','Directions'),
				'class' : 'link',
				events: {
					click: function() {
						document.id('direction-form').setStyle('display', 'block');
						google.maps.event.trigger(self.infoWindow.infoWindowObj, 'content_changed');
					}
				}
			});
		}

		if(this.sb.getMap().getZoom() < 10) {
			zoom = new Element('span', {
				'html' : this.translate('COM_HOTSPOTS_ZOOM','zoom'),
				'class' : 'link',
				events: {
					click: function(){
						self.sb.getMap().setCenter(new google.maps.LatLng(hotspot.latitude, hotspot.longitude));
                        self.sb.getMap().setZoom(10);
						self.infoWindow.close();
					}
				}
			});
		}


		if(this.options.showDirections.toInt()) {
			var formDirection = new Element( 'form', {
				id: 'direction-form',
				'onsubmit' : 'return false;'
			}).adopt([
				new Element('span' , {
					'id' : 'control-to',
					'class': 'link',
					html: this.translate('COM_HOTSPOTS_TO','to'),
					events: {
						click: function(){

							self.changeClass(this);
							this.set('class', 'active');

						}
					}
				}),
				new Element('span' , {
					id: 'control-from',
					'class': 'active',
					html: this.translate('COM_HOTSPOTS_FROM','from'),
					events : {
						click: function() {
							self.changeClass(this);
							this.set('class', 'active');
						}
					}
				}),
				new Element('div' , {
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
							click: function() {
								self.getDirectionsFromInfoWindow();
							}
						}
					}).adopt(
						[new Element('span', {
							'html': '<span>'+this.translate('COM_HOTSPOTS_SUBMIT','submit')+'</span>'
						})]),
					new Element('span', {
						'html': this.translate('COM_HOTSPOTS_CANCEL','cancel'),
						'class': 'link',
						events : {
							click: function() {
								google.maps.event.trigger(self.infoWindow.infoWindowObj, 'content_changed');
								document.id('direction-form').setStyle('display', 'none');
							}
						}
					})
				])
			]);
		}
		if(direction || zoom || formDirection) {
			container = new Element('div', {
				id: 'hotspots-container'

			}).adopt([
				links.adopt([direction,zoom]),
				formDirection
			]);
		} else {
			container = new Element('div');
		}

		return container;
	},

	getDirectionsFromInfoWindow:function () {
		var self = this;
		this.sb.getGeocoder().geocode({
			'latLng':self.infoWindow.getPosition()
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