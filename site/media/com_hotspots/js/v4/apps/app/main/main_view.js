/**
 * Created by DanielDimitrov on 02.06.2014.
 */
HotspotsManager.module("AppApp.Main", function(Main, HotspotsManager, Backbone, Marionette, $, _){

	var mainRegion = Backbone.Marionette.Region.extend({
		el: '#js-hs-main-app',
		onShow: function() {
			var height = HotspotsConfig.mapHeight;
			// On small devices the specified mapHeight might be bigger than the height of the actual
			// window. So, we set a smaller height
			if(this.$window.height() <  HotspotsConfig.mapHeight ) {
				height = this.$window.height()
			}
			this.$el.css('height',  height + 'px');

			this.resize();
		},

		cssCache: {},

		initialize: function() {
			var self = this;
			this.$window = $(window);

			this.listenTo(HotspotsManager, 'map:fullscreen', function() {
				self.fullScreen();
			});

			$(window).resize(function() {
				self.resize();
				HotspotsManager.trigger('window:resize');
			});
		},

		resize: function() {
			if(this.$window.width() < 650) {
				$(document.body).addClass('hs-small-device');
			} else {
				$(document.body).removeClass('hs-small-device');
			}

			// if we are in fullscreen mode we need to change the region height as well
			if(this.$el.hasClass('hs-fullscreen'))
			{
				this.$el.height(this.$window.height());
			}
		},

		fullScreen: function() {
			var self = this, $=jQuery;
			if(!this.$el.hasClass('hs-fullscreen')) {
				this.$el.addClass('hs-fullscreen');
				this.cssCache.height = self.$el.height();
				$(document.body).css('overflow', 'hidden').addClass('hotspots-fullscreen');
				this.$el.height($(window).height());
			} else {
				this.$el.removeClass('hs-fullscreen');
				$(document.body).css('overflow', 'auto').removeClass('hotspots-fullscreen');
				this.$el.height(self.cssCache.height);
			}
		}
	});

	HotspotsManager.addRegions({
		mainRegion: new mainRegion,
		mapRegion: "#js-map-region",
		menuRegion: '#js-menu-region',
		infoWindowRegion: '#js-hs-infowindow-region'
	});

	Main.Layout = Marionette.Layout.extend({
		template: "#js-hs-main-region"
	});
});
