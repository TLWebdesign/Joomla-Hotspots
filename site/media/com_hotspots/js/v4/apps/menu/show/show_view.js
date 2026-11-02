/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("MenuApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	Show.MenuView = Marionette.ItemView.extend({
		tagName: 'div',
		className: 'slide_menu',
		id: 'slide_menu',

		template: '#menu-layout-template',

		events: {
			'click .toggle': 'toggleMenu'
		},

		toggleMenu: function () {
			var self = this;
			if (this.opened) {
				this.opened = false;
				this.$el.animate({'marginLeft': 0});
				this.$el.find('#toggle-menu').switchClass('toggle-on', 'toggle-off');
				HotspotsManager.trigger('menu:slide', 0, self);
			}
			else {
				this.$el.animate(
					{
						'marginLeft': '-' + this.$el.width()
					},
					{
						complete: function () {
							HotspotsManager.trigger('menu:slide', 1, self);
						}
					}
				);
				this.opened = true;
				this.$el.find('#toggle-menu').switchClass('toggle-off', 'toggle-on');
			}
		},

		initialize: function() {
			this.listenTo(HotspotsManager, 'map:fullscreen', function() {
				this.resize();
			});

		},

		resize: function() {
			if($(document.body).hasClass('hs-small-device')) {
				var height = HotspotsConfig.mapHeightSmall;
				if ($(window).height() < 480) {
					height = 150;
				}
				this.$el.css('height', HotspotsManager.mainRegion.$el.height()-height);
				HotspotsManager.menuRegion.$el.css('top', height);
			} else {
				this.$el.css('height', HotspotsManager.mainRegion.$el.height());
				HotspotsManager.menuRegion.$el.css('top', 0);
			}

			HotspotsManager.trigger('menu:resized', this);
		},

		onShow: function () {
			if(!HotspotsConfig.startClosedMenu) {
				this.toggleMenu();
			}
			this.resize();
		}
	});
});
