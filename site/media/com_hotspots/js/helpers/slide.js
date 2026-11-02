new Class('compojoom.hotspots.slide', {
	Implements: [Options, Events],
	options: {
		container: 'slider',
		inner: 'slider-inner',
		controls: {
			next: 'next',
			previous: 'previous'
		},
		type: 'li',
		useCss: true,
		showItems: 4,
		itemWidth: 40
	},
	innerWidth: 0,
	containerWidth: 0,
    
	initialize: function(options) {
		this.setOptions(options);
        
		this.o = this.options;
		this.setContainerWidth();
		
		this.innerWidth = this.calculateInnerWidth();
		var containerWidth = document.id(this.o.container).getStyle('width').toInt();


		if(this.innerWidth <= containerWidth) {
			this.removeMargin();
			this.removeControls();
			return;
		}

		this.begin();

	},
	
	begin: function() {
		document.id(this.o.inner).setStyle('width', this.innerWidth);
		
		
		this.containerWidth =  document.id(this.o.container).getStyle('width').toInt();
		
		this.addControls();
		var inner = document.id(this.o.inner);
		this.myFx = new Fx.Tween(inner, { 
			property: 'margin-left'
		});	
		
	},
	
	setContainerWidth: function() {
		if(!this.o.useCss) {
			document.id(this.o.container).setStyle('width', this.o.showItems*this.o.itemWidth );
		}
	},
	
	reset: function() {
		this.initialize(this.o);
	},
	
	removeMargin: function() {
		if( typeof this.myFx != 'undefined') {
			this.myFx.start(0);
		}
	},
	
	removeControls: function() {
		document.id(this.o.controls.previous).setStyle('display', 'none');
		document.id(this.o.controls.next).setStyle('display', 'none');
	},
	
	addControls: function() {
		var margin = document.id(this.o.inner).getStyle('margin-left').toInt();
		if(margin == 0) {
			document.id(this.o.controls.previous).setStyle('display', 'none');
		}
		document.id(this.o.controls.next).setStyle('display', 'block');
		
		
		document.id(this.o.controls.next).addEvent('click', function(){
			
			var margin = document.id(this.o.inner).getStyle('margin-left').toInt();
		
			var difference = this.containerWidth - this.innerWidth;
			if(margin > difference) {
				this.move(1);
			} 
		}.bind(this));  
		
		document.id(this.o.controls.previous).addEvent('click', function(){

			var margin = document.id(this.o.inner).getStyle('margin-left').toInt();

			if(margin < 0) {
				this.move(-1);
			} 


		}.bind(this));  
		
	},

	calculateInnerWidth: function() {
		var allElements = document.id(this.o.inner).getChildren(this.o.type);
		var width = allElements.length * this.o.itemWidth;
		return width;
	},
    
	move: function(direction) {
		var inner = document.id(this.o.inner);
		var margin = inner.getStyle('margin-left').toInt();
		var newMargin = 0;
		var difference = this.containerWidth - this.innerWidth;
		
		
		if(direction == 1) {
			newMargin = margin-this.o.itemWidth;
			if(newMargin < difference) {
				newMargin = difference;
			}
			this.myFx.start(newMargin);
		} else {
			newMargin = margin+this.o.itemWidth;
			if(newMargin > 0) {
				newMargin= 0;
			}
			this.myFx.start(newMargin);
		}

		if(newMargin == 0) {
			document.id(this.o.controls.next).setStyle('display', 'block');
			document.id(this.o.controls.previous).setStyle('display', 'none');
		} else if(difference < newMargin){
			document.id(this.o.controls.next).setStyle('display', 'block');
			document.id(this.o.controls.previous).setStyle('display', 'block');
		} else if(difference >= newMargin) {
			document.id(this.o.controls.next).setStyle('display', 'none');
			document.id(this.o.controls.previous).setStyle('display', 'block');
		}
	}
                                
});
