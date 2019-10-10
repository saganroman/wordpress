<?php

header('Content-Type: text/css');

global $preview_dir, $preview_template, $base_template;

$preview_dir = dirname(__FILE__);
$preview_template = substr(str_replace(dirname($preview_dir), '', $preview_dir), 1);
$base_template = preg_replace('/(.*)(_preview$)/', '$1', $preview_template);
$style = file_get_contents('style.css');

function replace_image_callback($matches) {
    global $preview_dir, $base_template;
    $image = $matches[1];
    $preview_image_path = $preview_dir . '/images/' . $image;
    $image_path = (file_exists($preview_image_path) ? '' : '../' . $base_template . '/') . 'images/' . $image;
    return 'url(' . $image_path . ')';
}

$style = preg_replace_callback('/url\(images\/(.+)\)/U', 'replace_image_callback', $style);
echo $style;