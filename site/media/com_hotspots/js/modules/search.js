new Class('compojoom.hotspots.modules.search', {
	Implements: [Options, Events],
	options: {

	},

	initialize: function (options) {
		this.setOptions(options);
		var element = document.id('quick-search');
		var input = element.getElement('input');
		new OverText(input, {
			wrap: true
		});

		element.addEvent('submit', function (e) {
			e.stop();
			var searchWord = element.getElement('input').get('value').trim();
			input.blur();
			window.fireEvent('route', ['search', searchWord]);
		});
	}
});