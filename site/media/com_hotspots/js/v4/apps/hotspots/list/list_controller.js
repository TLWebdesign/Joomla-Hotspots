/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("HotspotsApp.List", function (List, HotspotsManager, Backbone, Marionette, $, _) {

	List.Controller = {
		init: function () {
			var hotspots = HotspotsManager.request('hotspots:collection');

			this.hotspotsListLayout = new HotspotsManager.HotspotsApp.List.Layout();

			var model = HotspotsManager.request('filter:entity');
			this.filterView = new HotspotsManager.FilterApp.Show.FilterView({model: model});

			this.filterView.on('filter:show', function (toggle) {
				if (toggle) {
					this.hotspotsListLayout.list.$el.show();
				}
				else {
					this.hotspotsListLayout.list.$el.hide();
				}
			}, this);

			this.hotspotsListLayout.on("show", function () {
				var loadingView = new HotspotsManager.Common.Views.Loading();
				this.hotspotsListLayout.list.show(loadingView);

				this.hotspotsListLayout.filter.show(this.filterView);

			}, this);

			return this.hotspotsListLayout;
		},

		shotTileHotspot: function(model) {
			var self = this;
			var view = new List.TileHotspot({model: model});
			view.on('close', function() {
				HotspotsManager.trigger('tile:marker:closed', model);
				self.hotspotsListLayout.tile.close();
			});
			this.hotspotsListLayout.tile.show(view);
		},

		showList: function (hotspots) {
			if (!this.hotspotsListView) {
				this.hotspotsListView = new HotspotsManager.Common.Views.PaginatedView({
					collection: hotspots,
					mainView: HotspotsManager.HotspotsApp.List.Hotspots
				});

				this.hotspotsListLayout.list.show(this.hotspotsListView);
			}

			this.hotspots = hotspots;
		},

		/**
		 * When a marker is selected we need to trigger the select on the hotspots collection
		 * in the list as well
		 *
		 * @param model
		 * @param isSelected
		 */
		selected: function (model, isSelected) {
			var selected = this.hotspots.get(model.get('id'));

			// change to the hotspots tab
			HotspotsManager.trigger('tabs:change', 'hotspots');

			// select the hotspot
			selected.select(true);
		},


		setPage: function(page) {
			this.page = page;
		}
	}
});
