/**
 * Created by DanielDimitrov on 02.06.2014.
 */
HotspotsManager.module("AppApp", function (AppApp, HotspotsManager, Backbone, Marionette, $, _) {

	// init the layout before anything else
	HotspotsManager.on('initialize:before', function (options) {
		var layout = new AppApp.Main.Layout();
		layout.on('show', function() {
			// Start the map in fullscren if that is necessary
			if(HotspotsConfig.startFullscreen) {
				HotspotsManager.trigger('map:fullscreen');
			}
		});
		HotspotsManager.mainRegion.show(layout);
	});
});