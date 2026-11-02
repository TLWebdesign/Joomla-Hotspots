/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("MenuApp", function(MenuApp, HotspotsManager, Backbone, Marionette, $, _){
	var API = {
		show: function() {
			MenuApp.Show.Controller.show();
		},
		open: function() {
			MenuApp.Show.Controller.open();
		},
		resize: function() {
			MenuApp.Show.Controller.resize();
		}
	};

	this.listenTo(HotspotsManager, 'initialize:before', function(options) {
		API.show();
	});

	this.listenTo(HotspotsManager, 'marker:selected', function(model) {
		API.open();
	});

	this.listenTo(HotspotsManager, 'window:resize', function() {
		API.resize();
	})

});