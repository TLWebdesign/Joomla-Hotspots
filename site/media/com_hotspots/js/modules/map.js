new Class('compojoom.hotspots.modules.map', {
	Implements: [Options, Events, compojoom.hotspots.helper],

	isMenuOpen: false,
	fullScreen: false,
	options: {},

	eventsMap: [
		{
			host: window,
			events: {
				hotspotsResize: 'onWindowResize',
				menuOpen: null,
				menuClose: null,
				resize: 'onWindowResize',
				screenChange: null
			}
		}
	],

	initialize: function (options, sb) {
		this.sb = sb;
		this.setOptions(options);

		this.exportAllEvents();
		this.tween = new Fx.Tween(document.id('map_canvas'), {
			onComplete: function () {
				window.fireEvent('resize');
			}.bind(this)
		});
	},

	calculateHeights: function () {
		var mapHeightBorderTop = document.id('map_cont').getStyle('border-top-width').replace('px', '');
		var mapHeightBorderBottom = document.id('map_cont').getStyle('border-bottom-width').replace('px', '');
		var mapHeight = document.id('map_cont').getSize().y;
		var element = null;

		var heights = {
			'map': mapHeight - mapHeightBorderBottom - mapHeightBorderTop,
			'borderTop': mapHeightBorderTop,
			'borderBottom': mapHeightBorderBottom,
			'window': window.getSize().y,
			'navigation': 0,
			'actions': 0
		}
		element = document.id('hotspots-menu-actions');
		if (element) {
			heights.navigation = document.id('hotspots-navigation').getSize().y
		}
		element = document.id('hotspots-navigation');
		if (element) {
			heights.actions = document.id('hotspots-menu-actions').getSize().y
		}
		return heights;
	},

	onScreenChange: function () {
		var widths = this.calculateWidth();
		if (document.body.hasClass('hotspots-fullscreen')) {
			document.body.removeClass('hotspots-fullscreen');
		} else {
			document.body.addClass('hotspots-fullscreen');
		}

		if (!this.isMenuOpen) {
			document.id('map_canvas').setStyle('width', widths.map);
		} else {
			document.id('map_canvas').setStyle('width', widths.map - widths.menu);
		}
		google.maps.event.trigger(this.sb.getMap(), 'resize');
		window.fireEvent('panoramaResize');
	},

	calculateWidth: function () {

		var borderRight = document.id('map_cont').getStyle('border-right-width').replace('px', '');
		var borderLeft = document.id('map_cont').getStyle('border-left-width').replace('px', '');
		var mapWidth = document.id('map_cont').getSize().x - borderRight - borderLeft;
		var menuWidth = document.id('slide_menu').getSize().x;

		var widths = {
			'map': mapWidth,
			'menu': menuWidth
		};
		return widths;
	},


	onWindowResize: function () {
		var widths = this.calculateWidth();

		if (this.isMenuOpen) {
			document.id('map_canvas').setStyle('width', widths.map - widths.menu);
		} else {
			document.id('map_canvas').setStyle('width', widths.map);
		}

		google.maps.event.trigger(this.sb.getMap(), 'resize');
		window.fireEvent('panoramaResize');
	},

	onMenuOpen: function () {
		this.isMenuOpen = true;
		var widths = this.calculateWidth();
		this.tween.start('width', widths.map, widths.map - widths.menu);
	},
	onMenuClose: function () {
		this.isMenuOpen = false;

		var widths = this.calculateWidth();
		this.tween.start('width', widths.map);
	}
});