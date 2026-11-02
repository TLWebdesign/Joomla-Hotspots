new Class('compojoom.hotspots.modules.markersgeocode', {
	Implements:[Options, Events, compojoom.hotspots.helper],

	geocodeModal:function () {
		this.geocodeModal = new LightFace({
			content:this.translate('COM_HOTSPOTS_GEOCODING_NOTICE',''),
			buttons:[
				{
					title:this.translate('COM_HOTSPOTS_GEOCODE', 'geocode'),
					color:'blue',
					event:function () {
						var checkboxes = $$(document.forms['adminForm'].elements['cid[]']);
						var ids = [];
						checkboxes.each(function (checkbox) {
							if (checkbox.checked) {
								ids.push(checkbox.get('value'));
							}
						});
						var ajax = new Request.JSON({
							url:'index.php?option=com_hotspots&task=hotspots.geocode',
							data:{cid:ids },
							onRequest:function () {
								this.geocodeModal.fade();
							}.bind(this),
							onSuccess:function (response) {
								this.geocodeModal.messageBox.set('html', response.message);
								this.geocodeModal.hideButton(this.translate('COM_HOTSPOTS_GEOCODE'));
							}.bind(this),
							onComplete:function () {
								this.geocodeModal.unfade();
								checkboxes.each(function (checkbox) {
									checkbox.set('checked', 0);
								})
								setTimeout(function () {
									window.location.reload()
								}, '5000');
							}.bind(this)
						}).send();
					}.bind(this)
				},

				{
					title:this.translate('COM_HOTSPOTS_CLOSE','close'),
					event:function () {
						this.geocodeModal.close();

					}.bind(this)
				}
			]

		});

		this.geocodeModal.open();
	}
});