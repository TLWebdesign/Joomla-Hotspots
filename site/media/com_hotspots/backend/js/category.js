(function($) {
  $(document).ready(function() {
    var formControl = "jform_params_"
    var imageUploadInput = $('#'+formControl +'hs_marker_image');
    var iconDropdownSelection = $('#select-icon')
    resetElements()


    iconDropdownSelection.change(function() {
      resetElements();
      if (this.value == "new") {
       $('#' + formControl +'hs_marker_image').closest('div.control-group').css('display', 'block');
      }


      if(this.value === "sample") {
        $('#select-sample-image').css('display','block');

        $('.lazy').lazy();

        $('#select-sample-image').delegate("div", "click", function() {
          var image = $(this).find("img").first()
          var imagePath = image.attr('src')
          var iconName = $(this).find("span").first().attr('data-id')
          updateFormIconValue( '[sample]/sample/'+ iconName)
          updateIconPlaceholder(imagePath)
        })
      }
    })

    imageUploadInput.change(function() {
      updateFormIconValue('[upload]'+this.value)
    });

    function updateFormIconValue(icon) {
      $('#' + formControl + 'icon').attr("value", icon);
    }

    function updateIconPlaceholder(path) {
      var categoryIcon = $('#category-icon img');
      if(categoryIcon.length) {
        categoryIcon.attr('src', path);
      } else {
        var catIconDiv = $('#category-icon')
        catIconDiv.html('');
        var image = $('<img>')
        image.attr('src', path)
        catIconDiv.append(image)
      }
      $('#category-icon').removeClass('validation-failed');
    }


    function resetElements() {
      $('#select-sample-image').css('display','none');
      if($('#' + formControl + 'hs_marker_image').closest('div.control-group'))
      {
        $('#' + formControl + 'hs_marker_image').closest('div.control-group').css('display', 'none');
      }
    }
  })

})(jQuery)
