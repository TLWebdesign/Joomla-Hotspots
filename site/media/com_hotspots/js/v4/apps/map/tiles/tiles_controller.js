/**
 * Created by DanielDimitrov on 12.05.14.
 */
HotspotsManager.module("MapApp.Tiles", function (Tiles, HotspotsManager, Backbone, Marionette, $, _) {

	var Controller = Marionette.Controller.extend({
		show: function() {
			new Tiles.TilesView();
		},

		renderTilesMarkers: function(collection) {
			if(typeof this.collection == "undefined") {
				this.collection = new HotspotsManager.MapApp.Markers.Location();
			}

			this.collection.add(collection.toJSON());

			if(!this.rendered) {
				var map = HotspotsManager.request("map:object");
				var markerCollectionView = new Tiles.MarkerTilesCollectionView({
					collection: this.collection,
					map: map.mapObj
				});
				markerCollectionView.render();
				this.rendered = true;
			}

		},
		tileMarkerClosed: function(model) {
			var deselected = this.collection.get(model.get('id'));
			deselected.deselect();
		}
	});

	Tiles.Controller = new Controller();
});