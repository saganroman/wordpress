<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (theme_can_view_preview() || theme_is_export_action()) {

    $base_template_dir = get_template_directory();
    load_template($base_template_dir . '/export/ProviderLog.php');

    @ini_set('display_errors', '1');
    @error_reporting(error_reporting() | E_ERROR | E_PARSE | E_COMPILE_ERROR);
    ProviderLog::registerErrorHandlers();

    function theme_add_logs() {
        $errors = ProviderLog::getErrors();
        if (!empty($errors)) {
            echo '<!-- PHP Errors: ' . str_replace('-->', '--', json_encode($errors, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0)) . ' -->';
        }
    }
    if (theme_is_preview()) {
        add_action('wp_footer', 'theme_add_logs');
    }
}