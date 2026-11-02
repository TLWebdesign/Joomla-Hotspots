function hotspotsLink(options) {
    var linkToId =  options.linkToId || '#jform_params_link_to_id'
    var fieldId = options.fieldId || '#jform_params_link_to'

    function watch() {
        var selected = jQuery(fieldId).value;
        if (selected == 0) {
            jQuery(linkToId).parent('div').css('display', 'none');
        } else {
            jQuery(linkToId).parent('div').css('display','block');
        }
    }

    jQuery(fieldId).change(function() {
        watch()
    })
}
