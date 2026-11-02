new Class('compojoom.hotspots.modules.menu', {
	Implements: [Options, Events, compojoom.hotspots.helper ],
	fullScreen: false,

	eventsMap: [
		{
			host: window,
			events: {
				resize: 'onWindowResize',
				screenChange: null,
				hotspotsResize: null,
				hotspotsSearchAddressResponse: null,
				menuClose: null,
				menuOpen: null,
				parseHotspotsList: 'parseHotspotsList',
				directionFound: null,
				zoomToMarkers: null,
				categoryClicked: null,
				searchList: null,
				hotspotsSearchHotspot: null
			}
		}
	],

	options: {
		menuId: 'slide_menu'
	},

	menuClosed: true,

	initialize: function (options, sandbox) {
		this.setOptions(options);

		this.sb = sandbox;
		this.setMenuHeight();
		this.setTabsContentHeight();

		this.initializeSlideMenu();
		this.initializeMenuTabs();
		this.initializeSearchTab();

		this.exportAllEvents();
		this.initializeAllCatTabs();


	},

	onScreenChange: function () {
		this.setMenuHeight();
		this.setTabsContentHeight();
	},
	onMenuOpen: function (dataId) {
		if (typeof dataId != 'undefined') {
			this.tabs.showTab(dataId);
		}
		this.slideMenu.slideOut();

		this.toggle.set('class', 'toggle-on');
		this.menuClosed = false;
	},
	onMenuClose: function () {
		this.toggle.set('class', 'toggle-off');
		this.menuClosed = true;
	},
	onHotspotsSearchAddressResponse: function (results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			var address = new Element('span', {
				'class': 'formated-address',
				html: results[0].formatted_address
			});
			var container = new Element('div', {
				'class': 'address-info'
			});
			var getDirection = new Element('span', {
				'class': 'hotspots-button',
				'html': this.translate('COM_HOTSPOTS_GET_DIRECTIONS', 'getDirections'),
				events: {
					click: function () {
						var forms = $$('.hotspots-tab-content form')

						forms.removeClass('active');
						document.id('search-directions').addClass('active');
						document.id('directions-arrival').set('value', results[0].formatted_address);

						document.id('directions-departure').focus();
						document.id('directions-display').set('html', '');
					}
				}
			});
			document.id('hotspots-address-result').set('html', '');
			container.adopt(address, getDirection);
			container.inject(document.id('hotspots-address-result'));
		} else if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
			document.id('hotspots-address-result').set('html', this.translate('COM_HOTSPOTS_ZERO_RESULTS_LOCATION', '0 Results'))
		} else {
			alert("Geocode was not successful for the following reason: " + status);
		}
	},
	onHotspotsResize: function () {
		var heights = this.calculateHeights();
		if (!this.fullScreen) {
			this.fullScreen = true;

			document.id(document.body).setStyle('overflow', 'hidden');
			this.normalScreenStyles = {
				'hotspots': document.id('hotspots').getStyles(
					'position', 'height', 'width', 'top', 'left'),
				'zindex': document.id('hotspots').getStyle('z-index'),
				'mapCont': heights.map,
				'mapCanvas': heights.map
			}

			var height = heights.window - heights.navigation - heights.borderTop - heights.borderBottom;

			document.id('hotspots').set('styles', {
				position: 'fixed',
				height: height + 'px',
				width: '100%',
				top: 0,
				left: 0,
				'z-index': 9998
			});

			document.id('map_cont').setStyle('height', '100%');
			document.id('map_canvas').setStyle('height', '100%');

			window.fireEvent('screenChange');
			window.fireEvent('fullScreen');

		} else {
			this.fullScreen = false;

			document.id(document.body).setStyle('overflow', 'auto');
			document.id('hotspots').set('styles', {
				position: this.normalScreenStyles.hotspots.position,
				height: this.normalScreenStyles.hotspots.height,
				width: '100%',
				top: this.normalScreenStyles.hotspots.top,
				left: this.normalScreenStyles.hotspots.left,
				'z-index': this.normalScreenStyles.zindex
			});
			document.id('map_cont').setStyle('height', this.normalScreenStyles.mapCont);
			document.id('map_canvas').setStyle('height', this.normalScreenStyles.mapCanvas);

			window.fireEvent('screenChange');
			window.fireEvent('normalScreen');
		}
	},
	onWindowResize: function () {
		if (this.fullScreen) {
			var heights = this.calculateHeights();
			var height = heights.window - heights.navigation - heights.borderTop - heights.borderBottom;
			document.id('hotspots').set('styles', {
				height: height + 'px'
			});

			this.setMenuHeight();
			this.setTabsContentHeight();
		}
	},

	initializeAllCatTabs: function () {
		document.id('all-hotspots').addEvent('click', function () {
			if (this.get('checked')) {
				window.fireEvent('allCatTabs', true);
				// determine the active categories and send them to the routing function
				var ids = [],
					cats = $$('ul#hotspots-tabs li');
				cats.each(function (cat) {
					if (cat.get('data-id') != 'search') {
						ids.push(cat.get('data-id'));
					}
				});

				window.fireEvent('route', ['category', ids]);
			} else {
				window.fireEvent('allCatTabs', false);
				var cat = $$('ul#hotspots-tabs li.active')[0];
				if (cat.get('data-id') != 'search') {
					window.fireEvent('route', ['category', cat.get('data-id')]);
				}

			}
		});
	},

	initializeSlideMenu: function () {
		this.menu = document.id(this.options.menuId);
		this.slideMenu = new Fx.Slide(this.menu, {
			mode: 'horizontal',
			hideOverflow: false
		});

		/**
		 * a small hack to show the menu
		 */
		this.menu.setStyles({
			position: 'absolute',
			overflow: 'visible'
		});

		this.toggle = document.id('toggle-menu');
		this.toggle.addEvent('click', function () {
			if (this.menuClosed) {
				this.slideMenu.slideOut();
				window.fireEvent('menuOpen');
			} else {
				this.slideMenu.slideIn();
				window.fireEvent('menuClose');
			}
		}.bind(this));
	},

	initializeSearchTab: function () {
		var element,
			actions = $$('.search-actions span'),
			forms = $$('.hotspots-tab-content form'),
			self = this;

		document.id('search-address').addEvent('submit', function (e) {
			e.stop();
			var location = document.id('search-address-input').get('value');
			window.fireEvent('hotspotsSearchAddress', location);
		});

		document.id('search-directions').addEvent('submit', function (e) {
			e.stop();
			var validator = new Form.Validator.Inline(this, {
				evaluateFieldsOnBlur: false
			});
			if (validator.validate()) {
				var departure = document.id('directions-departure').get('value');
				var arrival = document.id('directions-arrival').get('value');

				window.fireEvent('hotspotsSearchDirection', [departure, arrival])
				//				self.dataClass.getDirections(departure, arrival);
				//				if the deleteRouteButton is show, delete it
				if (self.deleteRouteButton) {
					self.deleteRouteButton.destroy();
				}


			}
		});

		element = document.id('search-hotspots');
		if (element) {
			element.addEvent('submit', function (e) {
				if (e && e.stop()) e.stop();

				var searchWord = document.id('search-hotspots-input').get('value').trim();
				var validator = new Form.Validator.Inline(this);
				if (validator.validate()) {
					self.searchHotspots(searchWord);
				}

			});
		}


		forms.each(function (form) {
			form.getElements('[type=text]').each(function (el) {
				new OverText(el, {
					wrap: true
				});
			});
		});


		actions.addEvent('click', function () {
			forms.removeClass('active');
			actions.removeClass('active');
			this.addClass('active');
			document.id(this.get('data-id')).addClass('active');
			//fire onBlur otherwise the labels are hidden
			document.id(this.get('data-id')).getElements('[type=text]').each(function (el) {
				el.fireEvent('blur');
			});
		});
	},

	createTab: function (catId) {
		var tabExist = null,
			self = this,
			lis = this.menu.getElement('ul').getElements('li'),
			title = '<img src="' + this.options.categories[catId].cat_icon + '" />',
			spanTitle = this.options.categories[catId].cat_name;


		lis.each(function (li) {
			if (li.get('data-id') == catId) {
				tabExist = catId;
			}
		});


		if (tabExist === null) {
			var span = new Element('span', {
				'title': spanTitle,
				html: title

			});

			document.id(this.options.menuId).getElement('ul').adopt(new Element('li', {
				'class': 'hotspots-tab ',
				'data-id': catId,
				events: {
					click: function () {
						self.lastCatClicked = catId;
						if (document.id('all-hotspots').get('checked')) {
							// determine the active categories and send them to the routing function
							var ids = [],
								cats = $$('ul#hotspots-tabs li');
							cats.each(function (cat) {
								if (cat.get('data-id') != 'search') {
									ids.push(cat.get('data-id'));
								}
							});

							window.fireEvent('route', ['category', ids]);
						} else {
							window.fireEvent('route', ['category', catId]);
						}
					}
				}
			}).adopt(span)
				.adopt(new Element('span', {
					'class': 'remove',
					html: '&times',
					events: {
						click: function (e) {
							e.stop();
							self.closeTab(this);
						}
					}
				}))
			);

			document.id(this.options.menuId).adopt(new Element('div', {

				'class': 'hotspots-tab-content',
				'data-id': catId,
				html: '<div id="htc' + catId + '"></div>'

			}).setStyle('display', 'block'));

			var moreLink = new Element('span', {
				'id': 'htc' + catId + 'more'
			});

			moreLink.inject(document.id('htc' + catId), 'after');
		}
		self.tabsSlide.reset();
//		If the menu is not closed by the user, open it
		if (!this.menuClosed && !this.options.startClosedMenu) {
			this.slideMenu.slideOut();
			if (this.slideMenu.open) {
				window.fireEvent('menuOpen');
			}
		}

		this.setTabsContentHeight();
	},

	onDirectionFound: function () {
		var self = this;
		self.deleteRouteButton = new Element('span', {
			'class': 'delete click right',
			'title': self.language.clearRoute,
			events: {
				click: function () {
					window.fireEvent('directionDestroy');

					// clear the directions and do blur to show the default title
					document.id('directions-departure')
						.set('value', '')
						.fireEvent('blur')
						.focus();
					document.id('directions-arrival').set('value', '').fireEvent('blur');
					document.id('directions-display').set('html', '');

					self.deleteRouteButton.destroy();
				}
			}
		});

		self.deleteRouteButton.inject(document.id('search-directions').getElement('div'));
		// a small trick so that we don't jump to a category tab after we have found a location
		this.lastCatClicked = 'search';
	},

	searchHotspots: function (searchWord) {
		document.id('hotspots-list').set('html', '');
		document.id('hms').set('html', '');

		window.fireEvent('route', ['search', searchWord]);
	},

	onHotspotsSearchHotspot: function () {
		// If the menu is not closed by the user, open it
		if (!this.menuClosed && !this.options.startClosedMenu) {
			this.slideMenu.slideOut();
			if (this.slideMenu.open) {
				window.fireEvent('menuOpen', 'search');
			}
		}

		var actions = $$('.search-actions span');
		var forms = $$('.hotspots-tab-content form');

		forms.removeClass('active');
		actions.removeClass('active');

		document.id('search-hotspots').addClass('active');
		actions.each(function (el) {
			if (el.get('data-id') == 'search-hotspots') {
				el.addClass('active');
			}
		});

		document.id('search-hotspots-input').set('value', decodeURIComponent(this.gup('search')));
	},

	onSearchList: function (spots) {

		var self = this,
			hotspots = spots.hotspots;

		document.id('hotspots-list').set('html', '');
		if (typeof hotspots == 'undefined') {
			var world = '<br />',
				noresults = this.translate('COM_HOTSPOTS_SEARCH_RETURNED_NO_RESULTS', 'We are sorry, but we couldn\'t find a hotspot matching your search criteria.');
			if (spots.worldCount.toInt() != 0) {
				noresults = this.translate('COM_HOTSPOTS_SEARCH_RETURNED_NO_RESULTS_IN_THIS_VIEW', 'We are sorry, but we couldn\'t find a hotspot matching your search criteria in the current view.')
				world += this.translate('COM_HOTSPOTS_SEARCH_RESULTS_AROUND_THE_WORLD');
			}

			var catInfo = new Element('div', {
				'class': 'info',
				html: noresults + world
			});
			catInfo.inject(document.id('hotspots-list'));
		} else {
			var catInfo = new Element('div', {
				'class': 'info',
				html: this.translate('COM_HOTSPOTS_SEARCH_IN_YOUR_CURRENT_VIEW_RETURNED', 'The search in your current view returned') + ' ' + spots.viewCount + ' ' + this.translate('COM_HOTSPOTS_HOTSPOTS', 'Hotspots')
			});
			catInfo.inject(document.id('hotspots-list'));
		}

		Object.each(hotspots, function (cat, key) {
			Object.each(cat, function (hotspot, hkey) {
				if (hkey != 'viewCount' && hkey != 'categoryCount') {
					self.spotContainer(hotspot).inject(document.id('hotspots-list'));
				}
			}, this);
		}, this);

		if (spots.offset < spots.viewCount) {
			if (spots.offset != 0) {
				self.previousPage(spots, '', true).inject('hotspots-list');
			}

			if (spots.offset + self.options.listLength.toInt() < spots.viewCount) {
				self.nextPage(spots, '', true).inject(document.id('hotspots-list'));
			}

		}
	},

	initializeMenuTabs: function () {
		//		create the tab slider
		this.tabsSlide = new compojoom.hotspots.slide({
			container: 'tab-container-inner',
			inner: 'hotspots-tabs',
			showItems: 4,
			itemWidth: 59,
			controls: {
				next: 'hotspots-slide-tabs-forward',
				previous: 'hotspots-slide-tabs-back'
			}
		});

		//		create tabs
		this.tabs = new compojoom.hotspots.tab(this.options.menuId, {
			tabSelector: '.hotspots-tab',
			contentSelector: '.hotspots-tab-content'
		});
	},

	calculateHeights: function () {
		var mapHeightBorderTop = document.id('map_cont').getStyle('border-top-width').replace('px', '');
		var mapHeightBorderBottom = document.id('map_cont').getStyle('border-bottom-width').replace('px', '');
		var mapHeight = document.id('map_cont').getSize().y;
		var element = null, nav, actions;

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
			nav = document.id('hotspots-navigation');
			if (nav) {
				heights.navigation = document.id('hotspots-navigation').getSize().y
			}
		}
		element = document.id('hotspots-navigation');
		if (element) {
			actions = document.id('hotspots-menu-actions');
			if (actions) {
				heights.actions = document.id('hotspots-menu-actions').getSize().y
			}
		}
		return heights;
	},

	setMenuHeight: function () {
		var heights = this.calculateHeights();
		document.id(this.options.menuId).setStyle('height', heights.map);
	},

	setTabsContentHeight: function () {
		var heights = this.calculateHeights();
		var baseMap = 500;
		var baseContentHeight = 450;
		var actualHeight = baseContentHeight + (heights.map - baseMap - heights.actions);
		var content = $$('#' + this.options.menuId + ' .hotspots-tab-content');
		content.each(function (el) {
			el.setStyle('height', actualHeight);
		});
	},

	calculateWidth: function () {
		var borderRight = document.id('map_cont').getStyle('border-right-width').replace('px', '');
		var borderLeft = document.id('map_cont').getStyle('border-left-width').replace('px', '');
		var mapWidth = document.id('map_cont').getSize().x - borderRight - borderLeft;
		var menuWidth = document.id(this.options.menuId).getSize().x;

		var widths = {
			'map': mapWidth,
			'menu': menuWidth
		};
		return widths;
	},

	closeTab: function (element) {
		var parent = element.getParent('.hotspots-tab'),
			menu = document.id(this.options.menuId),
			index = menu.getElements('.hotspots-tab').indexOf(parent),
			active,
			ids = [], elements;

		// close the tab
		this.tabs.closeTab(parent.get('data-id'));

		//if we have an active tab, return we don't need to reset the tabs and exit
		active = menu.getElement('.hotspots-tab.active');
		if (active) {
			// well in a perfect world this would function better
			// right now we will repeat the request, which will refresh the markers
			// and will change from the search tab to the latest tab in the group
			if (active.get('data-id') == 'search') {
				// check if there are open tabs and reset the markers
				elements = $$('ul#hotspots-tabs li:not(.active)');
				if (elements.length) {
					elements.each(function (cat) {
						ids.push(cat.get('data-id'));
					});
					window.fireEvent('route', ['category', ids]);
				} else {
					// if we don't have other tabs -> just remove the markers
					window.fireEvent('route', ['category', [-1]]);
				}

			}
			this.tabsSlide.reset();
			return;
		}

		var lis = document.id(this.options.menuId).getElements('li.hotspots-tab');

		if (index == lis.length) {
			index -= 1;
		} else if (index > lis.length) {
			index = lis.length;
		}

		var dataId = lis[index].get('data-id');

		// properly root when closing a tab
		if (document.id('all-hotspots').get('checked')) {
			var cats = $$('ul#hotspots-tabs li');
			cats.each(function (cat) {
				if (cat.get('data-id') != 'search') {
					ids.push(cat.get('data-id'));
				}
			});

			window.fireEvent('route', ['category', ids]);
		} else {
			if (dataId != 'search') {
				window.fireEvent('route', ['category', dataId]);
			}

		}

		//if we have the search tab set catid to -1
		if (dataId == 'search') {
			window.fireEvent('route', ['category', [-1]]);
		}

		this.tabsSlide.reset();
		this.tabs.showTab(dataId);
	},

	parseHotspotsList: function (spots, params) {

		var self = this;
		var hotspots = spots.hotspots;

		Object.each(hotspots, function (cat, key) {

			this.createTab(key);
			document.id('htc' + key).set('html', '');
			if (cat.viewCount != 0) {
				self.categoryInformation(cat, key).inject(document.id('htc' + key));

				if(cat.modules && cat.modules.top) {
					new Element('div', {
						'class': 'hotspots-modules hotspots-modules-top',
						html: cat.modules.top
					}).inject(document.id('htc'+key));
				}
				Object.each(cat, function (hotspot, hkey) {
					if (hkey != 'viewCount' && hkey != 'categoryCount' && hkey != 'modules') {
						self.spotContainer(hotspot).inject(document.id('htc' + key));
					}
				}, this);

				if (spots.offset < cat.viewCount) {
					if (spots.offset != 0) {
						self.previousPage(spots, key).inject(document.id('htc' + key));
					}

					if (spots.offset + self.options.listLength.toInt() < cat.viewCount) {
						self.nextPage(spots, key).inject(document.id('htc' + key));
					}

				}

				if(cat.modules && cat.modules.top) {
					new Element('div', {
						'class': 'hotspots-modules hotspots-modules-bottom',
						html: cat.modules.bottom
					}).inject(document.id('htc'+key));
				}
			} else {
				self.categoryInformation(cat).inject(document.id('htc' + key));
			}

		}, this);
		if (typeof this.lastCatClicked != 'undefined') {
			this.tabs.showTab(this.lastCatClicked);
		} else if (this.gup('catid')) {
			var cat = this.gup('catid').split(';').getLast();
			this.tabs.showTab(cat);
		}
	},

	spotContainer: function (hotspot) {
		var self = this,
			o = this.options,
			container = new Element('div', {
				'class': 'spots',
				events: {
					click: function (e) {
						if (e.target.hasClass('hs-zoom')) {
							self.sb.getMap().setCenter(new google.maps.LatLng(hotspot.latitude, hotspot.longitude));
							self.sb.setZoom(15);
						} else if (e.target.getParent().hasClass('hs-readmore') || e.target.match('a')) {
							//do nothing - why? why? why???
						} else {
							window.fireEvent('openUniqueInfoWindow', hotspot.id);
							$$('.spots').removeClass('active');
							container.addClass('active');
						}

					}
				}
			});
		var template = '<span class="hotspots-title">{{{title}}}</span>'
			+ '<div>{{#thumb}}<img src="{{thumb}}" alt="{{title}}" class="hotspots-thumb"/>{{/thumb}}{{{description}}}</div>'
			+ '<div class="clear-both"></div>';

		if (o.showAddress.toInt()) {
			template += '<div class="hs-address">';
			template += '<span class="hs-street">{{street}}</span>';
			if (o.userInterface.toInt()) {
				template += ', <span class="hs-city">{{city}}</span>';
				template += '{{#zip}}, <span class="hs-zip">{{zip}}</span>{{/zip}}';
			} else {
				template += '{{#zip}}, <span class="hs-zip">{{zip}}</span>{{/zip}}';
				template += ' <span class="hs-city">{{city}}</span>';
			}
			if (o.showCountry.toInt()) {
				template += ', <span class="hs-country">{{country}}</span>';
			}
			template += '</div>';
		}

		if (o.showAuthor.toInt() || o.showDate.toInt()) {
			template += '<div class="hs-meta">';
			if (o.showAuthor.toInt()) {
				template += '{{#created_by}}<span class="hs-author">' + this.translate('COM_HOTSPOTS_POSTED_BY', 'Posted by') + ' {{created_by}}</span>{{/created_by}}';
			}
			if (o.showDate.toInt()) {
				template += '<span class="hs-date"> ' + this.translate('COM_HOTSPOTS_ON', 'on') + ' {{date}}</span>'
			}
			template += '</div>';
		}
		template += '<div class="hs-links">';

		if (o.showZoomButton.toInt()) {
			template += '<span class="hs-zoom">' + this.translate('COM_HOTSPOTS_ZOOM', 'Zoom') + '</span>';
		}

		template += '{{#readmore}}<span class="hs-readmore"> <a href="{{readmore}}">' + this.translate('COM_HOTSPOTS_READ_MORE', 'Read more') + '</a></span>{{/readmore}}';

		template += '</div>';
		container.set('html', Mustache.to_html(template, hotspot));

		return container;
	},

	previousPage: function (spots, key, search) {
		var self = this,
			previous = new Element('span', {
				html: 'previous',
				'class': 'hs-navigation',
				events: {
					click: function () {
						if (search) {
							var offset = spots.offset - self.options.listLength.toInt(),
								searchWord = self.gup('search');
							window.fireEvent('hotspotsSearchHotspot', [searchWord, offset]);
						} else {
							var params = {
								'categories': key,
								'offset': spots.offset - self.options.listLength.toInt()
							};
							window.fireEvent('hotspotsLoadCategory', params);
						}
					}
				}
			});
		return previous;
	},

	nextPage: function (spots, key, search) {
		var self = this,
			next = new Element('span', {
				html: 'next',
				'class': 'hs-navigation',
				events: {
					click: function () {
						if (search) {
							var offset = spots.offset + self.options.listLength.toInt(),
								searchWord = self.gup('search');
							window.fireEvent('hotspotsSearchHotspot', [searchWord, offset]);
						} else {
							var params = {
								'categories': key,
								'offset': spots.offset + self.options.listLength.toInt()
							};
							window.fireEvent('hotspotsLoadCategory', params);
						}
					}
				}
			});

		return next;
	},

	/**
	 *
	 * @param cat
	 * @param key - the category id
	 * @return {Element}
	 */
	categoryInformation: function (cat, key) {
		var container = new Element('div', {
			'class': 'info'
		});
		if (cat.viewCount == 0) {
			if (cat.categoryCount > 0) {
				container.set('html', '<div class="info-content">' + this.translate('COM_HOTSPOTS_NO_LOCATIONS_IN_CURRENT_VIEW', 'There are no locations in your current map view. Zoom out to see more results') + '</div>');
			} else {
				container.set('html', '<div class="info-content">' + this.translate('COM_HOTSPOTS_NO_HOTSPOTS_IN_CATEGORY', 'No hotspots in category') + '</div>');
			}
		} else {
			container.set('html', '<h3>' + this.options.categories[key].cat_name + '</h3>');
			container.grab(new Element('div', {
				'class': "info-content",
				'html': '<div>' + this.options.categories[key].cat_description + '</div>' + this.translate('COM_HOTSPOTS_IN_YOUR_CURRENT_VIEW_THERE_ARE', 'In your current view there are') + ' ' + cat.viewCount + ' ' + this.translate('COM_HOTSPOTS_HOTSPOTS', 'Hotspots')
			}));
		}
		return container;
	},

	onCategoryClicked: function (id) {
		this.lastCatClicked = id;
	}
});