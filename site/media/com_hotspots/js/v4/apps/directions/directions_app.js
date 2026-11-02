/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("DirectionsApp", function(DirectionsApp, HotspotsManager, Backbone, Marionette, $, _){
	var API = {
		show: function() {
			DirectionsApp.Show.Controller.show();
		},
		search: function(origin, destination) {
			DirectionsApp.Show.Controller.search(origin, destination);
		}
	};

	HotspotsManager.on('directions:search', function(origin, destination) {
		API.search(origin, destination);
	});

	HotspotsManager.on('tabs:ready', function() {
		API.show();
	});
});