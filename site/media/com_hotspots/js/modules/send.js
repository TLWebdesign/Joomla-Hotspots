new Class('compojoom.hotspots.modules.send',{
	Implements: [Options, Events, compojoom.hotspots.helper],
	options: {
		showMenu: 1
	},
	eventsMap: [{
		host: window,
		events: {
			hotspotsSendMap:null
		}
	}],

	initialize: function(options, sb) {
		this.sb = sb;
		this.setOptions(options);

		this.exportAllEvents();

		if(this.options.mailMap.toInt()) {
			document.id('send-button').addEvent('click', function() {
				window.fireEvent('hotspotsSendMap');
			})

		};
	},

	onHotspotsSendMap:function () {
		var url = this.options.baseUrl + '?option=com_hotspots&view=mail&format=raw';
		var modal = new LightFace.Request({
			height:730,
			width:550,
			title:this.translate('COM_HOTSPOTS_EMAIL_THIS_MAP', 'Email this map'),
			url:url,
			draggable:true,
			resetOnScroll:false,
			request:{
				method:'post',
				'data':this.sb.getStaticMapParams(),
				onSuccess:function (response) {
					modal.messageBox.set('html', response);
					modal.MapForm = modal.messageBox.getElement('form');

					// Labels over the inputs.
					modal.MapForm.getElements('[type=text], textarea').each(function (el) {
						new OverText(el);
					});
				}.bind(this)
			},
			buttons:[
				{
					title:this.translate('COM_HOTSPOTS_SEND', 'Send'),
					color:'blue',
					event:function () {
						var validator = new Form.Validator.Inline(modal.MapForm, {
							wrap:true
						});
						if (validator.validate()) {
							var request = new Request({
								data:modal.messageBox.getElement('form'),
								url:modal.messageBox.getElement('form').get('action'),
								method:'post',
								noCache:true,
								onRequest:function () {
									modal.fade();
								},
								onSuccess:function (responseHTML) {
									modal.unfade();
									modal.messageBox.set('html', responseHTML);
									modal.hideButton(this.language.send);
								}.bind(this),
								onFailure:function () {
									modal.unfade();
									modal.messageBox.set('html', this.language.somethingIsWrong);
								}.bind(this)
							});
							request.send();
						}

					}.bind(this)
				},

				{
					title:this.translate('COM_HOTSPOTS_CLOSE', 'Close'),
					event:function () {
						modal.close();
					}
				}
			]
		});
		modal.open();
	}
});