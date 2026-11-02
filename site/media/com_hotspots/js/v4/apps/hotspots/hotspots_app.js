/**
 * Created by DanielDimitrov on 26.02.14.
 */
HotspotsManager.module("HotspotsApp", function(HotspotsApp, HotspotsManager, Backbone, Marionette, $, _){

	var API = {
		renderHotspotsInTab: function() {
			HotspotsApp.Tabs.Controller.render();
		},
		showList: function(hotspots) {
			HotspotsApp.List.Controller.showList(hotspots);
		},
		markerSelected: function(model, isSelected) {
			HotspotsApp.List.Controller.selected(model, isSelected);
		},
		setPage: function(page) {
			HotspotsApp.List.Controller.setPage(page);
		},
		shotTileHotspot: function(hotspot) {
			HotspotsApp.List.Controller.shotTileHotspot(hotspot);
		}
	};

	HotspotsManager.on('start:page', function(page) {
		API.setPage(page);
	});

	HotspotsManager.on("tabs:ready", function(){
		API.renderHotspotsInTab();
	});

	this.listenTo(HotspotsManager, "hotspots:fetchedData", function(hotspots) {
		return API.showList(hotspots);
	});

	HotspotsManager.on("marker:selected", function(model, isSelected) {
		API.markerSelected(model, isSelected);
	});

	HotspotsManager.on("tile:hotspot:loaded", function(model) {
		API.shotTileHotspot(model);
	});
});