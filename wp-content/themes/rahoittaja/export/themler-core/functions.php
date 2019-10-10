<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function is_themler_preview() {
    return isset($_GET['wp_customize']) && isset($_GET['theme']) || isset($_GET['preview']) && isset($_GET['template']);
}

function is_themler_action() {
    if (!isset($_REQUEST['action'])) {
        return false;
    }
    return strpos($_REQUEST['action'], 'theme_') === 0;
}

function themler_add_scripts_and_styles() {

    $bootstrap_ext = file_exists(THEMLER_PLUGIN_PATH . 'shortcodes/assets/css/bootstrap.min.css') ? '.min.css' : '.css';
    wp_register_style("themler-core-bootstrap", THEMLER_PLUGIN_URL . 'shortcodes/assets/css/bootstrap' . $bootstrap_ext, array(), THEMLER_PLUGIN_VERSION);
    wp_enqueue_style("themler-core-bootstrap");

    $style_ext = file_exists(THEMLER_PLUGIN_PATH . 'shortcodes/assets/css/style.min.css') ? '.min.css' : '.css';
    wp_register_style("themler-core-style", THEMLER_PLUGIN_URL . 'shortcodes/assets/css/style' . $style_ext, array(), THEMLER_PLUGIN_VERSION);
    wp_enqueue_style("themler-core-style");

    wp_register_style("themler-core-layout-ie", THEMLER_PLUGIN_URL . 'shortcodes/assets/css/layout.ie.css', array(), THEMLER_PLUGIN_VERSION);
    wp_enqueue_style("themler-core-layout-ie");
    if (function_exists('wp_style_add_data')) wp_style_add_data("themler-core-layout-ie", 'conditional', 'lte IE 9');

    wp_register_script("themler-core-jquery-fix", THEMLER_PLUGIN_URL . 'shortcodes/assets/js/jquery.js', array('jquery'), THEMLER_PLUGIN_VERSION);
    wp_enqueue_script("themler-core-jquery-fix");

    wp_register_script("themler-core-bootstrap", THEMLER_PLUGIN_URL . 'shortcodes/assets/js/bootstrap.min.js', array('jquery'), THEMLER_PLUGIN_VERSION);
    wp_enqueue_script("themler-core-bootstrap");

    wp_register_script("themler-core-script", THEMLER_PLUGIN_URL . 'shortcodes/assets/js/script.js', array('jquery'), THEMLER_PLUGIN_VERSION);
    wp_enqueue_script("themler-core-script");

    wp_register_script("themler-core-layout-core", THEMLER_PLUGIN_URL . 'shortcodes/assets/js/layout.core.js', array('jquery'), THEMLER_PLUGIN_VERSION);
    wp_enqueue_script("themler-core-layout-core");

    wp_register_script("themler-core-layout-ie", THEMLER_PLUGIN_URL . 'shortcodes/assets/js/layout.ie.js', array('jquery'), THEMLER_PLUGIN_VERSION);
    wp_enqueue_script("themler-core-layout-ie");
    if (function_exists('wp_script_add_data')) wp_script_add_data("themler-core-layout-ie", 'conditional', 'lte IE 9');
}
add_action('wp_enqueue_scripts', 'themler_add_scripts_and_styles');

function themler_edit_form_buttons($post) {
    ob_start();
    do_action('themler_edit_form_buttons', $post);
    $html = ob_get_clean();

    if ($html) {
?>
        <div style="margin-top: 5px; margin-bottom: 10px;">
            <?php echo $html; ?>
        </div>
<?php
    }
}
if (!has_action('edit_form_after_title', 'upage_edit_form_buttons')) {
    add_action('edit_form_after_title', 'themler_edit_form_buttons');
}

if (!function_exists('_at')) {
    /**
     * @param $array
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    function _at(&$array, $key, $default = false) {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }
}