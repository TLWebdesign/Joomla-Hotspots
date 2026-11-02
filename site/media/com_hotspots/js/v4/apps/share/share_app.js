/**
 * Created by DanielDimitrov on 06.06.2014.
 */
HotspotsManager.module("ShareApp", function(ShareApp, HotspotsManager, Backbone, Marionette, $, _){

	var API = {
		mail: function() {
			ShareApp.Mail.Controller.show();
		}
	};

	HotspotsManager.on('share:mail', function() {
		API.mail();
	});
});