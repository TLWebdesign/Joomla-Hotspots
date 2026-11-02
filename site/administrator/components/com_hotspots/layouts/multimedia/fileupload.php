<?php

/**
 * @package     Hotspots
 * @copyright   (C) 2008-2015 compojoom.com, (C) 2026 TLWebdesign
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 * Image upload widget (jQuery File Upload) of the Multimedia form field.
 *
 * @var  array  $displayData  formControl, fieldName, url, existingUrl, maxNumberOfFiles, maxSize (MB),
 *                            fileTypes (string[]), imageSize ['x', 'y'], canDelete
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

$app = Factory::getApplication();
$wa  = $app->getDocument()->getWebAssetManager();
$js  = 'media/com_hotspots/vendor/fileupload/js/';

$wa->useScript('jquery')->useStyle('fontawesome');

// One chain of dependencies: jQuery UI widget, templates, image processing, the upload plugins and our init.
$scripts = [
    'jquery-ui'      => ['media/com_hotspots/vendor/jquery-ui/jquery.ui.custom.js', ['jquery']],
    'tmpl'           => [$js . 'tmpl.min.js', []],
    'canvas-to-blob' => [$js . 'canvas-to-blob.min.js', []],
    'load-image'     => [$js . 'load-image.all.min.js', []],
    'iframe'         => [$js . 'jquery.iframe-transport.js', ['com_hotspots.jquery-ui']],
    'fileupload'     => [$js . 'jquery.fileupload.js', ['com_hotspots.iframe']],
    'fu-process'     => [$js . 'jquery.fileupload-process.js', ['com_hotspots.fileupload']],
    'fu-image'       => [$js . 'jquery.fileupload-image.js', ['com_hotspots.fu-process', 'com_hotspots.load-image', 'com_hotspots.canvas-to-blob']],
    'fu-audio'       => [$js . 'jquery.fileupload-audio.js', ['com_hotspots.fu-process']],
    'fu-video'       => [$js . 'jquery.fileupload-video.js', ['com_hotspots.fu-process']],
    'fu-validate'    => [$js . 'jquery.fileupload-validate.js', ['com_hotspots.fu-process']],
    'fu-ui'          => [$js . 'jquery.fileupload-ui.js', ['com_hotspots.fu-image', 'com_hotspots.fu-audio', 'com_hotspots.fu-video', 'com_hotspots.fu-validate', 'com_hotspots.tmpl']],
    'fileupload.init' => ['com_hotspots/js/fields/fileupload.js', ['com_hotspots.fu-ui', 'core']],
];

foreach ($scripts as $name => [$uri, $deps]) {
    if (!$wa->assetExists('script', 'com_hotspots.' . $name)) {
        $wa->registerScript('com_hotspots.' . $name, $uri, [], ['defer' => true], $deps);
    }
}

foreach (['jquery.fileupload', 'jquery.fileupload-ui', 'fileupload'] as $css) {
    if (!$wa->assetExists('style', 'com_hotspots.css.' . $css)) {
        $wa->registerStyle('com_hotspots.css.' . $css, 'media/com_hotspots/vendor/fileupload/css/' . $css . '.css');
    }

    $wa->useStyle('com_hotspots.css.' . $css);
}

$wa->useScript('com_hotspots.fileupload.init');

$app->getDocument()->addScriptOptions('com_hotspots.fileupload', [
    'maxFileSize'          => (int) ((float) $displayData['maxSize'] * 1048576),
    'maxNumberOfFiles'     => (int) $displayData['maxNumberOfFiles'],
    'fileTypes'            => array_values($displayData['fileTypes']),
    'url'                  => $displayData['url'],
    'urlWithExistingFiles' => $displayData['existingUrl'],
    'imageMaxWidth'        => (int) $displayData['imageSize']['x'],
    'imageMaxHeight'       => (int) $displayData['imageSize']['y'],
]);

$name = htmlspecialchars($displayData['formControl'] . '[' . $displayData['fieldName'] . ']', ENT_QUOTES, 'UTF-8');
$meta = htmlspecialchars($displayData['formControl'] . '[' . $displayData['fieldName'] . '_data]', ENT_QUOTES, 'UTF-8');
?>
<div id="fileupload" class="hotspots-fileupload">
    <div class="fileupload-buttonbar">
        <input type="file" name="files[]" id="file-upload-real" class="d-none" multiple
               accept="<?php echo htmlspecialchars('.' . implode(',.', $displayData['fileTypes']), ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card compojoom-notes">
            <div class="card-body">
                <span class="fileupload-process"><span class="fa fa-spinner fa-pulse" aria-hidden="true"></span></span>
                <?php echo Text::sprintf('COM_HOTSPOTS_MULTIMEDIA_DRAG_DROP_OR', '<button id="file-upload-fake" type="button" class="btn btn-link p-0 align-baseline">', '</button>'); ?>
                <br>
                <small class="text-muted">
                    <?php echo Text::sprintf('COM_HOTSPOTS_MULTIMEDIA_MAX_FILE_SIZE', (float) $displayData['maxSize'] . ' MB'); ?>
                    <?php echo Text::sprintf('COM_HOTSPOTS_MULTIMEDIA_ALLOWED_FILE_TYPES', htmlspecialchars(implode(', ', $displayData['fileTypes']), ENT_QUOTES, 'UTF-8')); ?>
                </small>
                <div class="fileupload-progress fade hide d-none">
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar progress-bar-success bg-success" style="width:0%;"></div>
                    </div>
                    <div class="progress-extended">&nbsp;</div>
                </div>
                <div class="alert alert-warning hide d-none compojoom-max-number-files">
                    <?php echo Text::sprintf('COM_HOTSPOTS_MULTIMEDIA_MAX_FILES_REACHED', (int) $displayData['maxNumberOfFiles']); ?>
                </div>
                <table role="presentation" class="table table-striped">
                    <tbody class="files"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td><span class="preview"></span></td>
        <td>
            <span class="name"><i>{%=file.name%}</i></span>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="progress-bar progress-bar-success bg-success" style="width:0%;"></div>
            </div>
            <small><strong class="size"><?php echo Text::_('COM_HOTSPOTS_MULTIMEDIA_PROCESSING'); ?>...</strong></small>
        </td>
        <td>
            {% if (!i) { %}
                <button type="button" class="btn btn-secondary btn-sm cancel">
                    <span class="fa fa-stop" aria-hidden="true"></span> <span><?php echo Text::_('JCANCEL'); ?></span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>

<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
        {% if (file.thumbnailUrl) { %}
            <span class="preview">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.title%}" target="_blank" rel="noopener"><img src="{%=file.thumbnailUrl%}" alt=""></a>
                {% } else { %}
                    <img src="{%=file.thumbnailUrl%}" alt="">
                {% } %}
            </span>
        {% } %}
        </td>
        <td>
        {% if (!file.error) { %}
            <div class="row g-2">
                <div class="col-lg-4">
                    <input type="text" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_TITLE'); ?>"
                        name="<?php echo $meta; ?>[{%=file.name%}][title]" value="{%=file.title%}">
                </div>
                <div class="col-lg-8">
                    <input type="text" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_DESCRIPTION'); ?>"
                        name="<?php echo $meta; ?>[{%=file.name%}][description]" value="{%=file.description%}">
                </div>
            </div>
        {% } else { %}
            <div><span class="badge bg-danger"><?php echo Text::_('ERROR'); ?></span> {%=file.error%}</div>
        {% } %}
        </td>
        <td class="text-center">
            {% if (file.deleteUrl) { %}
                <?php if ($displayData['canDelete']) : ?>
                    <button type="button" class="btn btn-danger btn-sm delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}">
                        <span class="fa fa-trash" aria-hidden="true"></span> <span><?php echo Text::_('JACTION_DELETE'); ?></span>
                    </button>
                    <div><small class="size text-muted">{%=o.formatFileSize(file.size)%}</small></div>
                <?php endif; ?>
            {% } else { %}
                <button type="button" class="btn btn-secondary btn-sm cancel">
                    <span class="fa fa-stop" aria-hidden="true"></span> <span><?php echo Text::_('JCANCEL'); ?></span>
                </button>
            {% } %}
            {% if (!file.error) { %}
                <input type="hidden" name="<?php echo $name; ?>[]" value="{%=file.name%}">
            {% } %}
        </td>
    </tr>
{% } %}
</script>
