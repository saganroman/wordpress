<?php
function theme_get_archive_data(){
    $archive_file = theme_get_theme_archive();
    if (!is_readable($archive_file)) {
        die(1);
    }
    return json_encode(array(
        'ext' => 'zip',
        'content' => base64_encode(file_get_contents($archive_file))
    ));
}