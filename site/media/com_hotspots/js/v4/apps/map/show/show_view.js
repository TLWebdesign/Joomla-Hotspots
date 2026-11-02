/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("MapApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	Show.MapView = HotspotsManager.Common.MapApp.MapView;

	Show.Map = HotspotsManager.Common.MapApp.Map.extend({
		resize: function() {
			var height = HotspotsConfig.mapHeightSmall;
			if($(document.body).hasClass('hs-small-device')) {
				this.$el.css('width', '100%');
				if ($(window).height() < 480) {
					height = 150;
				}
				this.mapView.$el.css('height', height);
			} else {
				this.$el.css('width', '100%').css('width', '-='+this.widthRestriction+'px');
				this.mapView.$el.css('height', HotspotsManager.mainRegion.$el.height());
			}
			google.maps.event.trigger(this.mapObj, 'resize');
		}
	});

	var map;
	HotspotsManager.reqres.setHandler("map:object", function(){
		return (function(){
			if(!map){
				var model = HotspotsManager.request('map:entity');
				map = new Show.Map({model: model});
			}
			return map;
		}());
	});
});
