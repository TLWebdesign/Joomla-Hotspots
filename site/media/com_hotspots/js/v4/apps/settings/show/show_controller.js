/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("SettingsApp.Show", function(Show, HotspotsManager, Backbone, Marionette, $, _){

	Show.Controller = {
		show: function() {
			var model = HotspotsManager.request('map:entity');
			var view = new Show.SettingsView({
				model: model
			});

			HotspotsManager.trigger('tabs:add', 3, 'settings', view);

			HotspotsManager.trigger('tabs:refresh');

		}
	};
});

