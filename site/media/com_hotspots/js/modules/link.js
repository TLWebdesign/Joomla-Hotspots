new Class('compojoom.hotspots.modules.link',{
	Implements: [Options, Events, compojoom.hotspots.helper],
	options: {
		
	},

	eventsMap:[
		{
			host:window,
			events: {
				hotspotsDispatch:null
			}
		}
	],

	initialize: function(container, options, sb) {
		this.setOptions(options);

		this.exportAllEvents();

		var self = this;
		self.sb = sb;
		self.container = document.id(container);

		document.id('link-button').addEvent('click', function() {
			if(!self.div) {
				self.show();
			} else {
				// destroy it if the user clicks again on the button
				self.div.destroy();
				self.div = null;
			}
		});
	},

	show: function() {
		var self = this, html = '<button class="close">×</button>'+this.translate('COM_HOTSPOTS_COPY_LINK','Copy link')+':<br /><input value="'+self.createLink()+'" style="width: 200px" />';
		var div = self.div = new Element('div', {
			html: html,
			'class' : 'hotspots-link-container',
			events: {
				'click:relay(.close)' : function() {
					self.div.destroy();
					self.div = null;
				},
				'click:relay(input)': function() {
					div.getElement('input').select();
				}
			}
		}).inject(this.container);

		// focus and select everything in the input field
		div.getElement('input').focus();
		div.getElement('input').select();
	},

	/**
	 * Creates a link that currently contains the center and zoom
	 * @return {String}
	 */
	createLink: function() {
		var url = window.location.href, c = this.gup('c'), z = this.gup('z');

		if(c) {
			url = url.replace(encodeURI(c),this.sb.getCenter().toString());
		} else {
			url += '&c='+this.sb.getCenter().toString();
		}

		if(z) {
			url = (url).replace('&z='+encodeURI(z),'&z='+this.sb.getZoom().toString());
		} else {
			url += '&z='+this.sb.getZoom().toString();
		}

		return url;
	},

	/**
	 * destroy the div containing the coordinates - ensures that when the user moves around
	 * he will click again on the link button and we will generate new coordinates
	 */
	onHotspotsDispatch:function () {
		if(this.div) {
			this.div.destroy();
			this.div = null;
		}
	}
});