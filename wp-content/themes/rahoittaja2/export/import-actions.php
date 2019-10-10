<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_import_content() {
    $import = new ThemlerContentImporter(get_template_directory() . '/content');
    $remove_prev = !empty($_REQUEST['removePrev']);
    $import->import($remove_prev);
    return array('result' => 'done');
}
theme_add_export_action('theme_import_content');