/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("SettingsApp", function(SettingsApp, HotspotsManager, Backbone, Marionette, $, _){

	var API = {
		show: function() {
			SettingsApp.Show.Controller.show();
		}
	};

	HotspotsManager.on('tabs:ready', function(options) {
		API.show();
	});
});