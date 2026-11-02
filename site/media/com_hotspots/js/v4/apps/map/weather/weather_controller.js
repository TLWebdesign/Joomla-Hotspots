/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("MapApp.Weather", function (Weather, HotspotsManager, Backbone, Marionette, $, _) {

	var Controller = Marionette.Controller.extend({
		listener: null,

		draw: function (map) {
			this.map = map;

			var infowindow = new google.maps.InfoWindow();

			// Clear the previous listener
			google.maps.event.removeListener(this.listener);
			// Sets up and populates the info window with details
			this.listener = map.data.addListener('click', function (event) {
				infowindow.setContent(
					"<img src=" + event.feature.getProperty("icon") + ">"
					+ "<br /><strong>" + event.feature.getProperty("city") + "</strong>"
					+ "<br />" + event.feature.getProperty("temperature") + "&deg;" + (HotspotsConfig.weatherTemperatureUnit == 'metric' ? 'C' : 'F')
					+ "<br />" + event.feature.getProperty("weather")
				);
				infowindow.setOptions({
					position: {
						lat: event.latLng.lat(),
						lng: event.latLng.lng()
					},
					pixelOffset: {
						width: 0,
						height: -15
					}
				});
				infowindow.open(map);
			});

			this.checkIfDataRequested();
		},

		toggle: function (map) {
			var model = HotspotsManager.request('map:entity');
			this.map = map;

			if (model.get('weatherLayer')) {
				if (this.drawn) {
					this.resetData();
				}
				else {
					this.draw(map)
				}
			}
			else {
				this.resetData();
			}
		},

		checkIfDataRequested: function () {
			// Stop extra requests being sent
			while (self.gettingData === true) {
				request.abort();
				self.gettingData = false;
			}
			this.getCoords();
		},

		// Get the coordinates from the Map bounds
		getCoords: function () {
			var bounds = this.map.getBounds();
			var NE = bounds.getNorthEast();
			var SW = bounds.getSouthWest();
			this.getWeather(NE.lat(), NE.lng(), SW.lat(), SW.lng());
		},

		// Make the weather request
		getWeather: function (northLat, eastLng, southLat, westLng) {
			var self = this;
			self.gettingData = true;
			var requestString = "https://api.openweathermap.org/data/2.5/box/city?bbox="
				+ westLng + "," + northLat + "," //left top
				+ eastLng + "," + southLat + "," //right bottom
				+ this.map.getZoom()
				+ "&cluster=yes&format=json&units=" + HotspotsConfig.weatherTemperatureUnit
				+ "&APPID=" + HotspotsConfig.weatherLayerApiKey;
			self.request = new XMLHttpRequest();
			self.request.onload = function () {
				var results = JSON.parse(this.responseText);
				if (results.cod === 200 && results.list.length > 0) {
					self.resetData();
					for (var i = 0; i < results.list.length; i++) {
						self.geoJSON.features.push(self.jsonToGeoJson(results.list[i]));
					}
					self.drawIcons(self.geoJSON);
				}
			};
			self.request.open("get", requestString, true);
			self.request.send();
		},

		// For each result that comes back, convert the data to geoJSON
		jsonToGeoJson: function (weatherItem) {
			var feature = {
				type: "Feature",
				properties: {
					city: weatherItem.name,
					weather: weatherItem.weather[0].main,
					temperature: weatherItem.main.temp,
					min: weatherItem.main.temp_min,
					max: weatherItem.main.temp_max,
					humidity: weatherItem.main.humidity,
					pressure: weatherItem.main.pressure,
					windSpeed: weatherItem.wind.speed,
					windDegrees: weatherItem.wind.deg,
					windGust: weatherItem.wind.gust,
					icon: "http://openweathermap.org/img/w/" + weatherItem.weather[0].icon + ".png",
					coordinates: [weatherItem.coord.Lon, weatherItem.coord.Lat]
				},
				geometry: {
					type: "Point",
					coordinates: [weatherItem.coord.Lon, weatherItem.coord.Lat]
				}
			};
			// Set the custom marker icon
			this.map.data.setStyle(function (feature) {
				return {
					icon: {
						url: feature.getProperty('icon'),
						anchor: new google.maps.Point(25, 25)
					}
				};
			});

			// returns object
			return feature;
		},

		// Add the markers to the map
		drawIcons: function (weather) {
			this.map.data.addGeoJson(this.geoJSON);
			// Set the flag to finished
			this.gettingData = false;
			this.drawn = true;
		},

		// Clear data layer and geoJSON
		resetData: function (map) {
			var map = map ? map : this.map;
			this.geoJSON = {
				type: "FeatureCollection",
				features: []
			};
			map.data.forEach(function (feature) {
				map.data.remove(feature);
			});
			this.drawn = false;
		}
	});

	Weather.Controller = new Controller();
});

