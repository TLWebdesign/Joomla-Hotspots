/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("InfowindowApp.Show", function(Show, HotspotsManager, Backbone, Marionette, $, _){

	Show.InfoWindowView = Backbone.Marionette.ItemView.extend({
		tagName: 'div',
		className: 'hs-infowindow-marker',

		events: {
			'click .js-hs-close' : 'close',
			'click .js-hs-zoom-in': function(e){
				e.stopPropagation();
				HotspotsManager.trigger('marker:zoomIn', this.model);
			}
		},

		template: '#infowindow-marker',

		onShow: function() {
			this.$el.fadeIn();
		},

		close: function() {
			var self = this;
			this.$el.fadeOut(function() {
				self.model.deselect();
				Marionette.ItemView.prototype.close.apply(self, arguments);
			});
		}
	});
});