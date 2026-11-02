jQuery(document).ready(function() {
    jQuery('#new-spots span').on('click', function() {
        var span = jQuery(this)
        const event = new CustomEvent('onHotspotClick', {detail: {id: span.data('id')}});
        window.dispatchEvent(event)
    })
})
