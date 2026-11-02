/**
 * this class is used for both front-end and back-end marker submit
 * When making changes -> check both locations for errors!
 *
 * @author Daniel Dimitrov - compojoom.com
 * @license GPL
 */
new Class('compojoom.hotspots.modules.submit', {
  Implements: [Options, Events, compojoom.hotspots.helper],

  isMenuOpen: false,
  fullScreen: false,
  options: {
    street: 'jform_street',
    zip: 'jform_plz',
    city: 'jform_town',
    country: 'jform_country',
    state: 'jform_administrative_area_level_1',
    latitude: 'jform_gmlat',
    longitude: 'jform_gmlng'
  },
  xhr: {},

  initialize: function (options, sb) {
    var self = this
    this.sb = sb
    this.setOptions(options)

    window.addEvent('hotspotsDispatch:once', function () {
      self.findInitialPosition()
      self.formActions()
      google.maps.event.addListener(self.sb.getMap(), 'click', function (event) {
        self.createMarker(event.latLng)
      })
      // set the small zoom control
      self.sb.getMap().setOptions({
        zoomControlOptions: {
          style: google.maps.ZoomControlStyle.SMALL
        }

      })

    })

    var request = new Request.HTML({
      url: this.options.baseUrl + '?option=com_hotspots&view=customfields&format=raw&catid=' + document.id('jform_catid').get('value') + '&id=' + document.id('hotspot-id').get('value'),
      onSuccess: function (responseTree, responseElements, responseHTML) {
        document.id('custom-fields').set('html', responseHTML)
      }
    })

    request.send()

    document.id('jform_catid').addEvent('change', function () {
      request.send({'url': self.options.baseUrl + '?option=com_hotspots&view=customfields&format=raw&catid=' + this.get('value') + '&id=' + document.id('hotspot-id').get('value')})
    })
  },

  /**
   * set the map position if lat and long fields have coordinates
   */
  findInitialPosition: function () {
    var lat = document.id(this.options.latitude).get('value')
    var lng = document.id(this.options.longitude).get('value')

    if (lat && lng) {
      var position = new google.maps.LatLng(lat, lng)
      this.createMarker(position)
      this.sb.getMap().setCenter(position)
    }
  },

  createMarker: function (position) {
    var self = this
    // remove the old markers
//        this.sb.markers.each(function (marker) {
//            marker.destroy();
//        });
    Object.each(this.sb.markers, function (marker, key) {
      marker.destroy()
      delete self.sb.markers[key]
    })
//        this.sb.markers.empty();

    var markerOptions = {
      title: this.translate('COM_HOTSPOTS_DRAG_ME', 'Drag me'),
      draggable: true
    }
    this.marker = this.sb.createMarker(position, markerOptions, 'none', 'none')
    this.marker.addEvents({
      drag: function () {
        self.updateFormCoordinates(this.getPosition())
      },
      dragend: function () {
        if (self.isSticky()) {
          self.updateFormAddress(this.getPosition())
        }
      }
    })
    // update the form address and form coordiantes
    self.updateFormCoordinates(position)
    if (self.isSticky()) {
      self.updateFormAddress(position)
    }

    return this.marker
  },

  updateMarkerPosition: function () {
    var street = document.id(this.options.street).get('value')
    var zip = document.id(this.options.zip).get('value')
    var town = document.id(this.options.city).get('value')
    var country = document.id(this.options.country).get('value')
    var state = document.id(this.options.state) ? document.id(this.options.state).get('value') : ''

    var address = street + ', ' + zip + ',' + town + ', ' + state + ', ' + country
    this.sb.getGeocoder().geocode({
      address: address
    }, function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        var location = results[0].geometry.location
        this.getMarker(location).setPosition(location)
        this.sb.getMap().setCenter(location)
        if (this.isSticky()) {
          this.updateFormCoordinates(location)
        }
      }
    }.bind(this))
  },

  updateFormAddress: function (latlng) {
    console.log('el', latlng)
    this.sb.getGeocoder().geocode({
      'latLng': latlng
    }, function (results, status) {
      var streetNumber = '',
        streetName = '',
        country = '',
        postalCode = '',
        state = '',
        city = ''
      if (status == google.maps.GeocoderStatus.OK) {
        results[0].address_components.each(function (el) {

          el.types.each(function (type) {
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

        if (this.options.userInterface == 0) {
          document.id(this.options.street).set('value', streetName + ' ' + streetNumber)
        } else {
          document.id(this.options.street).set('value', streetNumber + ' ' + streetName)
        }
        document.id(this.options.zip).set('value', postalCode)
        document.id(this.options.city).set('value', city)
        document.id(this.options.country).set('value', country)
        document.id(this.options.state) && document.id(this.options.state).set('value', state)
      }
    }.bind(this))

  },
  updateMarkerFromCoordinates: function (latlng) {
    this.getMarker(latlng).setPosition(latlng)
    this.sb.getMap().setCenter(latlng)
  },

  updateFormCoordinates: function (position) {
    document.id(this.options.longitude).set('value', position.lng())
    document.id(this.options.latitude).set('value', position.lat())
  },

  geolocation: function () {
    navigator.geolocation.getCurrentPosition(function (position) {
      document.id('hotspots-geolocation-info').set('html', '')
      var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude)
      if (this.isSticky()) {
        this.updateFormAddress(latlng)
      }
      this.updateMarkerFromCoordinates(latlng)
      this.updateFormCoordinates(latlng)
    }.bind(this), function () {
      document.id('hotspots-geolocation-info').set('html', this.translate('COM_HOTSPOTS_GEOLOCATION_NO_SUPPORT', 'Your browser does not support geolocation'))
    }.bind(this))
  },

  formActions: function () {
    if (navigator.geolocation != 'undefined') {
      document.id('hotspots-geolocation').addEvent('click', this.geolocation.bind(this))
    } else {
      document.id('hotspots-geolocation').setStyle('display', 'none')
    }

    var addresses = [this.options.street, this.options.zip, this.options.city, this.options.country]
    var keyupTimer
    addresses.each(function (addressElement) {
      document.id(addressElement).addEvent('keyup', function () {
        if (this.isSticky()) {
          clearTimeout(keyupTimer)
          keyupTimer = setTimeout(function () {
            this.updateMarkerPosition()
          }.bind(this), 1000)
        }
      }.bind(this))
    }.bind(this))

    var coordinatesFields = [this.options.latitude, this.options.longitude]
    coordinatesFields.each(function (field) {
      document.id(field).addEvent('keyup', function () {
        clearTimeout(keyupTimer)
        keyupTimer = setTimeout(function () {
          var lat = document.id(this.options.latitude).get('value')
          var lng = document.id(this.options.longitude).get('value')
          var latlng = new google.maps.LatLng(lat, lng)
          if (this.isSticky()) {
            this.updateFormAddress(latlng)
          }
          this.updateMarkerFromCoordinates(latlng)
        }.bind(this), 1000)
      }.bind(this))
    }.bind(this))

  },

  isSticky: function () {
    return document.id('jform_params_sticky').get('value').toInt()
  },

  getMarker: function (location) {
    if (this.marker == null) {
      this.marker = this.createMarker(location)
    }
    return this.marker
  }

})