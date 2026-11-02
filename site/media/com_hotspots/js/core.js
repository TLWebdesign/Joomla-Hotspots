new Class('compojoom.hotspots.core', {
    Implements:[Events],

    moduleData:{},

	addSandbox: function(container, options) {
		return this.sandbox = new compojoom.hotspots.sandbox(container, options);
	},

    addModule:function (moduleId) {
        var args = Array.prototype.slice.call(arguments, 1);

        if (this.sandbox == null) {
            this.sandbox = this.addSandbox('map_canvas', arguments[1]);
        }

        this.moduleData[moduleId] = args;
        this.moduleData[moduleId].push(this.sandbox);
    },
    start:function (moduleId) {
        var module = compojoom.hotspots.modules[moduleId];
        if (typeof module != 'undefined') {
            module.apply(Object.create(module.prototype), this.moduleData[moduleId])
        } else {
            console.log(moduleId + ' could not be found');
        }

    },
    startAll:function () {
        var moduleId, moduleData = this.moduleData;

        for (moduleId in moduleData) {
            this.start(moduleId);
        }
    }
});
