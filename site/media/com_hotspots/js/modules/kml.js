new Class('compojoom.hotspots.modules.kml', {
	Implements: [Options, Events, compojoom.hotspots.helper],

	options: {},
	xhr: {},
	executed: {},
	layers: [],

	eventsMap: [
		{
			host: window,
			events: {
				loadedKmls: null,
				hotspotsLoadedHotspots: 'getKmls'
			}
		}
	],

	initialize: function (options, sb) {
		this.sb = sb;
		this.setOptions(options);
		this.exportAllEvents();
	},

	/**
	 * we use a small trick here - since we load all the kmls for a particular category at once
	 * we don't need to ask the server each time to serve us the KML - just one request is enough!
	 * @param params
	 */
	getKmls: function (params) {
		var bounds = this.sb.getMap().getBounds(),
			categories = this.gup('catid'),
			currentCat = categories.replace(';', '_');

		if (typeof params == 'undefined') {
			params = {};
		}

		if (!this.executed[currentCat]) {
			if (typeof params.categories != 'undefined') {
				categories = params.categories;
			}
			var query = [
				'option=com_hotspots',
				'view=kmls',
				'cat=' + categories,
				'level=' + this.sb.getMap().getZoom(),
				'ne=' + bounds.getNorthEast().toUrlValue(),
				'sw=' + bounds.getSouthWest().toUrlValue(),
				'format=json'
			];

			this.xhr = new Request.JSON({
				url: this.options.baseUrl + "?" + query.join('&'),
				data: 'lang=' + Locale.getCurrent().name,
				link: 'cancel',
				onRequest: function () {
					window.fireEvent('hotspotsLoaderStart');
				}.bind(this),
				onSuccess: function (data) {
					this.executed[currentCat] = data;
					window.fireEvent('loadedKmls', data);
				}.bind(this),
				onComplete: function () {
					window.fireEvent('hotspotsLoaderStop');
				}.bind(this)
			});

			this.xhr.send();
		}
		else {
			window.fireEvent('loadedKmls', this.executed[currentCat]);
		}
	},

	onLoadedKmls: function (kmls) {
		var self = this;

		// walk through the kmls in the layers array and remove them from the map
		self.layers.each(function(layer) {
			layer.setMap(null);
		});
		self.layers.empty();

		Object.each(kmls, function (kml) {
			kml.each(function (value) {
				var kmlLayer = new google.maps.KmlLayer(value.file, {preserveViewport: true});
				kmlLayer.setMap(self.sb.getMap());

				// add the kmlLayer to the array
				self.layers.push(kmlLayer);
			})
		});
	}
});