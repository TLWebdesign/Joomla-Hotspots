/**
 * Hotspots image upload field (jQuery File Upload). Options come from Joomla.getOptions('com_hotspots.fileupload').
 */
(function () {
  'use strict';

  function toggleMaxNotice($widget, max) {
    var reached = $widget.fileupload('option').getNumberOfFiles() >= max;
    jQuery('.hotspots-max-number-files').toggleClass('hide d-none', !reached);
  }

  function init() {
    var options = Joomla.getOptions('com_hotspots.fileupload');
    var $ = jQuery;
    var $widget = $('#fileupload');

    if (!options || !$widget.length) {
      return;
    }

    var fake = document.getElementById('file-upload-fake');
    var real = document.getElementById('file-upload-real');

    if (fake && real) {
      fake.addEventListener('click', function () {
        real.click();
      });
    }

    $widget.fileupload({
      formData: {},
      autoUpload: true,
      maxFileSize: options.maxFileSize,
      maxNumberOfFiles: options.maxNumberOfFiles,
      acceptFileTypes: new RegExp('(\\.|/)(' + options.fileTypes.join('|') + ')$', 'i'),
      url: options.url,
      disableImageResize: false,
      imageMaxWidth: options.imageMaxWidth,
      imageMaxHeight: options.imageMaxHeight,
      finished: function () {
        toggleMaxNotice($(this), options.maxNumberOfFiles);
      },
      destroyed: function () {
        toggleMaxNotice($(this), options.maxNumberOfFiles);
      }
    }).on('fileuploadadd', function () {
      $('.fileupload-progress.hide').removeClass('hide d-none');
    });

    // Load the files the marker already has.
    $widget.addClass('fileupload-processing');
    $.ajax({
      url: options.urlWithExistingFiles,
      dataType: 'json',
      context: $widget[0]
    }).always(function () {
      $(this).removeClass('fileupload-processing');
    }).done(function (result) {
      $(this).fileupload('option', 'done').call(this, $.Event('done'), {result: result});
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
