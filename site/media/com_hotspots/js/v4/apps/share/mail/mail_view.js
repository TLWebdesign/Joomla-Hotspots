/**
 * Created by DanielDimitrov on 06.06.2014.
 */

HotspotsManager.module("ShareApp.Mail", function (Mail, HotspotsManager, Backbone, Marionette, $, _) {

	Mail.Item = Marionette.ItemView.extend({
		tagName: "div",
		template: "#js-hs-send-map-template",

		events: {
			'click .js-hs-cancel' : 'cancel',
			'click .js-hs-submit' : 'send'
		},

		bindings: {
			'input.js-hs-mail-mailto': 'mailto',
			'input.js-hs-mail-sender': 'sender',
			'input.js-hs-mail-sender-email': 'sender-email',
			'input.js-hs-mail-subject': 'subject',
			'textarea.js-hs-mail-textarea': 'message'
		},

		initialize: function() {
			this.listenTo(this.model, 'request', this.loading);
			this.listenTo(this.model, 'sync', this.hide);
			this.listenTo(this.model, 'error', this.hide);
		},

		onRender: function() {
			this.bindValidation();
			this.stickit();

			// add the token to the form
			var token = this.$el.find('input[type=hidden]');
			this.model.set(token.attr('name'), token.val());

		},

		cancel: function(e) {
			e.preventDefault();
			HotspotsManager.trigger('tabs:remove', 'mail')
		},

		send: function(e) {
			e.preventDefault();
			var errorDiv = $(e.target).siblings('.alert'),
				errors = this.model.validate();

			if(errors) {
				errorDiv.removeClass('hide');
				Object.each(errors, function(value) {
					errorDiv.append('<div>'+value+'</div>');
				});
			} else {
				errorDiv.addClass('hide');
				this.model.save();
			}
		},

		loading: function() {
			this.$el.addClass('hs-loading');
		},

		hide: function() {
			this.template = '#js-hs-send-map-template-sent-response';

			this.render();
			this.$el.removeClass('hs-loading');
		}
	});
});