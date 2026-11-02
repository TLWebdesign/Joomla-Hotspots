/**
 * Hotspots: the map scripts (js/v4) read the globals HotspotsConfig, HotspotsMarker and HotspotsTour.
 * PHP passes them as script options (Joomla.getOptions).
 */
(function (window, Joomla) {
	'use strict';

	window.HotspotsConfig = Joomla.getOptions('com_hotspots.config', {});

	var marker = Joomla.getOptions('com_hotspots.marker');
	var tour = Joomla.getOptions('com_hotspots.tour');

	if (marker) {
		window.HotspotsMarker = marker;
	}

	if (tour) {
		window.HotspotsTour = tour;
	}
}(window, Joomla));
