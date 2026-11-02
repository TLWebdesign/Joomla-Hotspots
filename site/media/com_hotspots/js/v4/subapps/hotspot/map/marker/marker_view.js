/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("HotspotMapApp.Marker", function(Marker, HotspotsManager, Backbone, Marionette, $, _){

	Marker.MarkerView = Backbone.GoogleMaps.MarkerView.extend({
		beforeRender: function() {
			this.gOverlay.setIcon(this.model.get('icon'));
		},
		openDetail: function() {}
	});
});