/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("MapApp.Markers", function(Markers, HotspotsManager, Backbone, Marionette, $, _){

	Markers.MarkerView = Backbone.GoogleMaps.MarkerView.extend({
		initialize: function () {

			this.model.on('change:highlighted', function(model, isSelected) {
				if (isSelected) {
					if(!model.get('selected')) {
					// Instantiate marker, with user defined properties
						this.highligtedMarker = new google.maps.Marker(_.extend({
							position: this.model.getLatLng(),
							map: this.map,
							icon: HotspotsConfig.rootUrl + '/media/com_hotspots/images/v4/icons/pin-highlight.png',
							zIndex: 9999
						}));
					}
				}
				else {
					this.highligtedMarker && this.highligtedMarker.setMap(null);
				}
			}, this);
			// Show detail view on model select
			this.model.on("change:selected", function(model, isSelected) {
				if (isSelected) {
					var self = this;
					this.highligtedMarker && this.highligtedMarker.setMap(null);
					this.gOverlay.setIcon(HotspotsConfig.rootUrl + '/media/com_hotspots/images/v4/icons/pin.png');
					this.gOverlay.setZIndex(9999);
					this.gOverlay.setAnimation(google.maps.Animation.BOUNCE);
					setTimeout(function() {
						self.gOverlay.setAnimation(null);
					}, 2000);
					HotspotsManager.trigger('marker:selected', model, isSelected);
				}
				else {
					this.closeDetail();
					this.gOverlay.setIcon(this.model.get('icon'));
					this.gOverlay.setAnimation(null);
				}
			}, this);
		},

		beforeRender: function() {
			this.gOverlay.setIcon(this.model.get('icon'));
		},
		openDetail: function() {}
	});

	Markers.MarkerCollectionView = Backbone.GoogleMaps.MarkerCollectionView.extend({
		markerView: Markers.MarkerView
	});


});