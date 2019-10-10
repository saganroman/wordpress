<?php
/*
Plugin Name: MotoPress Simple Contact Form Addon
Plugin URI: https://motopress.com/
Description: MotoPress Simple Contact Form provides a simple contact form on your wordpress website.
Version: 1.2.1
Author: MotoPress
Author URI: https://motopress.com/
Text Domain: mpce-cfa
License: GPL2 or later
*/



global $wp_version;
if (version_compare($wp_version, '3.9', '<') && isset($network_plugin)) {
    define('MPCE_CFA_PLUGIN_FILE', $network_plugin);
} else {
    define('MPCE_CFA_PLUGIN_FILE', __FILE__);
}
define('MPCE_CFA_PLUGIN_DIR', trailingslashit(plugin_dir_path(MPCE_CFA_PLUGIN_FILE)));

if( !function_exists('isContentEditor')){
    function isContentEditor() {
        return method_exists('MPCEShortcode', 'isContentEditor') && MPCEShortcode::isContentEditor();
    }
}

require_once MPCE_CFA_PLUGIN_DIR . 'inc/license.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/settings.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/loader.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/mpLibrary.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/shortcodes.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/simpleShortcodes.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/mail.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/settingsPage.php';
require_once MPCE_CFA_PLUGIN_DIR . 'inc/EDD_MPCE_CFA_Plugin_Updater.php';


function mpceCFALoadTextdomain() {
    load_plugin_textdomain('mpce-cfa', FALSE, MPCE_CFA_PLUGIN_NAME . '/lang/');
}
add_action('plugins_loaded', 'mpceCFALoadTextdomain');

function mpceCFAAdminInit() {
	new EDD_MPCE_CFA_Plugin_Updater(MPCE_CFA_EDD_STORE_URL, __FILE__, array(
		'version' => MPCE_CFA_VERSION,                       // current version number
		'license' => get_option('edd_mpce_cfa_license_key'), // license key (used get_option above to retrieve from DB)
		'item_id' => MPCE_CFA_EDD_ITEM_ID,                   // id of this plugin
		'author'  => MPCE_CFA_AUTHOR                         // author of this plugin
	));
}
add_action('admin_init', 'mpceCFAAdminInit');

function mpceCFALicenseInit($hookSuffix) {
    global $cfaLicense;
    add_filter('admin_mpce_license_tabs', 'mpceCFALicenseTab');
    add_action('admin_mpce_license_save-' . MPCE_CFA_PLUGIN_SHORT_NAME, array(&$cfaLicense, 'save'));
}
add_action('admin_mpce_license_init', 'mpceCFALicenseInit');

function mpceCFASettingsInit($hookSuffix) {
    add_filter('admin_mpce_settings_tabs', 'mpceCFASettingsTab');
    add_action('admin_mpce_settings_save-' . MPCE_CFA_PLUGIN_SHORT_NAME, 'mpceCFASettingsSave');
}
add_action('admin_mpce_settings_init', 'mpceCFASettingsInit');


function mpceCFAEnqueueScripts() {
    $settings = get_option('mpce-cfa-settings', array());

    wp_register_style('mpce-cfa-style', MPCE_CFA_PLUGIN_DIR_URL . 'assets/css/style.min.css', array(), MPCE_CFA_VERSION);

    wp_register_script( 'mpce-cfa-modernizr', MPCE_CFA_PLUGIN_DIR_URL . 'assets/js/minified/modernizr.min.js',array('jquery'), MPCE_CFA_VERSION, true);
    wp_register_script( 'mpce-cfa-polyfiller', MPCE_CFA_PLUGIN_DIR_URL . 'assets/js/minified/polyfiller.js',array('jquery', 'mpce-cfa-modernizr'), MPCE_CFA_VERSION, true);

    wp_register_script( 'mpce-cfa-script-ajax', MPCE_CFA_PLUGIN_DIR_URL . 'assets/js/engine.min.js',array('jquery'), MPCE_CFA_VERSION, true);
    wp_localize_script( 'mpce-cfa-script-ajax', 'MPCE_CFA_Ajax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),// URL to wp-admin/admin-ajax.php to process data
        'security' => wp_create_nonce( 'mpce-cfa-special-string' ),// Creates a random string to test against for security purposes
        'sitekey' => isset($settings['recaptch_site_key']) ? $settings['recaptch_site_key'] : '',
        'success' => isset($settings['mpce_cfa_mail_success']) ? $settings['mpce_cfa_mail_success'] : '', // messages
        'fail' => isset($settings['mpce_cfa_mail_fail']) ? $settings['mpce_cfa_mail_fail'] : '',
    ));

    if( isset($settings['recaptch_site_key']) && trim($settings['recaptch_site_key']) && trim($settings['recaptch_secret_key']) ) {
        $lang = '';
        if( isset($settings['recaptch_lang']) && $settings['recaptch_lang'] !== '' ){
            $lang = 'hl=' . trim($settings['recaptch_lang']) . '&';
        }
        wp_register_script('mpce-cfa-recaptcha', 'https://www.google.com/recaptcha/api.js?' . $lang . 'onload=mpce_cfa_onloadCallback&render=explicit', array(), null, true);
    }

    //enqueue scripts
    if(isContentEditor()){
        wp_enqueue_style('mpce-cfa-style');
        wp_enqueue_script( 'mpce-cfa-modernizr');
        
        wp_enqueue_script( 'mpce-cfa-script-ajax');
        wp_enqueue_script('mpce-cfa-recaptcha');
    }

}
add_action('wp_enqueue_scripts', 'mpceCFAEnqueueScripts');


/*function to add async and defer attributes*/
function mpce_cfa_script_loader_tag($tag){
    $settings = get_option('mpce-cfa-settings', array());

    $lang = '';
    if( isset($settings['recaptch_lang']) && $settings['recaptch_lang'] !== '' ){
        $lang = 'hl=' . trim($settings['recaptch_lang']) . '&';
    }

    $scripts = array('https://www.google.com/recaptcha/api.js?' . $lang . 'onload=mpce_cfa_onloadCallback&render=explicit',);

    foreach($scripts as $script){
        if(true == strpos($tag, $script ) )
            return str_replace( ' src', ' defer="defer" async="async" src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'mpce_cfa_script_loader_tag', 10 );

// Plugin Activation
function motopressCFAInstall($network_wide) {
    $autoLicenseKey = apply_filters('cfa_auto_license_key', false);
    if ($autoLicenseKey) {
        CFALicense::setAndActivateLicenseKey($autoLicenseKey);
    }
}
register_activation_hook(__FILE__, 'motopressCFAInstall');

// Plugin uninstall
function motopressCFAUnInstall() {
    delete_option('mpce-cfa-settings');
}
register_uninstall_hook( __FILE__, 'motopressCFAUnInstall' );