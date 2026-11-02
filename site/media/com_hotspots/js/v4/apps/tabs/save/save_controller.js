/**
 * Created by DanielDimitrov on 24.03.14.
 */
HotspotsManager.module("TabsApp.Save", function(Save, HotspotsManager, Backbone, Marionette, $, _){

	Save.Controller = {
		add: function(id, title, content, dataName) {
			var tabs = HotspotsManager.request("tab:entities"),
				tab = {
					id: id,
					tabName: title,
					content: content
				};

			if(dataName) {
				tab.dataName = dataName;
			}

			tabs.add(tab, {merge: true});
		}
	}
});