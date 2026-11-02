new Class('compojoom.hotspots.modules.menubar',{
	Implements: [Options, Events],
	options: {
		showMenu: 1
	},
	initialize: function(options) {	
		this.setOptions(options);
		if(this.options.resizeMap.toInt()) {
			document.id('resize').addEvent('click', function() {
				window.fireEvent('hotspotsResize');
			});
		}
		
		var element = document.id('directions-button');
		if(element) {
			document.id('directions-button').addEvent('click', function(){
				window.fireEvent('menuOpen', 'search');
			});
		}
		
		var element = document.id('center-button');
		if (element) {
			element.addEvent('click', function() {
				window.fireEvent('zoomToMarkers');
			}.bind(this));
		}
	}
});