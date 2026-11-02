new Class('compojoom.hotspots.modules.print',{
	Implements: [Options, Events, compojoom.hotspots.helper],
	options: {
		showMenu: 1
	},
	eventsMap: [{
		host: window,
		events: {
			hotspotsPrint:null
		}
	}],
	initialize: function(options, sb) {
		this.sb = sb;
		this.setOptions(options);

		this.exportAllEvents();

		if(this.options.print.toInt()) {
			document.id('print-button').addEvent('click', function() {
				window.fireEvent('hotspotsPrint');
			});
		}

	},

	onHotspotsPrint:function () {
		var baseUrl = "http://maps.google.com/maps/api/staticmap?";
		var params = this.sb.getStaticMapParams();

		var url = baseUrl + params;
		var popupheight2 = 500;
		this.htmlPopUp(this.options.staticMapWidth, popupheight2,
			'<html><head><title>' + this.translate('COM_HOTSPOTS_PRINT','Print') + '</title></head><body Onload="window.print();">'
				+ '<img src="' + url + '"><br /><p align="right">'
				+ '<img src="' + this.options.baseUrl + 'media/com_hotspots/images/utils/print.png">'
				+ '<a href="javascript:print();">'
				+ '<font face="Verdana" size="2pt">Print</a></p></body></html>'
		);
	},

	htmlPopUp:function (w, h, site) {
		var x = screen.availWidth / 2 - w / 2;
		var y = screen.availHeight / 2 - h / 2;
		var popupWindow = window.open(
			'', '', 'width=' + w + ',height=' + h + ',left=' + x + ',top=' + y + ',screenX=' + x + ',screenY=' + y);
		popupWindow.document.write(site);
	}
});