/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("HotspotMapApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	Show.MapView = HotspotsManager.Common.MapApp.MapView;

	Show.Map = HotspotsManager.Common.MapApp.Map.extend({
		cssCache: {},
		events: {
			'click .js-hs-fullscreen-toggle': function (e) {
				e.stopPropagation();

				this.fullScreen();
			}
		},
		addCustomButtons: function () {
			// fullscreen
			var topRight = document.createElement('div');
			topRight.innerHTML = $('#hs-fullscreen-template').html();
			topRight.index = -1;
			this.mapObj.controls[google.maps.ControlPosition.TOP_RIGHT].push(topRight);
		},

		resize: function () {
			this.mapView.$el.css('height', HotspotsConfig.mapHeightSingle);

			google.maps.event.trigger(this.mapObj, 'resize');
		},

		fullScreen: function () {
			var self = this, $ = jQuery, $el = $(document.body);
			if (!$el.hasClass('hs-fullscreen')) {
				$el.addClass('hs-fullscreen');
				this.cssCache.height = self.$el.height();
				$el.css('overflow', 'hidden').addClass('hotspots-fullscreen');
				this.mapView.$el.height($(window).height());
			} else {
				$el.removeClass('hs-fullscreen');
				$el.css('overflow', 'auto').removeClass('hotspots-fullscreen');
				this.mapView.$el.height(self.cssCache.height);
			}
			google.maps.event.trigger(this.mapObj, 'resize');
		}
	});

	var map;
	HotspotsManager.reqres.setHandler("map:object", function () {
		return (function () {
			if (!map) {
				var model = HotspotsManager.request('map:entity');
				map = new Show.Map({model: model});
			}
			return map;
		}());
	});
});