/**
 * Created by DanielDimitrov on 24.03.14.
 */
HotspotsManager.module("HotspotsApp.Tabs", function (Tabs, HotspotsManager, Backbone, Marionette, $, _) {

	Tabs.Controller = {
		render: function () {
			this.hotspotsListLayot = HotspotsManager.HotspotsApp.List.Controller.init();

			HotspotsManager.trigger('tabs:add', 2, 'hotspots', this.hotspotsListLayot);
			HotspotsManager.trigger('tabs:refresh');
		}
	}
});