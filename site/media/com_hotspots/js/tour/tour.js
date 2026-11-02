hopscotch.registerHelper('hotspotsTab', function() {
	hsOpenMenuForBubble();

	jQuery("#js-tabs-region li[data-name='hotspots'] a").trigger('click');

	hopscotch.refreshBubblePosition();
});
hopscotch.registerHelper('directionsTab', function() {
	hsOpenMenuForBubble();

	jQuery("#js-tabs-region li[data-name='directions'] a").trigger('click');

	hopscotch.refreshBubblePosition();
});
hopscotch.registerHelper('cookie', function() {
	jQuery.cookie("hs-toured", "toured");
});

var hsTop = 0;
hopscotch.registerHelper('startEnd', function() {
	var bubble = jQuery('.hopscotch-bubble');
	if(!hsTop) {
		hsTop = bubble.height() + parseInt(bubble.css('top')) + 50;
		bubble.css('top', hsTop);
	}
	else {
		bubble.css('top', hsTop);
	}

	jQuery('.hopscotch-bubble-arrow-container').hide();
});

// Start the tour!
if(!jQuery.cookie('hs-toured')) {
	jQuery(document).ready(function() {
		$map = jQuery("#js-hs-main-app");

		// Using setTimeout here as without it onImpression can't find the element on the page...
		setTimeout(function() {
			$map.onImpression({
				offset: 0,
				callback: function() {
					hopscotch.startTour(HotspotsTour);
				}
			});
		}, 0)
	});
}

/**
 * Opens the menu and refreshes the bubble position
 */
function hsOpenMenuForBubble() {
	var $ = jQuery;
	if($('#toggle-menu').hasClass('toggle-off'))
	{
		$('#toggle-menu').trigger('click');
	}

	// We need to refresh the position of the bubble if the menu was closed
	HotspotsManager.on('menu:slide', function() {
		hopscotch.refreshBubblePosition();
	})
}