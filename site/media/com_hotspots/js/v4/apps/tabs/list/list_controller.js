/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("TabsApp.List", function(List, HotspotsManager, Backbone, Marionette, $, _){

	List.Controller = {
		tabs: null,
		initTabs: function() {
			if(!this.tabs) {
				// hack: http://stackoverflow.com/a/28683238/471574
				jQuery.ui.tabs.prototype._isLocal = function() { return true; };
				this.tabs = $("#js-hs-tab-container").tabs();
			}

			return this.tabs;
		},
		create: function() {
			var tabCollection = HotspotsManager.request("tab:entities");

			var layout = new List.TabsLayout();

			var region = new Backbone.Marionette.Region({
				el: "#js-tabs-region"
			});

			var tabView = new HotspotsManager.TabsApp.TabItem.View({
				collection: tabCollection
			});

			var tabContentView = new HotspotsManager.TabsApp.TabContentItem.View({
				collection: tabCollection
			});

			region.show(layout);

			layout.tabs.show(tabView);
			layout.tabContent.show(tabContentView);

			this.initTabs();

			HotspotsManager.trigger('tabs:ready');
		},
		refresh: function() {
			this.initTabs().tabs('refresh');
		},
		change: function(name) {
			var $lis, $li, index;

			$lis = this.tabs.find('#js-hs-tabs ul li');
			$li = this.tabs.find("#js-hs-tabs ul li[data-name='"+name+"']");
			index = $lis.index($li);

			this.initTabs().tabs('option', 'active', index)
		}
	};
});
