/**
 * Created by DanielDimitrov on 23.03.14.
 */

HotspotsManager.module("TabsApp.TabContentItem", function(TabContentItem, HotspotsManager, Backbone, Marionette, $, _){

	TabContentItem.Item = Marionette.ItemView.extend({
		tagName: "div",
		template: "#tab-content-template",
		className: 'hs-tab-content',

		initialize: function() {
			this.$el.attr('id', 'tabs-' + this.model.get('id'));
		},

		modelEvents: {
			'change:content' : 'onShow'
		},

		onShow: function() {
			if(typeof this.model.get('content') === 'object') {
				var region = new Backbone.Marionette.Region({
					el: "#hs-tab-content-region-" + this.model.get('id')
				});

				region.show(this.model.get('content'));
			}
		}
	});

	TabContentItem.View = Marionette.CollectionView.extend({
		itemView: TabContentItem.Item,
		itemViewContainer: "div",

		initialize: function() {
			var self = this;

			this.listenTo(HotspotsManager, 'map:fullscreen', function() {
				self.resize();
			});

			this.listenTo(HotspotsManager, 'window:resize', function() {
				self.resize();
			});
			this.listenTo(HotspotsManager, 'menu:resized', function() {
				self.resize();
			});
		},

		onShow: function() {
			this.resize();
		},

		resize: function() {
			this.$el.height(HotspotsManager.mainRegion.$el.height()- this.$el.position().top - HotspotsManager.menuRegion.$el.position().top);
		}
	});

});