/**
 * Created by DanielDimitrov on 24.03.14.
 */

HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){
	Entities.Mail = Backbone.Model.extend({
		defaults: {
			mailto: '',
			sender: '',
			message: '',
			sent: false,
			url: Backbone.history.location.href
		},

		validation: {
			mailto: {
				required: true,
				format: 'email',
				message: 'Does not match format'
			},
			message: {
				required: true,
				minLength: 10
			},
			subject: {
				required: true,
				minLength: 5
			}
		},

		url: function() {
			return HotspotsConfig.rootUrl + '?option=com_hotspots&task=mail.send&format=raw'
		},

		sync: _.wrap(Backbone.sync, function(sync, method, model, options){
			this.set('url', Backbone.history.location.href);
			var newOptions = _.extend(options, {emulateJSON: true, emulateHTTP: true});
			return sync(method, model, newOptions);
		})
	});

	var API = {
		getMail: function() {
			return new Entities.Mail();
		}
	};

	HotspotsManager.reqres.setHandler("mail:entity", function() {
		return API.getMail();
	});
});
