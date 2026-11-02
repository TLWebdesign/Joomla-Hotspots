/**
 * This class shows a loading box generally over the map
 * TODO: make use of the message
 */
new Class('compojoom.hotspots.modules.loader',{
	Implements: [Options, Events, compojoom.hotspots.helper],
	eventsMap: [{
		host: window,
		events: {
			hotspotsLoaderStart:null,
			hotspotsLoaderStop:null
		}
	}],
	running: 0,

	initialize: function(container, options, sb) {
		this.sb = sb;
		this.setOptions(options);
		this.exportAllEvents();
		this.container = document.id(container);
	},

	getSpinner:function (message) {
		if (!this.spinner) {
			this.spinner = new Element('div', {
				'class':'spinner'
			}).adopt(new Element('img', {
				src:this.options.rootUrl + 'media/com_hotspots/images/utils/loader.gif'
			}), new Element('div', {
				html: this.translate('COM_HOTSPOTS_LOADING_DATA', 'Loading data')
			})).inject(this.container);
		}
		return this.spinner;
	},

	onHotspotsLoaderStart: function(message) {
		this.running = this.running + 1;
		this.getSpinner(message).fade('in');
	},

	onHotspotsLoaderStop: function() {
		this.running = this.running - 1;
		if(this.running <= 0) {
			this.getSpinner('').fade('out');
		}
	}
});