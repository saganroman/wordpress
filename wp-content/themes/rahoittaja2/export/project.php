<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_get_project() {
    header('Content-Type: application/javascript');
    header("Pragma: no-cache");

    $base_template_dir = get_template_directory();
    $base_template_name = get_template() . '';
    $template_name = $base_template_name . '_preview';

    $project = get_theme_project($base_template_dir);
    $project_data = $project['project_data'];

    $user = wp_get_current_user();
    $uid = (int) $user->ID;
    $templates = theme_get_templates(false);
    $domain = theme_get_domain();
?>
    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';

    var templateInfo = <?php echo json_encode(array(
        'login_page' => wp_login_url(theme_get_editor_link() . (empty($domain) ? '' : '&domain=' . urlencode($domain))),
        'user' => $uid,
        'nonce' => wp_create_nonce('theme_template_export'),
        'importer_nonce' => (!theme_is_converted() && theme_content_exists() ? wp_create_nonce('theme_content_importer') : ''),
        'template_url' => esc_url( get_template_directory_uri() ) ,
        'admin_url' => admin_url(),
        'ajax_url' => admin_url('admin-ajax.php'),
        'pages_url' => admin_url('edit.php?post_type=page'),
        'home_url' =>  home_url(),
        'cms_version' => get_wp_versions(),
        'base_template_name' => $base_template_name,
        'template_name' => $template_name,
        'templates' => $templates['urls'],
        'page_url' => $templates['page_url'],
        'used_template_files' => $templates['used_files'],
        'template_types' => $templates['types'],
        'projectData' => $project_data,
        'woocommerce_enabled' => theme_woocommerce_enabled(),
        'maxRequestSize' => getMaxRequestSize(),
        'active_plugins' => theme_get_plugins_info(),
        'upage_editor' => (function_exists('upage_get_editor_link') ? upage_get_editor_link(array('page_id' => '', 'domain' => '')) : ''),
        'plugin_active' => CoreUpdateHelper::isPluginActive(),
        'ask_import_content' => !get_option('themler_hide_import_notice') && file_exists("$base_template_dir/content"),
    )); ?>;
    templateInfo.md5Hashes = <?php
        $hashes_path = get_template_directory() . '/export/hashes.json';
        if (file_exists($hashes_path)) {
            FilesHelper::readfile($hashes_path);
        } else {
            echo '{}';
        }
    ?>;

    templateInfo.cssJsSources = <?php
        $cache_file = get_template_directory() . '/export/cache.json';
        if (file_exists($cache_file)) {
            FilesHelper::readfile($cache_file);
        } else {
            echo '{}';
        }
    ?>;

<?php
    update_option('themler_hide_import_notice', true); // do not ask again
}
theme_add_export_action('theme_get_project');