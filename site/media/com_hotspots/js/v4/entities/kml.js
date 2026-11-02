/**
 * Created by DanielDimitrov on 24.03.14.
 */

HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){
	Entities.Kml = Backbone.Model.extend({

	});

	Entities.KmlCollection = Backbone.Collection.extend({
		model: Entities.Kml,

		url: function() {

			var map = HotspotsManager.request('map:object'),
				filter = HotspotsManager.request('filter:entity'),
				search = filter.get('search'),
				params = [
					'cat=' + filter.getCatIds().join(';'),
					'level=' + filter.get('level'),
					'ne=' + filter.get('ne'),
					'sw=' + filter.get('sw'),
					'hs-language=' + HotspotsConfig.language
				];

			return HotspotsConfig.baseUrl + '?option=com_hotspots&view=kmls&format=json&' + params.join('&');
		}
	});


	var collection = new Entities.KmlCollection();
	var cats = '', needUpdate = true;
	HotspotsManager.on("filter:changed", function() {

		if(needUpdate) {
			collection.fetch({
				success: function(collection, response, options) {
					HotspotsManager.trigger('kmls:fetched', collection);
				}
			});
		}
		needUpdate = false;

	});

	HotspotsManager.on('initialize:after', function() {
		cats = HotspotsManager.request('category:entities');

		cats.on('selected', function() {
			needUpdate = true;
		});
		cats.on('deselect', function() {
			needUpdate = true;
		});
	})
});
