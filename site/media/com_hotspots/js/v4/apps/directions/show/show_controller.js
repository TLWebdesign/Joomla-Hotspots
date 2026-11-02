/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("DirectionsApp.Show", function(Show, HotspotsManager, Backbone, Marionette, $, _){

	Show.Controller = {
		show: function() {
			var model = HotspotsManager.request('direction:entity');
			var view = new Show.DirectionsView({
				model: model
			});

			HotspotsManager.trigger('tabs:add', 1, 'directions', view);

			HotspotsManager.trigger('tabs:refresh');
		},

		search: function(origin, destination) {
			var model = HotspotsManager.request('direction:entity');
			model.set('origin', origin);
			model.set('destination', destination);

			var view = new Show.DirectionsView({
				model: model
			});

			HotspotsManager.trigger('tabs:add', 1, 'directions', view);

			HotspotsManager.trigger('tabs:refresh');


			view.searchDestination();
			HotspotsManager.trigger('tabs:change', 'directions');
		}
	};
});

