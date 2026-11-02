new Class('compojoom.hotspots.tab', {
    
    Implements: [Events, Options],

    options: {
        tabSelector: '.tab',
        contentSelector: '.content',
        activeClass: 'active'
    },

    container: null,
    showNow: false,

    initialize: function(container, options) {
        this.setOptions(options);

        this.container = document.id(container);
        this.container.getElements(this.options.contentSelector).setStyle('display', 'none');

        this.container.addEvent('click:relay(' + this.options.tabSelector + ')', function(e, tab) {
            this.showTab(tab.get('data-id'), tab);
			
        }.bind(this));

        this.container.getElement(this.options.tabSelector).addClass(this.options.activeClass);
        this.container.getElement(this.options.contentSelector).setStyle('display', 'block');

        window.addEvent('showTab', function(tabId) {
            this.showTab(tabId);
        }.bind(this));
    },

    showTab: function(dataId, tab) {
		var content = '';
		this.container.getElements(this.options.contentSelector).each(function(el){
			if(el.get('data-id') == dataId) {			
				content = el;
			}
		});
        if (!tab) {
			this.container.getElements(this.options.tabSelector).each(function(el) {
				if(el.get('data-id') == dataId) {
					tab = el;
				}
			});
        }

        if (content) {
            this.container.getElements(this.options.tabSelector).removeClass(this.options.activeClass);
            this.container.getElements(this.options.contentSelector).setStyle('display', 'none');
            tab.addClass(this.options.activeClass);
            content.setStyle('display', 'block');
            this.fireEvent('change', dataId);
        }
		
    }, 

    closeTab: function(dataId) {
        var tabs     = this.container.getElements(this.options.tabSelector);
		tabs.each(function(tab) {
			if(tab.get('data-id') == dataId) {
				tab.destroy();
			}
		});
        this.container.getElements(this.options.contentSelector).each(function(content) {
			if(content.get('data-id') == dataId) {
				content.destroy();
			}
		});
        this.fireEvent('close', dataId);
    }
});