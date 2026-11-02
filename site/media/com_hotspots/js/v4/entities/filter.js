/**
 * Created by DanielDimitrov on 24.03.14.
 */

HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){
	Entities.Filter = Backbone.Model.extend({
		defaults: {
			search: '',
			total: 0
		},
		initialize: function() {
			var cats = HotspotsManager.request('category:entities');
			this.set('cats',cats.selected);
		},

		getCatIds: function() {
			var cats = [];

			_.each(this.get('cats'), function (value) {
				cats.push(value.get('id'));
			});

			return cats;
		}
	});


	var API = {
		filter: false,
		getFilterEntity: function(options) {
			if(!this.filter) {
				this.filter = new Entities.Filter(options);
			}

			return this.filter;
		}
	};

	HotspotsManager.reqres.setHandler("filter:entity", function(options) {
		return API.getFilterEntity(options);
	});

	this.listenTo(HotspotsManager, "map:init", function(map) {
		var filter = API.getFilterEntity();
		var b = map.getBounds();

		filter.set('level', map.getZoom());
		filter.set('ne', b.getNorthEast().toUrlValue());
		filter.set('sw', b.getSouthWest().toUrlValue());
		filter.set('center', map.getCenter());
		filter.set('zoom', map.getZoom());
		filter.set('offset', false);

		HotspotsManager.trigger('filter:changed', filter);
	});

	this.listenTo(HotspotsManager, "map:changedposition", function(map) {
		var filter = API.getFilterEntity();
		var b = map.getBounds();

		filter.set('level', map.getZoom());
		filter.set('ne', b.getNorthEast().toUrlValue());
		filter.set('sw', b.getSouthWest().toUrlValue());
		filter.set('center', map.getCenter());
		filter.set('zoom', map.getZoom());
		filter.unset('page');

		HotspotsManager.trigger('filter:changed', filter);
		HotspotsManager.trigger('filter:updated', filter);
	});

	this.listenTo(HotspotsManager, "marker:selected", function(model, isSelected) {
		var filter = API.getFilterEntity();
		filter.set('hotspot:selected', model.get('id'));
		HotspotsManager.trigger('filter:updated', filter);
	});

	this.listenTo(HotspotsManager, 'hotspots:page', function(page) {
		var filter = API.getFilterEntity();
		filter.set('page', page);
		filter.unset('hotspot:selected');
		HotspotsManager.trigger('filter:updated', filter);
	});

	this.listenTo(HotspotsManager, "filter:search", function() {
		var filter = API.getFilterEntity();
		HotspotsManager.trigger('filter:updated', filter);
	});

	this.listenTo(HotspotsManager, "search:newboundaries", function(n, e, s, w) {
		var filter = API.getFilterEntity();

		filter.set('ne', b.getNorthEast().toUrlValue());
		filter.set('sw', b.getSouthWest().toUrlValue());

		HotspotsManager.trigger('filter:updated', filter);
	})

});
