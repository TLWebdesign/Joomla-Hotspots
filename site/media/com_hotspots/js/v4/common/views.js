/**
 * Created by DanielDimitrov on 22.04.2014.
 */
HotspotsManager.module("Common.Views", function (Views, ContactManager, Backbone, Marionette, $, _) {
	Views.PaginatedView = Marionette.Layout.extend({
		template: "#paginated-view",
		regions: {
			paginationControlsRegionTop: "#js-pagination-controls-top",
			paginationControlsRegionBottom: "#js-pagination-controls-bottom",
			paginationMainRegion: ".js-pagination-main"
		},
		/**
		 * There is a lot of copy/paste in this method, but unfortunatly Marionettejs
		 * doesn't allow us to show the same view in 2 different regions.
		 *
		 * @param options
		 */
		initialize: function (options) {
			var self = this, listView, topControls, bottomControls;
			this.collection = options.collection;

			this.listenTo(this.collection, 'request', this.loading);
			this.listenTo(this.collection, 'sync', this.hide);
			this.listenTo(this.collection, 'error', this.hide);

			listView = new options.mainView({
				collection: this.collection
			});

			topControls = new Views.PaginationControls(
				{
					paginatedCollection: this.collection
				}
			);

			bottomControls = new Views.PaginationControls(
				{
					paginatedCollection: this.collection
				}
			);

			this.listenTo(topControls, 'page:change', function (page) {
				self.trigger('page:change', page);
			});

			this.listenTo(bottomControls, 'page:change', function (page) {
				self.trigger('page:change', page);
			});

			this.on("show", function () {
				this.paginationControlsRegionTop.show(bottomControls);
				this.paginationControlsRegionBottom.show(topControls);
				this.paginationMainRegion.show(listView);
			});
		},

		loading: function() {
			this.$el.addClass('hs-loading');
		},

		hide: function() {
			this.$el.removeClass('hs-loading');
		}
	});

	Views.PaginationControls = Marionette.ItemView.extend({
		template: "#pagination-controls",
		className: "pagination pagination-mini text-center",

		events: {
			'click a[class=navigatable]': 'navigateToPage'
		},

		initialize: function (options) {
			this.paginatedCollection = options.paginatedCollection;

			this.listenTo(this.paginatedCollection, 'sync', this.render);
			this.listenTo(this.paginatedCollection, "page:change:after", this.render);
		},

		navigateToPage: function (e) {
			e.preventDefault();
			var page = parseInt($(e.target).data('page'), 10);

			HotspotsManager.trigger('hotspots:page', page);
			this.trigger('page:change', page);
		},

		serializeData: function () {
			var info = _.clone(this.paginatedCollection.state);
			info.pageSet = this.paginatedCollection.setPagination(info);
			if (info.currentPage > 1) {
				info.previous = info.currentPage - 1;
			}

			if (info.currentPage < info.totalPages) {
				info.next = info.currentPage + 1;
			}

			return info;
		}
	});

	Views.Loading = Marionette.ItemView.extend({
		template: "#loading-view",
		className: 'hs-init-loading'
	});
});