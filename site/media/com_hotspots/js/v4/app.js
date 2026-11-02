/**
 * Created by DanielDimitrov on 26.02.14.
 */
var HotspotsManager = new Marionette.Application();

HotspotsManager.navigate = function (route, options) {
	options || (options = {});
	Backbone.history.navigate(route, options);
};

HotspotsManager.Router = Marionette.AppRouter.extend({
	routes: {
		':lat/:lng/:z(/cats/:cats)(/page/:page)(/search/:search)(/hotspot/:hotspot)' : 'init'
	},

	init: function(lat, lng, z, cats, page, search, hotspot) {
		var filter = HotspotsManager.request('filter:entity');

		HotspotsManager.trigger('map:coordinates', lat, lng, z);

		if(cats) {
			var catCollection = HotspotsManager.request('category:entities');
			var catIds = cats.split(';');
			catCollection.deselectAll();
			_.each(catIds, function(value) {
				catCollection.select(catCollection.get(value));
			});
		}

		if(page) {
			filter.set('page', parseInt(page, 10))
		}

		if(search) {
			filter.set('search', search);
		}

		if(hotspot) {
			filter.set('hotspot:selected', hotspot);
			var collection = HotspotsManager.request('hotspots:collection');
			collection.once('sync', function() {
				var model = collection.get(hotspot);

				HotspotsManager.trigger('hotspot:selected', model);
			});
		}
	}
});

HotspotsManager.getCurrentRoute = function () {
	return Backbone.history.fragment
};

HotspotsManager.on('initialize:after', function (options) {
	Backbone.history.start();
});

HotspotsManager.addInitializer(function(){
	new HotspotsManager.Router();
});

jQuery(document).ready(function () {
	HotspotsManager.start();
});
