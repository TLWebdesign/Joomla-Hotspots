/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("HotspotsApp.List", function(List, HotspotsManager, Backbone, Marionette, $, _){
	List.Layout = Marionette.Layout.extend({
		template: "#hs-tab-list-layout-template",

		regions: {
			filter: '#js-hs-tab-filter-region',
			list: "#hs-tab-list-hotspots-region",
			tile: '#js-hs-tab-hotspots-tile-region',
			hotspotsRegion: "#hotspots-region"
		}
	});

	var HotspotView = Marionette.ItemView.extend({
		events: {
			'keypress .js-hs-get-directions input': 'keypressSearchDir',
			'click .js-hs-show-directions': 'displayDirForm',
			'click .hs-quick-dir': 'changeDir',
			'click .js-hs-get-directions .js-hs-close': 'displayDirForm',
			'click .js-hs-get-directions .js-hs-search': 'searchDir',
			'click .js-hs-zoom-in': function(e){
				e.stopPropagation();
				HotspotsManager.trigger('marker:zoomIn', this.model);
			}
		},

		initialize: function() {
			this.$el.prop("id", "hs-hotspot-id-" + this.model.get("id"));
			this.$el.attr("data-id", this.model.get("id"));

			HotspotsManager.request('category:entities');
		},

		displayDirForm: function(e) {
			e.stopPropagation();
			this.$el.find('.hs-get-directions').toggle();
			this.$el.find('.hs-get-directions input').focus();
		},

		keypressSearchDir: function(e) {
			if ( e.which === 13 ) {
				this.searchDir(e);
			}
		},

		searchDir: function(e) {
			e.stopPropagation();
			var input = this.$el.find('.hs-get-directions input'),
				active = this.$el.find('.hs-quick-dir.active').data('id'),
				origin, destination;

			if(active == 'from') {
				origin = input.val();
				destination = {lat: this.model.get('lat'), lng: this.model.get('lng'), name: this.model.getFormatedAddress()}
			} else {
				destination = input.val();
				origin = {lat: this.model.get('lat'), lng: this.model.get('lng'), name: this.model.getFormatedAddress()}

			}
			HotspotsManager.trigger('directions:search', origin, destination);
		},

		changeDir: function(e) {
			e.stopPropagation();
			$(e.currentTarget).closest('.hs-get-directions').find('.hs-quick-dir').each(function(key, el) {
				$(el).removeClass('active');
			});
			$(e.currentTarget).addClass('active');
		},

		selected: function() {
			var self = this,
				latLng = new google.maps.LatLng(this.model.get('lat'), this.model.get('lng')),
				mapModel = HotspotsManager.request('map:entity'),
				content = this.$el.closest('#js-hs-tabs-content > div'),
				top, bottom, height;

			mapModel.svService.getPanoramaByLocation(
				latLng,
				100,
				function(data, status) {
					if (status == google.maps.StreetViewStatus.OK) {
						if(data.links.length) {
							var pano = data.links[0].pano;
							var url = 'https://maps.googleapis.com/maps/api/streetview?size=260x90&sensor=false&pano='+pano + '&key=' + HotspotsConfig.apiKey;

							// Remove any old click events on the streetmap
							self.$el.find('.hs-streetmap').unbind('click');
							// Add the new events
							self.$el.find('.hs-streetmap').css({
								'background': 'url('+url+')',
								'display': 'block'
							}).on('click', function(e) {
								e.stopPropagation();
								HotspotsManager.trigger('marker:streetView', pano);
							});
						}

					}
				}
			);

			this.$el.addClass('hs-selected');
			top = this.$el.position().top;
			bottom = this.$el.position().top + this.$el.outerHeight(true);
			height = content.height();

			if (!((top > 0 && top < height) && (bottom > 0 && bottom < height))) {
				$.smoothScroll({
					scrollElement: content,
					scrollTarget: this.$el
				});
			}

			this.$el.find('.js-hs-place-user-with-avatar').tooltipster({
				content: function() {
					return self.$el.find('.js-hs-place-avatar-hidden').html()
				},
				maxWidth: 200,
				interactive: true
			});
		}
	});
	List.Hotspot = HotspotView.extend({
		template: "#hotspot-list-item",
		tagName: 'div',
		className: 'hs-tab-list-item',

		genericEvents: {
			'click': function(e) {
				this.select();
			}
		},

		modelEvents: {
			'selected' : 'selected',
			'deselected' : 'deselected'
		},

		initialize: function() {
			this.$el.prop("id", "hs-hotspot-id-" + this.model.get("id"));
			this.$el.attr("data-id", this.model.get("id"));

			this.events = _.extend({}, this.genericEvents, this.events);

			HotspotsManager.request('category:entities');
		},

		deselected: function() {
			this.$el.find('.hs-get-directions').css('display', 'none');

			this.$el.find('.hs-streetmap').css('display', 'none');
			this.$el.removeClass('hs-selected');
		},

		select: function(silent) {
			this.model.select();
			// trigger select on the marker collection
			if(!silent) {
				HotspotsManager.trigger('hotspot:selected', this.model);
			}
		},

		onRender: function() {
			var model = this.model, $galleria = '';

			if(model.get('multimedia'))
			{
				$galleria = this.$el.find(".hs-galleria");

				// Using setTimeout here as without it onImpression can't find the element on the page...
				setTimeout(function() {
					$galleria.onImpression({
						offset: 0,
						callback: function() {
							Galleria.run($galleria, {
								dataSource: model.get('multimedia')
							});
						},
						scrollable: '#js-hs-tabs-content > div'
					});
				}, 0)
			}
		}
	});

	List.TileHotspot = HotspotView.extend({
		template: "#hotspot-list-item",
		tagName: 'div',
		className: 'hs-tab-list-item hs-tab-list-item-tile',

		triggers: {
			'click .js-hs-close': 'close'
		},

		initialize: function() {
			this.$el.prop("id", "hs-hotspot-id-" + this.model.get("id"));
			this.$el.attr("data-id", this.model.get("id"));
		},

		onShow: function() {
			this.selected();
			this.$el.prepend('<div class="js-hs-close pull-right fa fa-times"></div>');
		}
	});

	List.NoHotspots = Marionette.ItemView.extend({
		 template: "#js-hs-empty-list-template",
		 className: "alert"
	 });

	List.Hotspots = Marionette.CollectionView.extend({
		emptyView: List.NoHotspots,
		itemView: List.Hotspot,
		timeout: null,

		events: {
			'mouseenter .hs-tab-list-item': 'highlight',
			'mouseleave .hs-tab-list-item': 'dehighlight'
		},

		highlight: function(e) {
			e.preventDefault();
			var id = $(e.currentTarget).data("id");
			HotspotsManager.trigger('marker:highlighted', this.collection.get(id));
		},

		dehighlight: function(e) {
			e.preventDefault();
			var id = $(e.currentTarget).data("id");
			HotspotsManager.trigger('marker:dehighlighted', this.collection.get(id));
		}
	});
});