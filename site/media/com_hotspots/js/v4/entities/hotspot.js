/**
 * Created by DanielDimitrov on 26.02.14.
 */
HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){


	Entities.Hotspot = Backbone.Model.extend({
		initialize: function() {
			var selectable = new Backbone.Picky.Selectable(this);
			_.extend(this,selectable);
		},
		getFormatedAddress: function() {
			var address = [];
			address.push(this.get('street'));
			address.push(this.get('zip'));
			address.push(this.get('city'));
			address.push(this.get('state'));
			address.push(this.get('country'));

			address = _.uniq(_.compact(address));

			return address;
		}
	});

	Entities.HotspotCollection = Backbone.PageableCollection.extend({
		mode: "server",
		model: Entities.Hotspot,

		initialize: function(options) {
			var self = this,
				filter = HotspotsManager.request('filter:entity');

			// Init the state of currentPage
			this.state.currentPage = filter.get('page');

			// We propagate page changes in our filter, so whenever page changes there, we need to update
			this.listenTo(filter, 'change:page', function(model, page) {
				// when the map is moved the pagination is unset in the filter in order to get a nicer url
				// here we need to set it to 1 for the paginator
				page = page || 1;

				self.getPage(parseInt(page, 10));
				self.trigger("page:change:after");
			});

			filter.on('change:search', function() {

				self.fs = filter.get('search') ? 1 : 0;
			});

			var singleSelect = new Backbone.Picky.SingleSelect(this);
			_.extend(this, singleSelect);
		},


		url: function (params) {
			var filter = HotspotsManager.request('filter:entity');
			var query = [
				'option=com_hotspots',
				'view=jsonv4',
				'task=gethotspots',
				'hs-language=' + HotspotsConfig.language
			];

			return HotspotsConfig.baseUrl + '?' + query.join('&');
		},

		state: {
			// how many items per page should be shown
			pageSize: HotspotsConfig.listLength,

			pagesInRange: 1
		},

		queryParams: {
			'search' : function() {
				var filter = HotspotsManager.request('filter:entity');
				if (filter.get('search')) {
					return filter.get('search');
				}
			},
			'cat' : function() {
				var filter = HotspotsManager.request('filter:entity');
				return filter.getCatIds().join(';');
			},
			'level': function() {
				var filter = HotspotsManager.request('filter:entity');
				return filter.get('level')
			},
			'ne' : function() {
				var filter = HotspotsManager.request('filter:entity');
				return filter.get('ne');
			},
			'sw' : function() {
				var filter = HotspotsManager.request('filter:entity');
				return filter.get('sw');
			},
			'c' : function() {
				var filter = HotspotsManager.request('filter:entity');
				return filter.get('center').toUrlValue();
			},
			/** full search parameter **/
			'fs' : function() {
				var filter = HotspotsManager.request('filter:entity');

				return filter.hasChanged('search') ? 1 : 0;
			},

			/**
			 * On the server we use the offset to grab the right number of hotspots
			 *
			 * @returns {number}
			 */
			'offset': function() {
				var filter = HotspotsManager.request('filter:entity');

				// start page is always 1, but offset has to be 0
				return this.state.currentPage * this.state.pageSize - this.state.pageSize;
			},

			// what format would you like to request results in?
			'format': 'raw'
		},

		/**
		 * Copied and modified this function out of the old backbone.paginator
		 * Once we know how to handle pages in paginator v2 we'll update/remove it
		 *
		 * @param info
		 * @returns {Array}
		 */
		setPagination: function ( info ) {

			var pages = [], i = 0, l = 0;

			// How many adjacent pages should be shown on each side?
			var ADJACENTx2 = info.pagesInRange * 2,
				LASTPAGE = info.totalPages;

			if (LASTPAGE > 1) {

				// not enough pages to bother breaking it up
				if (LASTPAGE <= (1 + ADJACENTx2)) {
					for (i = 1, l = LASTPAGE; i <= l; i++) {
						pages.push(i);
					}
				}

				// enough pages to hide some
				else {

					//close to beginning; only hide later pages
					if (info.currentPage <=  (info.pagesInRange + 1)) {
						for (i = 1, l = 2 + ADJACENTx2; i < l; i++) {
							pages.push(i);
						}
					}

					// in middle; hide some front and some back
					else if (LASTPAGE - info.pagesInRange > info.currentPage && info.currentPage > info.pagesInRange) {
						for (i = info.currentPage - info.pagesInRange; i <= info.currentPage + info.pagesInRange; i++) {
							pages.push(i);
						}
					}

					// close to end; only hide early pages
					else {
						for (i = LASTPAGE - ADJACENTx2; i <= LASTPAGE; i++) {
							pages.push(i);
						}
					}
				}

			}

			return pages;
		},

		parseState: function (resp, queryParams, state, options) {
			var filter = HotspotsManager.request('filter:entity');
			filter.set('total', resp.total_entries);
			return {totalRecords: resp.total_entries};
		},

		parseRecords: function (resp, xhr) {
			var cats = HotspotsManager.request('category:entities'),
				hotspots = resp.items;

			// If we did a search and it doesn't return anything in current view, but somewhere else
			// then use the new boundaries
			if (resp.boundaries)
			{
				HotspotsManager.trigger('hotspots:newboundaries', resp.boundaries)
			}

			hotspots.forEach(function(hotspot, key) {
				if(!hotspot.icon) {
					hotspot.icon = cats.get(hotspot.catid).get('cat_icon');
				}
			});

			return hotspots;
		}
	});

	var API = {
		request: false,
		collection: false,
		getCollection: function() {
			if(!this.collection) {
				this.collection = new Entities.HotspotCollection;
			}
			return this.collection;
		},

		getHotspotEntities: function(options) {
			var hotspots = this.getCollection();

			// If the user is moving too fast we need to cancel the previous request
			if(this.request && this.request.readyState > 0 && this.request.readyState < 4){
				this.request.abort();
			}

			var defer = $.Deferred();
			this.request = hotspots.fetch({
				success: function(data){
					defer.resolve(data);
				}
			});

			return defer.promise();
		}
	};

	HotspotsManager.reqres.setHandler("hotspots:entities", function(options){
		return API.getHotspotEntities(options);
	});

	HotspotsManager.reqres.setHandler("hotspots:collection", function(){
		return API.getCollection();
	});
});