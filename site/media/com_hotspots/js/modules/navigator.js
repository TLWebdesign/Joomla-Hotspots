new Class('compojoom.hotspots.modules.navigator', {
	Implements: [Options, Events, compojoom.hotspots.helper],
	options: {

	},
	allTabs: false,

	eventsMap: [
		{
			host: window,
			events: {
				hotspotsDispatch: null,
				route: null,
				allCatTabs: null
			}
		}
	],

	initialize: function (options, sb) {
		this.setOptions(options);

		this.exportAllEvents();

		// Don't fire the hashchange too early, otherwise we get out of bounds errors
		google.maps.event.addListenerOnce(sb.getMap(), 'idle', function () {
			window.onhashchange = function () {
				this.onHotspotsDispatch();
			}.bind(this);
		}.bind(this));
	},

	onRoute: function (type, id) {
		var url = new URI(window.location);
		if (type == 'category') {
			if (this.allTabs) {
				var cats = this.gup('catid').split(';');
				if (cats.contains('-1')) {
					cats.erase('-1');
				}
				if (id instanceof Array) {
					url.set('fragment', '!/catid=' + id.join(';'));
				} else {
					if (!cats.contains(id)) {
						cats.push(id)
					}
					url.set('fragment', '!/catid=' + cats.join(';'));
				}

			} else {
				url.set('fragment', '!/catid=' + id);
			}
		}

		if (type == 'search') {
			url.set('fragment', '!/search=' + id);
		}
		url.go();
	},

	onHotspotsDispatch: function () {
		if (window.location.hash) {
			var hash = window.location.hash;

			if (hash.indexOf('catid') != -1) {
				if (this.gup('catid') == -1) {
					// if had open tabs we now need to also delete the markers on the map
					window.fireEvent('hotspotsLoadedHotspots', {});
				} else if (this.gup('catid') != -1) {
					window.fireEvent('hotspotsLoadCategory', this.gup('catid'));
				} else {
					window.fireEvent('menuOpen');
				}
			} else if (hash.indexOf('search') != -1) {
				var searchWord = this.gup('search').trim();
				window.fireEvent('hotspotsSearchHotspot', searchWord);

			}
		}
	},

	onAllCatTabs: function (value) {
		this.allTabs = value;
	}

});