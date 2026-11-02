/**
 * Created by DanielDimitrov on 25.03.14.
 */
HotspotsManager.module("InfowindowApp", function(InfowindowApp, HotspotsManager, Backbone, Marionette, $, _){

	var API = {
		show: function(model) {
			InfowindowApp.Show.Controller.selected(model);
		}
	};

	this.listenTo(HotspotsManager, 'marker:selected', function(model) {
		API.show(model);
	});

	this.listenTo(HotspotsManager, 'tile:selected', function(model) {
		API.show(model);
	});
});