/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("TabsApp.List", function(List, HotspotsManager, Backbone, Marionette, $, _){

	List.TabsLayout = Marionette.Layout.extend({
		template: '#tabs-template',
		id: 'js-hs-tab-container',

		regions: {
			tabs: "#js-hs-tabs",
			tabContent: "#js-hs-tabs-content"
		}
	});

});