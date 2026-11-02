new Class('compojoom.hotspots.modules.categories', {
    Implements:[Options, Events],
    options:{
        showMenu:1
    },
    initialize:function (categoriesPanel, htmlElement, options) {
        this.setOptions(options);

        var self = this, categoryIds = $$(categoriesPanel + ' ' + htmlElement);

        categoryIds.addEvent('click', function (e) {
            e.stop();
            window.fireEvent('route', ['category', this.get('data-id')]);
            window.fireEvent('categoryClicked', this.get('data-id'));
        });
        new compojoom.hotspots.slide({
            container:'hotspots-categories-inner',
            inner:'hotspots-category-items',
            controls:{
                next:'cat-forward',
                previous:'cat-back'
            },
            useCss:false,
            type:'span',
            showItems:self.options.numOfCatsToShow.toInt(),
            itemWidth:42
        });
    }
});