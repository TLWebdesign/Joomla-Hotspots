/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("DirectionsApp.Show", function(Show, HotspotsManager, Backbone, Marionette, $, _){

	Show.Controller = {
		show: function() {
			var model = HotspotsManager.request('direction:entity'),
				markerModel = HotspotsManager.request('marker:entity');

			var view = new Show.DirectionsView({
				model: model,
				markerModel: markerModel
			});

			var region = new Marionette.Region({
				el: '#js-hs-item-direction-region'
			});

			region.show(view);
		},

		search: function(origin, destination) {
			var model = HotspotsManager.request('direction:entity');
			model.set('origin', origin);
			model.set('destination', destination);

			var view = new Show.DirectionsView({
				model: model
			});

			view.searchDestination();
		}
	};
});

