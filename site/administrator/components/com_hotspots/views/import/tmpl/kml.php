<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       12.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');

if (JVERSION > 3)
{
	JHtml::_('formbehavior.chosen', 'select');
}

$params = JComponentHelper::getParams('com_media');
$maxSize = (int) ($params->get('upload_maxsize', 0));

JHtml::stylesheet('media/com_hotspots/css/vendor/jquery.fileupload.css');
JHtml::stylesheet('media/lib_compojoom/css/jquery.fileupload.css');
JHtml::stylesheet('media/com_hotspots/css/vendor/jquery.fileupload-ui.css');

CompojoomHtmlBehavior::jquery();
JHtml::script('media/lib_compojoom/js/jquery.ui.custom.js');
JHtml::script('media/lib_compojoom/third/wizard/jquery.easyWizard.js');
JHtml::script('media/lib_compojoom/third/wizard/jquery.snippet.js');

JHtml::script('media/lib_compojoom/js/tmpl.min.js');
JHtml::script('media/lib_compojoom/js/load-image.all.min.js');
JHtml::script('media/lib_compojoom/js/jquery.iframe-transport.js');
JHtml::script('media/lib_compojoom/js/jquery.fileupload.js');
JHtml::script('media/lib_compojoom/js/jquery.fileupload-process.js');
JHtml::script('media/lib_compojoom/js/jquery.fileupload-image.js');
JHtml::script('media/lib_compojoom/js/jquery.fileupload-audio.js');
JHtml::script('media/lib_compojoom/js/jquery.fileupload-video.js');

JHtml::script('media/lib_compojoom/js/jquery.fileupload-validate.js');
JHtml::script('media/lib_compojoom/js/jquery.fileupload-ui.js');
if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}
echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'import', '', '');
?>
<style>
	.loading-indicator {
		display: none;
	}
	.loading-indicator.working {
		display: block;
		width: 32px;
		height: 32px;
	}
