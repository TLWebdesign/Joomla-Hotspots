/**
 * Created by DanielDimitrov on 23.03.14.
 */
HotspotsManager.module("TabsApp.TabItem", function(TabItem, HotspotsManager, Backbone, Marionette, $, _){

	TabItem.Item = Marionette.ItemView.extend({
		tagName: "li",
		template: "#tab-template",
		attributes: function() {
			return {
				'data-name': this.model.get('dataName')
			}
		}
	});

	TabItem.View = Marionette.CollectionView.extend({
		itemView: TabItem.Item,
		itemViewContainer: "ul",
		tagName: "ul"
	});

});