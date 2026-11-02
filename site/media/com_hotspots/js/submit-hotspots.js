jQuery(document).ready(function ($) {

  var options = {
    street: '#jform_street',
    zip: '#jform_plz',
    city: '#jform_town',
    country: '#jform_country',
    state: '#jform_administrative_area_level_1',
    latitude: '#jform_gmlat',
    longitude: '#jform_gmlng'
  }

  var map
  var marker
  var geocoder = new google.maps.Geocoder()

  function init () {
    options = $.extend(options, window.hotspotsConf)

    map = new google.maps.Map(document.getElementById('map-add'), {
      center: {lat: 0, lng: 0},
      zoom: parseInt(options.mapStartZoom)
    })

    setInitialMapCenter()
    setMapOptions()

    google.maps.event.addListener(map, 'click', function (event) {
      createMarker(event.latLng)
    })

    $.ajax({
      url: options.baseUrl + '?option=com_hotspots&view=customfields&format=raw&catid=' + $('#jform_catid').val() + '&id=' + $('#hotspot-id').val(),
    }).done(function (data) {
      $('#custom-fields').html(data)
    })

    $('#jform_catid').change(function () {
      $.ajax({
        'url': options.baseUrl + '?option=com_hotspots&view=customfields&format=raw&catid=' + $('#jform_catid').val() + '&id=' + $('#hotspot-id').val()
      }).done(function (data) {
        $('#custom-fields').html(data)
      })
    })

    // Find initial marker position after the default map center has been complited
    $(window).on('hotspot:map:center:set', function() {
      findInitialPosition()
    })


    formActions()
  }

  function setMapOptions () {
    google.maps.visualRefresh = options.visualRefresh
    var mapOptions = {
      panControl: options.panControl,
      panControlOptions: {
        position: google.maps.ControlPosition[options.panControlPosition]
      },
      zoomControl: options.zoomControl,
      zoomControlOptions: {
        style: google.maps.ZoomControlStyle[options.zoomControlStyle],
        position: google.maps.ControlPosition[options.zoomControlPosition]
      },
      mapTypeControl: options.mapTypeControl,
      mapTypeControlOptions: {
        style: google.maps.MapTypeControlStyle[options.mapTypeControlStyle],
        position: google.maps.ControlPosition[options.mapTypeControlPosition]
      },
      scaleControl: options.scaleControl,
      streetViewControl: options.streetViewControl,
      streetViewControlOptions: {
        position: google.maps.ControlPosition[options.streetViewPosition]
      },
      overviewMapControl: options.overviewMapControl,
      scrollwheel: options.scrollwheel,
      fullscreenControl: options.fullscreenControl

    }

    // add the styled maps option
    if (options.styledMaps) {
      try {
        mapOptions.styles = JSON.decode(options.styledMaps)
      } catch (exception) {
        console.log('Hotspots error: provided map style is incorrect')
      }
    }

    map.setOptions(mapOptions)
    setMapType()

    if (options.trafficLayer == 1) {
      var trafficLayer = new google.maps.TrafficLayer()
      trafficLayer.setMap(map)
    }

    if (options.transitLayer == 1) {
      var transitLayer = new google.maps.TransitLayer()
      transitLayer.setMap(map)
    }

    if (options.bicyclingLayer == 1) {
      var bikeLayer = new google.maps.BicyclingLayer()
      bikeLayer.setMap(map)
    }
  }

  function setMapType () {
    map.setMapTypeId(google.maps.MapTypeId[{
      0: 'ROADMAP',
      1: 'ROADMAP',
      2: 'SATELLITE',
      3: 'HYBRID',
      4: 'TERRAIN'
    }[options.mapType]])
  }

  function setInitialMapCenter () {
    var latLng
    var center = options.mapStartPosition
    if (((center).replace(/\s+/g, '')).match(/^-?\d+\.\d+\,-?\d+\.\d+$/)) {
      center = (center.replace(/\s+/g, '')).split(',')
      latLng = {lat: center[0], lng: center[1]}
    }

    if (latLng) {
      map.setCenter(latLng)
      $(window).trigger('hotspot:map:center:set')
      return
    }

    // geocode
    geocoder.geocode({'address': center}, function (point) {
      if (!point.length) {
        alert(center + ' ' + options.notFound)
      } else {
        map.setCenter(point[0].geometry.location)
      }

      $(window).trigger('hotspot:map:center:set')
    })
  }

  function findInitialPosition () {
    var lat = $(options.latitude).val()
    var lng = $(options.longitude).val()

    if (lat && lng) {
      var position = new google.maps.LatLng(lat, lng)
      createMarker(position)
      map.setCenter(position)
    }
  }

  function createMarker (position) {

    marker && marker.setMap(null)

    marker = new google.maps.Marker({
      position: position, map: map,
      title: translate('COM_HOTSPOTS_DRAG_ME', 'Drag me'),
      draggable: true
    })

    google.maps.event.addListener(
      marker,
      'drag',
      function (e) {
        updateFormCoordinates(e.latLng)
      }
    )

    google.maps.event.addListener(
      marker,
      'dragend',
      function (e) {
        if (isSticky()) {
          updateFormAddress(e.latLng)
        }
      }
    )

    updateFormCoordinates(position)

    if (isSticky()) {
      updateFormAddress(position)
    }

    return marker
  }

  function updateFormAddress (latlng) {

    geocoder.geocode({
      'latLng': latlng
    }, function (results, status) {
      var streetNumber = '',
        streetName = '',
        country = '',
        postalCode = '',
        state = '',
        city = ''
      if (status == google.maps.GeocoderStatus.OK) {
        results[0].address_components.forEach(function (el) {

          el.types.forEach(function (type) {
            if (type == 'street_number') {
              streetNumber = el.long_name
            }

            if (type == 'route') {
              streetName = el.long_name
            }

            if (type == 'country') {
              country = el.long_name
            }

            if (type == 'locality') {
              city = el.long_name
            }

            if (type == 'administrative_area_level_1') {
              state = el.long_name
            }
          })
        })

        var postalCodes = results[0].address_components.filter(function (val) {
          return val.types.includes('postal_code')
        })

        if (postalCodes.length) {
          postalCode = postalCodes[0].long_name
        }

        if (options.userInterface == 0) {
          $(options.street).val(streetName + ' ' + streetNumber)
        } else {
          $(options.street).val(streetNumber + ' ' + streetName)
        }
        $(options.zip).val(postalCode)
        $(options.city).val(city)
        $(options.country).val(country)
        $(options.state) && $(options.state).val(state)
      }
    })

  }

  function geolocation () {
    navigator.geolocation.getCurrentPosition(function (position) {
      $('#hotspots-geolocation-info').html('')
      var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
      if (isSticky()) {
        updateFormAddress(latlng)
      }
      updateMarkerFromCoordinates(latlng)
      updateFormCoordinates(latlng)
    }, function () {
      $('#hotspots-geolocation-info').html(translate('COM_HOTSPOTS_GEOLOCATION_NO_SUPPORT', 'Your browser does not support geolocation'))
    })
  }

  function formActions () {
    if (typeof navigator.geolocation !== 'undefined') {
      $('#hotspots-geolocation').on('click', geolocation)
    } else {
      $('#hotspots-geolocation').setStyle('display', 'none')
    }

    var addresses = [options.street, options.zip, options.city, options.country]
    var keyupTimer
    addresses.forEach(function (addressElement) {
      $(addressElement).keyup(function () {

        if (isSticky()) {
          clearTimeout(keyupTimer)
          keyupTimer = setTimeout(function () {
            updateMarkerPosition()
          }, 1000)
        }
      })
    })

    var coordinatesFields = [options.latitude, options.longitude]
    coordinatesFields.forEach(function (field) {
      $(field).keyup(function () {
        clearTimeout(keyupTimer)
        keyupTimer = setTimeout(function () {
          var lat = $(options.latitude).val()
          var lng = $(options.longitude).val()
          var latlng = new google.maps.LatLng(lat, lng)
          if (isSticky()) {
            updateFormAddress(latlng)
          }
          updateMarkerFromCoordinates(latlng)
        }, 1000)
      })
    })

  }

  function updateMarkerPosition () {
    var street = $(options.street).val()
    var zip = $(options.zip).val()
    var town = $(options.city).val()
    var country = $(options.country).val()
    var state = $(options.state) ? $(options.state).val() : ''

    var address = street + ', ' + zip + ',' + town + ', ' + state + ', ' + country

    geocoder.geocode({
      address: address
    }, function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        var location = results[0].geometry.location
        if (!marker) {
          marker = createMarker(location)
        }
        marker.setPosition(location)
        map.setCenter(location)
        if (isSticky()) {
          updateFormCoordinates(location)
        }
      }
    })
  }

  function updateMarkerFromCoordinates (latlng) {
    if (!marker) {
      marker = createMarker(latlng)
    }
    marker.setPosition(latlng)
    map.setCenter(latlng)
  }

  function translate (key, def) {
    return Joomla.JText._(key, def)
  }

  function isSticky () {
    return parseInt($('#jform_params_sticky').val())
  }

  function updateFormCoordinates (position) {
    $(options.longitude).val(position.lng())
    $(options.latitude).val(position.lat())
  }

  init()
})
