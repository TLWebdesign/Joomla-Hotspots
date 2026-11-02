/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("MapApp.Markers", function (Markers, HotspotsManager, Backbone, Marionette, $, _) {

	Markers.Location = Backbone.GoogleMaps.LocationCollection.extend({
		initialize: function() {
			// Deselect other models on model select
			// ie. Only a single model can be selected in a collection
			this.on("change:highlighted", function(selectedModel, isSelected) {
				if (isSelected) {
					this.each(function(model) {
						if (selectedModel.cid !== model.cid) {
							model.dehighlight();
						}
					});
				}
			}, this);
		}
	});

	Markers.Controller = {
		places: false,
		render: false,
		addressMarker: '',
		getMarkerCollection: function () {
			if (!this.places) {
				this.places = new Markers.Location();
			}
			return this.places;
		},

		draw: function (hotspots) {

			if(!this.render) {
				this.places = this.getMarkerCollection();

				this.places.reset(hotspots.toJSON());
				// if we use the pagination in the menu, there is loaded data event.
				// So instead we use the sync of the hotspots
				hotspots.on('sync', function() {
					this.places.set(hotspots.toJSON());
				}, this);

				var map = HotspotsManager.request("map:object");
				this.markerCollectionView = new Markers.MarkerCollectionView({
					collection: this.places,
					map: map.mapObj
				});
				this.markerCollectionView.render();
				this.render = true;
			}
		},

		fetchMarkers: function () {
			var entities = HotspotsManager.request('hotspots:entities');
			entities.done(function (hotspots) {
				HotspotsManager.trigger('hotspots:fetchedData', hotspots);
			});
		},

		selected: function(model) {
			var selected = this.places.get(model.get('id'));

			if (selected) {

				selected.select();
				this.places.add(selected);
			}
		},

		highlighted: function(model) {
			var highlighted = this.places.get(model.get('id'));
			highlighted.highlight();
		},
		dehighlighted: function(model) {
			var dehighlighted = this.places.get(model.get('id'));
			dehighlighted.dehighlight();
		},

		renderAddress: function(results, status) {
			var map = HotspotsManager.request("map:object");

			if (status == google.maps.GeocoderStatus.OK) {

				map.mapObj.setCenter(results[0].geometry.location);
				this.addressMarker = new google.maps.Marker({
					map: map.mapObj,
					position: results[0].geometry.location
				});
			}
		},

		clearAddressMarker: function(){
			this.addressMarker.setMap(null);
		}
	};

});

