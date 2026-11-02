/**
 * Created by DanielDimitrov on 12.05.14.
 */
HotspotsManager.module("MapApp.Tiles", function (Tiles, HotspotsManager, Backbone, Marionette, $, _) {

	Tiles.TilesView = Backbone.View.extend({

		initialize: function() {
			this.map = HotspotsManager.request('map:object');
			this.filter = HotspotsManager.request('filter:entity');
			this.setCustomTiles();
		},

		/**
		 * We need the same params for both the tile + ajax request for markers
		 * @param coord
		 * @return {Array}
		 */
		getUrlParams: function (coord) {

			var zoom = this.map.mapObj.getZoom(),
				categories = this.filter.getCatIds().join(';'),
				search = this.filter.get('search'),
				params = [
					'x=' + coord.x,
					'y=' + coord.y,
					'z=' + zoom,
					'hs-language=' + HotspotsConfig.language
				];

			if (categories) {
				params.push('cats=' + categories);
			}
			if (search) {
				params.push('search=' + search);
			}

			return params;
		},

		setCustomTiles: function () {
			var self = this,
				customTile = {
					getTileUrl: function (coord, zoom) {
						var normalizedCoord = self.getNormalizedCoord(coord, zoom);

						// return if we have - coordiantes or if we are not showing any categories
						if (!normalizedCoord) {
							return null;
						}
						var params = self.getUrlParams(normalizedCoord);
						return HotspotsConfig.baseUrl + "?option=com_hotspots&task=tiles.create&format=png&" + params.join('&');
					},
					tileSize: new google.maps.Size(256, 256),
					name: "MarkersTile"
				},
				customTileMap = new google.maps.ImageMapType(customTile);

			this.map.mapObj.overlayMapTypes.clear();
			this.map.mapObj.overlayMapTypes.insertAt(0, customTileMap);
		},

		/**
		 * Normalizes the coords that tiles repeat across the x axis (horizontally)
		 * like the standard Google map tiles.
		 */
		getNormalizedCoord: function (coord, zoom) {
			var y = coord.y;
			var x = coord.x;

			// tile range in one direction range is dependent on zoom level
			// 0 = 1 tile, 1 = 2 tiles, 2 = 4 tiles, 3 = 8 tiles, etc
			var tileRange = 1 << zoom;

			// don't repeat across y-axis (vertically)
			if (y < 0 || y >= tileRange) {
				return null;
			}

			// repeat across x-axis
			if (x < 0 || x >= tileRange) {
				x = (x % tileRange + tileRange) % tileRange;
			}

			return {
				x: x,
				y: y
			};
		}

	});

	Tiles.MarkerTilesView = Backbone.GoogleMaps.MarkerView.extend({
		beforeRender: function() {
			var icon = new google.maps.MarkerImage(
				HotspotsConfig.rootUrl + '/media/com_hotspots/images/utils/trans-marker.png',
				new google.maps.Size(7, 7),
				null,
				new google.maps.Point(3, 3)
			);

			this.gOverlay.setIcon(icon);
			this.gOverlay.setVisible(true);

		},

		initialize: function() {
			// Show detail view on model select
			this.model.on("change:selected", function(model, isSelected) {

				if (isSelected) {
					HotspotsManager.trigger('tile:selected', model, isSelected);
					var self = this;
					this.highligtedMarker && this.highligtedMarker.setMap(null);
					this.gOverlay.setIcon(HotspotsConfig.rootUrl + '/media/com_hotspots/images/v4/icons/pin-16px.png');
					this.gOverlay.setZIndex(9999);
					this.gOverlay.setAnimation(google.maps.Animation.BOUNCE);
					setTimeout(function() {
						self.gOverlay.setAnimation(null);
					}, 2000);
				}
				else {
					var icon = new google.maps.MarkerImage(
						HotspotsConfig.rootUrl + '/media/com_hotspots/images/utils/trans-marker.png',
						new google.maps.Size(7, 7),
						null,
						new google.maps.Point(3, 3)
					);
					this.closeDetail();
					this.gOverlay.setIcon(icon);
					this.gOverlay.setAnimation(null);
				}
			}, this);
		},
		openDetail: function() {}
	});

	Tiles.MarkerTilesCollectionView = Backbone.GoogleMaps.MarkerCollectionView.extend({
		markerView: Tiles.MarkerTilesView
	})
});