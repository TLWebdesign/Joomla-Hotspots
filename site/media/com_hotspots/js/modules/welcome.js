new Class('compojoom.hotspots.modules.welcome', {
	Implements: [Options, Events],
	options: {
		
	},
	
	initialize: function(options) {
		var hidden = Cookie.read('hide-welcome');
		if(!hidden) {
			var element = document.id('close-welcome');
			if(element) {
				element.addEvent('click', function() {
					if(document.id('hide-welcome').get('checked')) {
						Cookie.write('hide-welcome', '1', {
							duration: 24*60*356
						});
					};
					document.id('hotspots-welcome').fade(0);
				});
			}
		}	
	}
});