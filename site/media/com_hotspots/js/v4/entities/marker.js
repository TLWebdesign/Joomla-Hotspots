/**
 * Created by DanielDimitrov on 24.03.14.
 */

HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){
	Entities.Marker = Backbone.GoogleMaps.Location.extend({

	});

	Entities.MarkerCollection = Backbone.Collection.extend({
		model: Entities.Marker,

		coord: {x: 0,y : 0},

		url: function() {
			var map = HotspotsManager.request('map:object'),
				filter = HotspotsManager.request('filter:entity'),
			    zoom = map.mapObj.getZoom(),
				categories = filter.getCatIds().join(';'),
				search = filter.get('search'),
				params = [
					'x=' + this.coord.x,
					'y=' + this.coord.y,
					'z=' + zoom,
					'hs-language=' + HotspotsConfig.language
				];

			if (categories) {
				params.push('cats=' + categories);
			}
			if (search) {
				params.push('search=' + search);
			}

			return HotspotsConfig.baseUrl + '?option=com_hotspots&view=tile&format=json&' + params.join('&');
		}
	});

	var entity;

	var API = {
		getMarkerCollection: function() {
			return new Entities.MarkerCollection();
		},
		getMarkerEntity: function() {
			if(!entity)
			{
				entity = new Entities.Marker(HotspotsMarker);
			}
			return entity;
		}
	};

	HotspotsManager.reqres.setHandler("marker:collection", function(options){
		return API.getMarkerCollection();
	});

	HotspotsManager.reqres.setHandler("marker:entity", function(){
		return API.getMarkerEntity();
	});
});
