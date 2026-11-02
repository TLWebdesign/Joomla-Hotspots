/**
 * Created by DanielDimitrov on 26.02.14.
 */
HotspotsManager.module("Entities", function(Entities, HotspotsManager, Backbone, Marionette, $, _){

	Entities.TabCollection  = Backbone.Collection.extend();

	var initTabEntities = function() {
		Entities.tabs = new Entities.TabCollection(
			HotspotsConfig.tabs
		);
	};

	var API = {
		getTabs: function() {
			if(Entities.tabs === undefined){
				initTabEntities();
			}
			return Entities.tabs;
		}
	};

	HotspotsManager.reqres.setHandler("tab:entities", function(){
		return API.getTabs();
	});
});