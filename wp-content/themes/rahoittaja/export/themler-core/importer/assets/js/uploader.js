/*global importerSettings */

jQuery(function() {
    'use strict';

    var fileInput = jQuery('#themler-file-field');
    var submitButton = jQuery("[name=themler-upload]");
    var installFromThemeButton = jQuery("[name=themler-install-from-theme]");
    var removePrevCheckbox = jQuery("#themler-remove-prev");
    var progressBar = jQuery('#themler-upload-progress');
    var errorBar = jQuery('#themler-upload-error');
    var uploadFile = null;

    function updateProgress(value) {
        if (value === 100) {
            progressBar.html('Import completed').removeClass('upload-progress');
        } else {
            progressBar.html(value + '%').addClass('upload-progress');
        }
    }

    fileInput.bind({
        change: function() {
            if (this.files[0]) {
                uploadFile = this.files[0];
                submitButton.removeAttr('disabled');
            } else {
                submitButton.attr('disabled', '');
                uploadFile = null;
            }
            progressBar.html('').removeClass('upload-progress');
            errorBar.html('');
        }
    });

    function submit(file, options) {

        submitButton.attr('disabled', '');
        installFromThemeButton.attr('disabled', '');
        updateProgress(0);
        errorBar.html('');

        var onProgress = function(percents) {
            updateProgress(percents);
        };
        var onComplete = function() {
            updateProgress(100);
            setTimeout(function() {
                fileInput.val('');
                submitButton.attr('disabled', '');
                installFromThemeButton.removeAttr('disabled');
                uploadFile = null;
            }, 200);
            jQuery('body').trigger('upload-complete');
        };
        var onError = function(xhr) {
            installFromThemeButton.removeAttr('disabled');
            errorBar.html('Error occured: (status ' + xhr.status + ')<br>' + xhr.responseText);
            console.error(JSON.stringify(xhr, null, '\t'));
            jQuery('body').trigger('upload-error');
        };

        var uploader = new ChunkedUploader(
            file,
            {
                progress: onProgress,
                complete: onComplete,
                error: onError
            },
            jQuery.extend(true, options || {}, {
                url: importerSettings.uid,
                _wpnonce: importerSettings.ajax_nonce,
                removePrev: removePrevCheckbox.is(':checked') ? '1' : ''
            })
        );
        uploader.upload();
    }

    installFromThemeButton.click(function() {
        submit(new Uint8Array(0), {
            fromTheme: true
        });
    });

    submitButton.click(function() {

        if (!uploadFile) {
            return;
        }
        submit(uploadFile);
    });
});

function ChunkedUploader(file, params, formParams) {
    'use strict';

    var _file = file;
    if (_file instanceof Uint8Array) {
        _file = new Blob([_file]);
    }
    var CHUNK_SIZE = parseInt((importerSettings.chunkSize || (1024 * 1024)) * 0.9);
    var uploadedChunkNumber = 0, allChunks;
    var fileName = (_file.name || 'content').replace(/[^A-Za-z0-9\._]/g, '');
    var fileSize = _file.size || _file.length;
    var total = Math.ceil(fileSize / CHUNK_SIZE);

    var rangeStart = 0;
    var rangeEnd = CHUNK_SIZE;
    validateRange();

    var sliceMethod;

    if ('mozSlice' in _file) {
        sliceMethod = 'mozSlice';
    } else if ('webkitSlice' in _file) {
        sliceMethod = 'webkitSlice';
    } else {
        sliceMethod = 'slice';
    }

    this.upload = upload;
    var requests;

    function upload() {
        var data;

        setTimeout(function () {
            requests = [];

            for (var chunk = 0; chunk < total - 1; chunk++) {
                data = _file[sliceMethod](rangeStart, rangeEnd);
                requests.push(createChunk(data, formParams));
                incrementRange();
            }

            allChunks = requests.length + 1;

            jQuery.when.apply(jQuery, requests).then(
                function success() {
                    var lastChunkData = _file[sliceMethod](rangeStart, rangeEnd);
                    createChunk(lastChunkData, jQuery.extend(true, {last: true}, formParams));
                },
                onUploadFailed
            );
        }, 0);
    }

    function createChunk(data, params) {
        var formData = new FormData();
        formData.append('filename', fileName);
        formData.append('chunk', new Blob([data], { type: 'application/octet-stream' }), 'blob');

        if (typeof params === 'object') {
            for (var i in params) {
                if (params.hasOwnProperty(i)) {
                    formData.append(i, params[i]);
                }
            }
        }

        var url = importerSettings.actions.uploadZip;

        return jQuery.ajax({
            url: url,
            data: formData,
            type: 'POST',
            mimeType: 'application/octet-stream',
            processData: false,
            contentType: false,
            headers: (rangeEnd <= fileSize) ? {
                'Content-Range': ('bytes ' + rangeStart + '-' + rangeEnd + '/' + fileSize)
            } : {},
            success: onChunkCompleted,
            error: onUploadFailed
        });
    }

    function validateRange() {
        if (rangeEnd > fileSize) {
            rangeEnd = fileSize;
        }
    }

    function incrementRange() {
        rangeStart = rangeEnd;
        rangeEnd = rangeStart + CHUNK_SIZE;
        validateRange();
    }

    function onUploadFailed(xhr) {
        if (xhr.statusText === 'abort') {
            return;
        }

        if (requests) {
            jQuery.each(requests, function () {
                this.abort();
            });
        }
        params.error(xhr);
    }

    function onChunkCompleted(responseText, status, xhr) {
        var response;
        try {
            response = JSON.parse(responseText);
            if (response.status === 'error') {
                onUploadFailed(xhr);
                return false;
            }
        } catch(e) {
            onUploadFailed(xhr);
            return false;
        }

        ++uploadedChunkNumber;
        if (uploadedChunkNumber === allChunks) {
            params.complete();
        } else {
            params.progress(Math.round((100 * uploadedChunkNumber) / allChunks));
        }
    }
}