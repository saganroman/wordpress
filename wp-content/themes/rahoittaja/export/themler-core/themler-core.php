<?php
/*
Plugin Name: Themler core
Plugin URI: http://themler.io
Description: Provide shortcodes created with Themler
Version: 0.2.22
Author: Themler.io
Author URI: http://themler.io
*/
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$themler_plugin_data = get_file_data(__FILE__, array(
    'Version' => 'Version'
));

define('THEMLER_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('THEMLER_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('THEMLER_PLUGIN_VERSION', $themler_plugin_data['Version']);

require_once(dirname(__FILE__) . '/functions.php');
require_once(dirname(__FILE__) . '/settings.php');

if (!class_exists('ShortcodesUtility') && !is_themler_preview() && !is_themler_action()) {
    require_once(dirname(__FILE__) . '/shortcodes/shortcodes.php');
}
require_once(dirname(__FILE__) . '/importer/importer.php');