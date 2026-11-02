/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("MenuApp.Show", function(Show, HotspotsManager, Backbone, Marionette, $, _){

	Show.Controller = {
		show: function() {
			this.view = new Show.MenuView();

			this.view.on("show", function(){
				HotspotsManager.trigger('tabs:list');
			});

			HotspotsManager.menuRegion.show(this.view);
		},
		open: function() {
			if(this.view && !this.view.opened) {
				this.view.toggleMenu();
			}
		},
		resize: function() {
			this.view && this.view.resize();
		}
	};
});

