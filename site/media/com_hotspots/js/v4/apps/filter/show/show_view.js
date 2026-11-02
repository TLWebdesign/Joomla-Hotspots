/**
 * Created by DanielDimitrov on 21.04.2014.
 */
HotspotsManager.module("FilterApp.Show", function(Show, HotspotsManager, Backbone, Marionette, $, _){

	Show.FilterView = Marionette.ItemView.extend({
		template: '#js-hs-filter-template',

		modelEvents: {
			"change:total": 'render'
		},

		events: {
			'click .js-hs-show-filter': 'showFilters',
			'click input[type=checkbox]': 'selectCat',
			'click .js-hs-submit-search': 'search',
			'keypress input[name=search]': function(e) {
				if(e.which == 13) {
					this.search();
				}
			},
			'click .js-hs-close': 'deselectCat'
		},

		bindings: {
			'input[name=search]' : 'search'
		},

		search: function() {
			this.$el.find('.js-hs-show-filter').removeClass('hide');
			this.$el.find('.js-hs-filter-options').addClass('hide');
			this.$el.find('.js-hs-active-filters ').removeClass('hide');

			this.trigger('filter:show', 1);
			HotspotsManager.trigger('filter:search');
			HotspotsManager.trigger('filter:changed');
		},

		initialize: function() {


			this.cats = HotspotsManager.request('category:entities');
			this.cats.on('selected', this.renderSelectedCats, this);
			this.cats.on('deselected', this.renderSelectedCats, this);
		},

		onRender: function() {
			this.renderSelectedCats();
			this.checkCategories();

			this.$el.find('.js-hs-filter-options label').tooltipster({
				contentAsHTML: true,
				maxWidth: 250
			});

			this.stickit();
		},

		renderSelectedCats: function() {
			var activeCats = this.$el.find('.js-hs-active-filters').empty();
			_.each(this.model.get('cats'), function(value){
				var img = $('<img>').attr({
					'src': value.get('cat_icon')
				});
				var span = $('<span>').attr({
					class: 'fa fa-times js-hs-close',
					'title': '<strong>' + value.get('cat_name') + '</strong> ' + value.get('cat_description'),
					'data-cat-id': value.get('id')
				});
				var container = $('<div class="hs-quick-filter-img"></div>');
				activeCats.append(container.append(span).append(img));
			});
			this.$el.find('.js-hs-active-filters span').tooltipster({
				contentAsHTML: true,
				maxWidth: 200,
				position: 'bottom'
			});

		},

		showFilters: function(e) {
			this.$el.find('.js-hs-show-filter').addClass('hide');
			this.$el.find('.js-hs-filter-options').removeClass('hide');
			this.$el.find('.js-hs-filter-counter').addClass('hide');
			this.$el.find('.js-hs-active-filters ').addClass('hide');
			this.trigger('filter:show', this.$el.find('.js-hs-filter-options').hasClass('hide'));
		},

		selectCat: function(e) {
			var clicked = $(e.currentTarget),
				self = this;
			if( clicked.is(':checked') ) {
				self.cats.select(this.cats.get(clicked.val()));

				// Automatically select children categories
				clicked.parents('li').first().find('input').each(function(){
					var input = jQuery(this);
					input.prop( "checked", true );
					self.cats.select(self.cats.get(input.val()));
				});
			} else {
				this.cats.deselect(this.cats.get(clicked.val()));
				clicked.parents('li').first().find('input').each(function(){
					var input = jQuery(this);
					input.prop( "checked", false );
					self.cats.deselect(self.cats.get(input.val()));
				});
			}
		},

		deselectCat: function(e) {
			var clicked = $(e.currentTarget);
			var id = clicked.data('cat-id');
			this.cats.deselect(this.cats.get(id));
			clicked.closest('div').remove();

			this.checkCategories();
			this.search();
		},

		checkCategories: function() {
			var cats = this.$el.find('.js-hs-filter-options input[name=cats]'),
				ids = [];

			_.each(this.model.get('cats'), function(value){
				ids.push(value.get('id'));
			});

			cats.each(function(key, value) {
				var checkbox = $(value);
				checkbox.prop('checked', false);

				if(_.indexOf(ids, Number(checkbox.val())) >= 0) {
					checkbox.prop('checked', true);
				}
			});
		}
	});
});
