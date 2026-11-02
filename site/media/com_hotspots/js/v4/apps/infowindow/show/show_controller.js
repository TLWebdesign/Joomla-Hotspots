/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("InfowindowApp.Show", function (Show, HotspotsManager, Backbone, Marionette, $, _) {

	Show.Controller = {
		/**
		 * When a marker is selected we need to trigger the select on the hotspots collection
		 * in the list as well
		 *
		 * @param model
		 * @param isSelected
		 */
		selected: function (model, isSelected) {
			var infowindow = new Show.InfoWindowView({
				model: model
			});
			HotspotsManager.infoWindowRegion.show(infowindow);
		}
	}
});
