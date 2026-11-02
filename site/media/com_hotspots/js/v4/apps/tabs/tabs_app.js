/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("TabsApp", function(TabsApp, HotspotsManager, Backbone, Marionette, $, _){

	var API = {
		listTabs: function() {
			TabsApp.List.Controller.create();
		},
		addTab: function(id, title, content, dataName){
			TabsApp.Save.Controller.add(id, title, content, dataName);
		},
		refresh: function(){
			TabsApp.List.Controller.refresh();
		},
		change: function(name) {
			TabsApp.List.Controller.change(name);
		},
		remove: function(name) {
			TabsApp.Remove.Controller.remove(name);
			this.change('hotspots');
		}
	};

	HotspotsManager.on('tabs:list', function(){
		API.listTabs();
	});

	HotspotsManager.on('tabs:refresh', function(){
		API.refresh();
	});

	HotspotsManager.on('tabs:add', function(id, title, content, dataName){
		API.addTab(id, title, content, dataName);
	});

	HotspotsManager.on('tabs:change', function(name){
		API.change(name);
	});

	HotspotsManager.on('tabs:remove', function(name){
		API.remove(name);
	});

	HotspotsManager.on('tabs:ready', function(name){
		API.change(HotspotsConfig.startTab);
	});
});