</style>
	<div class="box-info full animated fadeInDown">
		<div id="myWizard" class="easyWizardElement easyPager">

				<section class="step" data-step-title="Upload KML files">
					<form id="fileupload" action="/server/php/" method="POST" enctype="multipart/form-data">
						<!-- Redirect browsers with JavaScript disabled to the origin page -->
						<noscript><input type="hidden" name="redirect" value="https://blueimp.github.io/jQuery-File-Upload/"></noscript>
						<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
						<div class="row fileupload-buttonbar">
							<div class="col-lg-7">
								<!-- The fileinput-button span is used to style the file input field as button -->
				                <span class="btn btn-success fileinput-button">
				                    <i class="glyphicon glyphicon-plus"></i>
				                    <span>Add files...</span>
				                    <input type="file" name="files[]" multiple>
				                </span>
								<button type="submit" class="btn btn-primary start">
									<i class="glyphicon glyphicon-upload"></i>
									<span>Start upload</span>
								</button>
								<button type="reset" class="btn btn-warning cancel">
									<i class="glyphicon glyphicon-ban-circle"></i>
									<span>Cancel upload</span>
								</button>
								<button type="button" class="btn btn-danger delete">
									<i class="glyphicon glyphicon-trash"></i>
									<span>Delete</span>
								</button>
								<input type="checkbox" class="toggle">
								<!-- The global file processing state -->
								<span class="fileupload-process"></span>
							</div>
							<!-- The global progress state -->
							<div class="col-lg-5 fileupload-progress fade">
								<!-- The global progress bar -->
								<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
									<div class="progress-bar progress-bar-success" style="width:0%;"></div>
								</div>
								<!-- The extended global progress state -->
								<div class="progress-extended">&nbsp;</div>
							</div>
						</div>
						<!-- The table listing the files available for upload/download -->
						<table role="presentation" class="table table-striped">
							<tbody class="files"></tbody>
						</table>
					</form>
					<br>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Notes</h3>
						</div>
						<div class="panel-body">
							<ul>
								<li>The maximum file size for uploads is <strong><?php echo $maxSize; ?> MB</strong>.</li>
								<li>Only KML files are allowed
								</li>
								<li>You can <strong>drag &amp; drop</strong> files from your desktop on this webpage (see <a
										href="https://github.com/blueimp/jQuery-File-Upload/wiki/Browser-support">Browser support</a>).
								</li>
							</ul>
						</div>
					</div>
				</section>
				<section class="step" data-step-title="Select files">

						Please select KML files to import <div class="loading-indicator"><i class="fa fa-spinner fa-spin"></i></div>
						<table id="kml-select" role="presentation" class="table table-striped">
							<tbody class="files"></tbody>
						</table>
				</section>
				<section class="step" data-step-title="Create mapping">
					create mapping <div class="loading-indicator"><i class="fa fa-spinner fa-spin"></i></div>
					<form id="kml-mapping">
						<table role="presentation" class="table table-striped">
							<tbody class="mapping"></tbody>
						</table>
					</form>
				</section>
				<section class="step" data-step-title="Import">
					Import <div class="loading-indicator"><i class="fa fa-spinner fa-spin"></i></div>
					<div class="import"></div>
				</section>
			<div class="easyWizardButtons" style="clear: both;">
				<button class="prev btn btn-default" style="display: none;">« Back</button>
				<button class="next btn btn-default">Next »</button>
			</div>
		</div>

	</div>

	<!-- The template to display files available for upload -->
	<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}



	</script>
	<!-- The template to display files available for download -->
	<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
	</script>

	<script id="select-kml" type="text/x-tmpl">
		{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr>
    <td>
        <input name="kmls[]" type="checkbox" value="{%=file.name%}"/>
    </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
    </tr>
{% } %}

	</script>

	<script id="select-kml-mapping" type="text/x-tmpl">
		{% for (var i=0, mapping; mapping=o.mappings[i]; i++) { %}
    <tr>
    <td>
        <img src="{%=mapping.icon%}" /> {%=mapping.id%}

        <select name="styles[{%=mapping.id%}]">
            {% for (var y=0, cat; cat=o.cats[y]; y++) { %}
                <option value="{%=cat.id%}">{%=cat.title%}</option>
            {% } %}
        </select>
    </td>
        </tr>
{% } %}

	</script>

	<script id="imported" type="text/x-tmpl">
		Imported {%=o.imported%} locations
	</script>

	<script type="text/javascript">

		jQuery(document).ready(function () {
			var $ = jQuery;

			// Initialize the jQuery File Upload widget:
			$('#fileupload').fileupload({
				// Uncomment the following to send cross-domain cookies:
				//xhrFields: {withCredentials: true},
				url: 'index.php?option=com_hotspots&task=import.kml&<?php echo  JSession::getFormToken(); ?>=1'
			});

			// Enable iframe cross-domain access via redirect option:
			$('#fileupload').fileupload(
				'option',
				'redirect',
				window.location.href.replace(
					/\/[^\/]*$/,
					'/cors/result.html?%s'
				)
			);

			if (window.location.hostname === 'blueimp.github.io') {
				// Demo settings:
				$('#fileupload').fileupload('option', {
					url: '//jquery-file-upload.appspot.com/',
					// Enable image resizing, except for Android and Opera,
					// which actually support image resizing, but fail to
					// send Blob objects via XHR requests:
					disableImageResize: /Android(?!.*Chrome)|Opera/
						.test(window.navigator.userAgent),
					maxFileSize: 5000000,
					acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
				});
				// Upload server status check for browsers with CORS support:
				if ($.support.cors) {
					$.ajax({
						url: '//jquery-file-upload.appspot.com/',
						type: 'HEAD'
					}).fail(function () {
						$('<div class="alert alert-danger"/>')
							.text('Upload server currently unavailable - ' +
							new Date())
							.appendTo('#fileupload');
					});
				}
			} else {
				// Load existing files:
				$('#fileupload').addClass('fileupload-processing');
				$.ajax({
					// Uncomment the following to send cross-domain cookies:
					//xhrFields: {withCredentials: true},
					url: $('#fileupload').fileupload('option', 'url'),
					dataType: 'json',
					context: $('#fileupload')[0]
				}).always(function () {
					$(this).removeClass('fileupload-processing');
				}).done(function (result) {
					$(this).fileupload('option', 'done')
						.call(this, $.Event('done'), {result: result});
				});
			}


			// Init the form wizard
			jQuery('#myWizard').easyWizard({
				buttonsClass: 'btn btn-default',
				submitButton: false,
				after:  function(wizardObj, prevStepObj, currentStepObj) {
					$('.loading-indicator').addClass('working');
					if(currentStepObj.data('step') == 2) {

						$.ajax({
							// Uncomment the following to send cross-domain cookies:
							//xhrFields: {withCredentials: true},
							url: $('#fileupload').fileupload('option', 'url'),
							dataType: 'json',
							context: $('#fileupload')[0]
						}).always(function () {
							$('.loading-indicator').removeClass('working');
						}).done(function (result) {
							var template = tmpl('select-kml');
							console.log(template, result);
							var test = template(
								 result
							);

							$(currentStepObj.find('.files')).html(test).children();
						});
					}

					if(currentStepObj.data('step') == 3) {
						var checkboxes = prevStepObj.find('input[type=checkbox]:checked');

						$('.loading-indicator').addClass('working');
						$.ajax({
							type: 'POST',
							// Uncomment the following to send cross-domain cookies:
							//xhrFields: {withCredentials: true},
							url: 'index.php?option=com_hotspots&task=import.kmlmapping',
							dataType: 'json',
							data: checkboxes
						}).always(function() {
							$('.loading-indicator').removeClass('working');
						}).done(function (result) {
								var template = tmpl('select-kml-mapping');
								console.log(template, result);
								var test = template(
									result
								);

								$(currentStepObj.find('.mapping')).html(test).children();
							});


					}

					if(currentStepObj.data('step') == 4) {
						$('.loading-indicator').addClass('working');
						var files = $('#kml-select').find('input[type=checkbox]:checked').serialize();
						$.ajax({
							type: 'POST',
							// Uncomment the following to send cross-domain cookies:
							//xhrFields: {withCredentials: true},
							url: 'index.php?option=com_hotspots&task=import.kmlimport',
							dataType: 'json',
							data: prevStepObj.find('form').serialize() + '&'+ files
						}).always(function() {
							$('.loading-indicator').removeClass('working');
						}).done(function (result) {
							var template = tmpl('imported');
							var test = template(
								result
							);

							$(currentStepObj.find('.import')).html(test).children();
						});
					}
				}
			});
		});
	</script>
<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
