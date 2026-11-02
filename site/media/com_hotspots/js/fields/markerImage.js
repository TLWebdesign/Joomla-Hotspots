function initMarkerImage(options) {
    var select = options.select || '#marker-image'
    var imageContainer = options.imageContainer || '#sample-image'
    var fieldId = options.fieldId || '#jform_params_markerImage_id'
    var currentIcon = options.currentIcon || '#current-icon'

    function init() {
        jQuery(select).change(function() {
            editIconForm();
        });
    }

    function editIconForm() {
        var selected = jQuery(select).val();

        if (selected == 0) {
            _resetElements();
        }

        if (selected == 1) {
            jQuery(imageContainer).css('display','block');
            loadImages();
        }
    }

    function _resetElements() {
        jQuery(fieldId).value();
        jQuery(currentIcon).html();
        jQuery(imageContainer).css('display','none');
    }

    function loadImages() {
        jQuery(imageContainer + ' .lazy').lazy({
            appendScroll: jQuery(imageContainer)
        });

        jQuery(imageContainer).delegate("div", "click", function() {
            var image = jQuery(this).find("img").first()
            var imagePath = image.attr('src')
            var icon = jQuery(this).find("span").first().attr('data-id')

            addicon(icon, imagePath)
        })
    }

    function addicon(icon, path, title) {
        jQuery(fieldId).val('/sample/'+icon);

        var categoryIcon = jQuery(currentIcon +' img');
        if(categoryIcon.length) {
            categoryIcon.attr('src', path);
        } else {
            var catIconDiv = jQuery(currentIcon)
            catIconDiv.html('');
            var image = jQuery('<img>')
            image.attr('src', path)
            catIconDiv.append(image)
        }
    }

    init()
}
