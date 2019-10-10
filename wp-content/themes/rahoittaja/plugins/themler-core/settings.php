<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $themler_options, $themler_default_options;
$themler_options = array(
    array(
        'name' => __('Import/Export', 'default'),
        'type' => 'heading',
    ),
    array(
        'id'   => 'themler_export_post_limit',
        'name' => __('Export posts limit', 'default'),
        'desc' => __('Used when you Download theme with Themler.', 'default'),
        'type' => 'numeric',
    ),
    array(
        'id'   => 'themler_export_page_limit',
        'name' => __('Export pages limit', 'default'),
        'desc' => __('Used when you Download theme with Themler.', 'default'),
        'type' => 'numeric',
    ),
);

$themler_default_options = array(
    'themler_export_post_limit' => 50,
    'themler_export_page_limit' => 50,
);


function themler_get_option($name) {
    global $themler_default_options;
    $result = get_option($name);
    if ($result === false) {
        $result = _at($themler_default_options, $name);
    }
    return $result;
}