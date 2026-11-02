new Class('compojoom.hotspots.helper', {
	language: {},
	localize: function(options) {
		this.language = options.localization;
	},

	/*get url parameter from hash*/
	gup: function ( name ){
		name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
		var hash = window.location.hash.replace('#!/', "");
		var regexS = name+"=([^&]*)";
		var regex = new RegExp( regexS );
		var results = regex.exec( hash );
		if( results == null ) {
			return "";
		} else {
			return results[1];
		}
	},

    exportAllEvents: function() {

        Array.each(this.eventsMap, function(obj) {
            this.exportEvents(obj.host, obj.events);
        }, this);
    },

	translate: function(key, def) {
		return Joomla.JText._(key, def);
	},

    exportEvents: function(hostobject, eventsobject) {
        var self = this, final = {}, on = "on";


        Object.each(eventsobject, function(method, event)  {
            // if we have method name, use it! Otherwise create onEventname function
            method = method || [on, event.capitalize()].join("");
            if (!self[method]) {
                // console.log("failed to find this." + method);
                self[method] = Function.from();
            }
            final[event] = self[method].bind(self);
        });

        hostobject.addEvents(final);
    },

	changeClass:function (element) {
		var siblings = element.getSiblings('span');
		siblings.removeClass('active');

		siblings.addClass('link');
	}
});