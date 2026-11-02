new Class('compojoom.hotspots.modules.categories', {
	lazyLoading: false,
	
	initialize: function() {
		var self = this;
		this.control = 'jform_params_';
		document.id('select-icon').addEvent('change', function() {
			this.editIconForm();
		}.bind(this));

		// Copy an uploaded image
		var input = document.querySelector('#'+this.control +'hs_marker_image');
		input.onchange = function() {
			document.id(self.control+'icon').value = '[upload]'+(this).get('value');
		};

		this._resetElements();
	},
	
	editIconForm: function() {
		var selected = document.id('select-icon').get('value');

		if (selected == "new") {
			this._resetElements();
			document.id(this.control +'hs_marker_image').getParent('div.control-group').setStyle('display', 'block');
		}

		if (selected == "sample") {
			this._resetElements();
			document.id('select-sample-image').setStyle('display','block');
			this.loadImages();
		}
	},
	
	_resetElements: function() {
		document.id('select-sample-image').setStyle('display','none');
		if(document.id(this.control +'hs_marker_image').getParent('div.control-group'))
		{
			document.id(this.control +'hs_marker_image').getParent('div.control-group').setStyle('display', 'none');
		}
	},

	loadImages: function() {
		if(this.lazyLoading === false) {
			new LazyLoad({
				range: 50, 
				container: 'select-sample-image'
			});
			this.lazyLoading = true;
		}
		
		var self = this;
		document.id('select-sample-image').getElements('div').addEvent('click', function() {
			self.addicon(this.getElement('span').get('data-id'),this.getElement('img').get('src'), this.getElement('img').get('title'));
		})
	},

	addicon: function(icon, path, title) {
		document.id(this.control+'icon').value = '[sample]/sample/'+icon;
		
		alert('Sample Icon ' + title + ' selected');
		
		var categoryIcon = document.id('category-icon').getElement('img');
		if(categoryIcon) {
			categoryIcon.set('src', path);
		} else {
			var image = new Element('img', {
				src: path
			});
			document.id('category-icon').set('html', '');
			image.inject(document.id('category-icon'));
		}
		document.id('category-icon').removeClass('validation-failed');
	}
});

