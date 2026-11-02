/**
 * Created by DanielDimitrov on 24.03.14.
 */

HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){
	Entities.Tile = Backbone.Model.extend({
		url: function (params) {
			var query = [
				'option=com_hotspots',
				'task=hotspots.hotspot',
				'format=json',
				'id=' + this.get('id'),
				'hs-language=' + HotspotsConfig.language
			];

			return HotspotsConfig.baseUrl + '?' + query.join('&');
		}
	});

	var collection = HotspotsManager.request("marker:collection");
	HotspotsManager.on("tiles:mousemove", function(coord, tileCoordinate) {
		collection.coord  = {x: tileCoordinate.x, y: tileCoordinate.y};
		collection.fetch({
			success: function(collection, response, options) {
				HotspotsManager.trigger('tiles:markers', collection);
			}
		});
	});

	this.listenTo(HotspotsManager, "tile:selected", function(model, isSelected) {
		var tile = new Entities.Tile(model.toJSON());
		tile.fetch({success: function(model, response, options) {
			HotspotsManager.trigger('tile:hotspot:loaded', model);

		}});
	});
});
