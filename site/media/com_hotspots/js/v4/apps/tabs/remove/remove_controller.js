/**
 * Created by DanielDimitrov on 10.06.2014.
 */
/**
 * Created by DanielDimitrov on 24.03.14.
 */
HotspotsManager.module("TabsApp.Remove", function(Remove, HotspotsManager, Backbone, Marionette, $, _){

	Remove.Controller = {
		remove: function(dataName) {
			var tabs = HotspotsManager.request("tab:entities");

			tabs.remove(tabs.where({dataName: dataName}));
		}
	}
});