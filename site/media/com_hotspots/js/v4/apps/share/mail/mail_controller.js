/**
 * Created by DanielDimitrov on 06.06.2014.
 */
HotspotsManager.module("ShareApp.Mail", function(Mail, HotspotsManager, Backbone, Marionette, $, _){

	Mail.Controller = {
		show: function() {
			var model = HotspotsManager.request('mail:entity');
			var view = new Mail.Item({model: model});

			HotspotsManager.trigger('tabs:add', 4, '<span class="fa fa-envelope"></span>Send Map', view, 'mail');
			HotspotsManager.trigger('tabs:refresh');
			HotspotsManager.trigger('tabs:change', 'mail');

		}
	};
});

