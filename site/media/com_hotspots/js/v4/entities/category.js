/**
 * Created by DanielDimitrov on 24.03.14.
 */

HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){
	Entities.Category = Backbone.Model.extend({
		initialize: function() {
			var selectable = new Backbone.Picky.Selectable(this);
			_.extend(this,selectable);
		}
	});

	Entities.CategoryCollection = Backbone.Collection.extend({
		model: Entities.Category,

		initialize: function() {
			var MultiSelect = new Backbone.Picky.MultiSelect(this);
			_.extend(this, MultiSelect);
		}
	});

	var API = {
		active: false,

		getCategoryEntities: function() {
			if(!this.active) {
				this.active = new Entities.CategoryCollection(HotspotsConfig.categories);

				HotspotsConfig.startCat.forEach(function(value) {
					this.active.select(this.active.get(value));
				}, this);
			}

			return this.active;
		}
	};

	HotspotsManager.reqres.setHandler("category:entities", function() {
		return API.getCategoryEntities();
	});
});
