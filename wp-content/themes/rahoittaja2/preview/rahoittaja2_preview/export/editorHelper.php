<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$base_template_dir = get_template_directory();
load_template($base_template_dir . '/export/ProviderLog.php');

if(!function_exists('theme_woocommerce_enabled')) {
    function theme_woocommerce_enabled() {
        global $woocommerce;
        return $woocommerce != null;
    }
}

if(!function_exists('theme_fdm_enabled')) {
    function theme_fdm_enabled() {
        global $fdm_controller;
        return $fdm_controller != null;
    }
}

function theme_convert_protocol($url) {
    return is_ssl() ? preg_replace('/^http:/', 'https:', $url) : preg_replace('/^https:/', 'http:', $url);
}

function theme_get_domain() {
    if (!isset($_GET['domain']))
        return false;
    return theme_convert_protocol($_GET['domain']);
}

function theme_get_templates($use_preview = true) {
    global $theme_templates, $theme_template_types, $theme_templates_short_link;

    $theme_root = get_template_directory();
    $preview_theme_root = $theme_root . '_preview';

    $templates_path = $theme_root . '/export/templates.php';
    $preview_templates_path = $preview_theme_root . '/export/templates.php';

    if ($use_preview && file_exists($preview_templates_path)) {
        $templates_path = $preview_templates_path;
        $theme_root = $preview_theme_root;
    }
    require_once($theme_root . '/export/templatesHelper.php');
    require_once($templates_path);

    $files = FilesHelper::enumerate_files($theme_root, false);
    $used_files = array();
    foreach($files as $file) {
        $info = pathinfo($file['path']);
        if (theme_get_array_value($info, 'extension') === 'php') {
            $used_files[] = $info['filename'];
        }
    }

    return array(
        'urls' => $theme_templates,
        'types' => $theme_template_types,
        'used_files' => $used_files,
        'page_url' => $theme_templates_short_link
    );
}

function get_wp_versions() {
    global $wp_version, $woocommerce;
    $wc_version = '';
    if (theme_woocommerce_enabled())
        $wc_version = $woocommerce->version;

    return array('wordpress' => $wp_version, 'woocommerce' => $wc_version);
}

// deprecated
function getThemeTemplates(){
    $templates = array();

    $templates['product_url'] = '';
    $query_args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $query = new WP_Query( $query_args );
    if ( $query->have_posts() ) {
        while ($query->have_posts()) : $query->the_post();
            global $post;
            $templates['product_url'] = 'p=' . $post->ID . '&';
            break;
        endwhile;
    }

    $templates['blogpage_url'] = '';
    if ( 'page' == get_option('show_on_front') && get_option('page_for_posts') ){
        $templates['blogpage_url'] = 'page_id=' . get_option('page_for_posts') . '&';
    }

    $templates['singlepost_url'] = '';
    $all_posts = get_posts(array('order' => 'ASC'));
    if ( isset($all_posts[0])){
        $single_post = $all_posts[0];
        $templates['singlepost_url'] = 'p=' . $single_post->ID . '&';
    }

    $categories = get_categories(array(
        'child_of'   => 0,
        'orderby'    => 'id',
        'order'      => 'ASC',
        'number'     => 1
    ));
    if (empty($categories)) {
        $templates['category_id'] = '1';
    } else {
        $categories = array_values($categories);
        $templates['category_id'] = $categories[0]->cat_ID;
    }

    $pages = array();
    $pages[] = 'page' == get_option('show_on_front') ? (int) get_option( 'page_on_front' ) : 0; // front page
    $pages[] = (int) get_option( 'page_for_posts' ); // page for posts
    if (theme_woocommerce_enabled()){
        $cart_page  = (int) wc_get_page_id('cart');
        $templates['cart_url'] = 'p=' . $cart_page . '&';
        $pages[] = $cart_page;
        $pages[] = (int) wc_get_page_id('shop'); // shop page
    }

    $templates['page_url'] = '';
    $all_pages = get_pages( array( 'sort_column' => 'ID', 'exclude' => implode(', ', $pages) ) );
    if ( isset($all_pages[0])){
        $single_page = $all_pages[0];
        $templates['page_url'] = 'page_id=' . $single_page->ID . '&';
    }

    if (theme_fdm_enabled()) {
        // menu
        $res_url = '';
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
        foreach ((array) $menus as $key => $menu) {
            $items = wp_get_nav_menu_items($menu);
            foreach ((array) $items as $key2 => $item) {
                if ($item->object === 'fdm-menu' && strpos($item->url, '?') > 0) {
                    $res_url = substr ($item->url, strpos($item->url, '?') + 1) . '&';
                    break;
                }
            }
            if ($res_url !== '') {
                break;
            }
        }
        $templates['fdm_menu_url'] = $res_url;

        // menu item
        $menu_item_query_args = array(
            'post_type'      => 'fdm-menu-item',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $query = new WP_Query( $menu_item_query_args );
        if ( $query->have_posts() ) {
            while ($query->have_posts()) : $query->the_post();
                $menu_item_link = get_permalink();
                if (strpos($menu_item_link, '?') > 0){
                    $templates['fdm_menu_item_url'] = substr ($menu_item_link, strpos($menu_item_link, '?') + 1) . '&';
                }
                break;
            endwhile;
        }

    }

    return $templates;
}

function theme_set_hashes($current_theme_path, $data) {
    ProviderLog::start('theme_set_hashes');
    $hashesfile = $current_theme_path . '/export/hashes.json';
    ProviderLog::start('load');
    $hashes_content = file_exists($hashesfile) ? file_get_contents($hashesfile) : '';
    ProviderLog::end('load');
    if (false === $hashes_content)
        throw new PermissionDeniedException($hashesfile);
    ProviderLog::start('decode');
    $hashes = json_decode($hashes_content, true);
    ProviderLog::end('decode');
    ProviderLog::start('process');
    foreach ($data as $file => $content) {
        if ('[DELETED]' === $content) {
            if (isset($hashes[$file]))
                unset($hashes[$file]);
        } else {
            $hashes[$file] = $content;
        }
    }
    ProviderLog::end('process');
    ProviderLog::start('encode');
    $hashes_content = json_encode($hashes);
    ProviderLog::end('encode');
    ProviderLog::start('save');
    if (false === file_put_contents($hashesfile, $hashes_content))
        throw new PermissionDeniedException($hashesfile);
    ProviderLog::end('save');
    ProviderLog::end('theme_set_hashes');
}

function theme_set_cache($current_theme_path, &$data) {
    ProviderLog::start('theme_set_cache');
    $cachefile = $current_theme_path . '/export/cache.json';
    ProviderLog::start('load');
    $cache = file_exists($cachefile) ? file_get_contents($cachefile) : '';
    ProviderLog::end('load');
    if (false === $cache)
        throw new PermissionDeniedException($cachefile);
    ProviderLog::start('decode');
    $cache = json_decode($cache, true);
    ProviderLog::end('decode');
    ProviderLog::start('process');
    if (is_array($data)) {
        foreach ($data as $control => $storage) {
            if (!is_array($storage))
                continue;

            foreach ($storage as $file => $content) {
                if ('[DELETED]' === $content) {
                    if (isset($cache[$control]) && isset($cache[$control][$file]))
                        unset($cache[$control][$file]);
                } else {
                    $cache[$control][$file] = $content;
                }
            }
        }
    }
    ProviderLog::end('process');
    ProviderLog::start('encode');
    $cache = json_encode($cache);
    ProviderLog::end('encode');
    ProviderLog::start('save');
    if (false === file_put_contents($cachefile, $cache))
        throw new PermissionDeniedException($cachefile);
    ProviderLog::end('save');
    ProviderLog::end('theme_set_cache');
}

function get_theme_project($current_theme_path){
    ProviderLog::start('get_theme_project');
    $projectfile = $current_theme_path . '/export/project.json';
    $project = array();
    if (file_exists($projectfile)) {
        $content = file_get_contents($projectfile);
        if (false === $content)
            throw new PermissionDeniedException($projectfile);
        $project = json_decode($content, true);
    }
    if(is_array($project['project_data']['project'])) {
        $project['project_data']['project'] = json_encode($project['project_data']['project']);
    }
    ProviderLog::end('get_theme_project');
    return $project;
}

function setThemeProject($current_theme_path, $project){
    ProviderLog::start('setThemeProject');
    $projectfile = $current_theme_path . '/export/project.json';
    if (false === file_put_contents($projectfile, json_encode($project)))
        throw new PermissionDeniedException($projectfile);
    ProviderLog::end('setThemeProject');
}

function setThemeProjectData($current_theme_path, $project_data){
    $project = get_theme_project($current_theme_path);
    $project['project_data'] = $project_data;
    setThemeProject($current_theme_path, $project);
}

function theme_data_json_get($file_path) {
    if (!is_file($file_path) || !filesize($file_path)) {
        return array();
    }

    $json = '';
    $handle = fopen($file_path, 'r');
    if (flock($handle, LOCK_EX)) {
        $json = fread($handle, filesize($file_path));
        flock($handle, LOCK_UN);
    }
    fclose($handle);

    if (!$json) {
        return array();
    }
    $json = json_decode($json, true);
    if (!is_array($json)) {
        return array();
    }
    return $json;
}

function theme_data_json_put($file_path, $key, $value) {

    $handle = fopen($file_path, 'c+');
    if (flock($handle, LOCK_EX)) {
        $file_size = filesize($file_path);
        $json = $file_size ? fread($handle, $file_size) : '';
        if ($json) {
            $json = json_decode($json, true);
        }
        if (!$json) {
            $json = array();
        }
        ftruncate($handle, 0);
        fseek($handle, 0);

        $json[$key] = $value;
        fwrite($handle, json_encode($json));

        flock($handle, LOCK_UN);
    }
    fclose($handle);
}

function theme_get_uploaded_images_json_path() {
    return get_template_directory() . '/export/uploaded_images.json';
}

function theme_get_manifest_version($manifest) {
    if (!preg_match('#\#ver:(\d+)#i', $manifest, $matches))
        return false;

    return trim($matches[1]);
}

function theme_get_theme_version($themeName = null) {
    $theme = wp_get_theme($themeName);

    if (!file_exists($theme->get_template_directory() . '/export/manifest.version'))
        return false;
    $version = file_get_contents($theme->get_template_directory() . '/export/manifest.version');
    if (false === $version)
        return false;
    return trim($version);
}

function theme_get_manifests_dir() {
    $base_upload_dir = wp_upload_dir();
    if (false !== $base_upload_dir['error'])
        throw new Exception('Upload folder error!');

    return $base_upload_dir['basedir'] . '/manifests';
}

function theme_save_manifest($manifest) {
    $manifests_dir = theme_get_manifests_dir();
    $version = theme_get_manifest_version($manifest);
    if ($version === false)
        return;
    $manifest_path = "$manifests_dir/$version.manifest";
    $theme_manifest_path = get_template_directory() . "/export/$version.manifest";
    FilesHelper::create_dir($manifests_dir);
    if (false === file_put_contents($manifest_path, $manifest))
        throw new PermissionDeniedException($manifest_path);
    if (false === file_put_contents($theme_manifest_path, $manifest))
        throw new PermissionDeniedException($theme_manifest_path);
    if (false === file_put_contents(get_template_directory() . '/export/manifest.version', $version))
        throw new PermissionDeniedException('manifest.version');
}

function theme_load_manifest($version) {
    $base_upload_dir = wp_upload_dir();
    if (false !== $base_upload_dir['error'])
        throw new Exception('Upload folder error!');

    $manifests_dir = theme_get_manifests_dir();
    if (file_exists("$manifests_dir/$version.manifest")) {
        $manifest = file_get_contents("$manifests_dir/$version.manifest");
        return is_ssl() ? str_replace('http://', 'https://', $manifest) : str_replace('https://', 'http://', $manifest);
    }
    return false;
}

function theme_get_editor_link($template = null) {
    if (!$template)
        $template = get_template();
    $ver = theme_get_theme_version($template);
    return admin_url() . 'themes.php?page=theme_editor&theme=' . $template . ($ver === false ? '' : "&ver=$ver");
}

function getMaxRequestSize() {
    $postSize = toBytes(ini_get('post_max_size'));
    $uploadSize = toBytes(ini_get('upload_max_filesize'));
    $memorySize = toBytes(ini_get('memory_limit'));

    return min($postSize, $uploadSize, $memorySize);
}

function toBytes($str) {
    $str = strtolower(trim($str));

    if ($str) {
        switch ($str[strlen($str) - 1]) {
            case 'g':
                $str *= 1024;
            case 'm':
                $str *= 1024;
            case 'k':
                $str *= 1024;
        }
    }

    return intval($str);
}

function get_memory_limit() {
    if (!function_exists('ini_get'))
        return -1;
    return toBytes(ini_get('memory_limit'));
}

function theme_get_preview_changed_files()
{
    ProviderLog::start('theme_get_preview_changed_files');
    $base_template_dir = get_template_directory();
    $changed_files_file = $base_template_dir . '/export/preview_changed_files.json';
    $changed_files = null;
    if (is_file($changed_files_file)) {
        $changed_files_file_content = file_get_contents($changed_files_file);
        if ($changed_files_file_content) {
            $changed_files = json_decode($changed_files_file_content, true);
        }
    }
    ProviderLog::end('theme_get_preview_changed_files');
    return $changed_files;
}

function theme_get_preview_relative_path($path) {
    $preview_template_dir = FilesHelper::normalize_path(get_template_directory() . '_preview');
    $path = FilesHelper::normalize_path($path);
    if (substr($path, 0, strlen($preview_template_dir)) === $preview_template_dir) {
        $path = substr($path, strlen($preview_template_dir));
    }
    return $path;
}

function theme_set_preview_changed_files($changed_files) {
    ProviderLog::start('theme_set_preview_changed_files');
    $base_template_dir = get_template_directory();
    $changed_files_file = $base_template_dir . '/export/preview_changed_files.json';
    $_changed_files = $changed_files;
    $changed_files = array();
    foreach($_changed_files as $path) {
        $changed_files[] = theme_get_preview_relative_path($path);
    }
    $changed_files_file_content = json_encode(array_unique($changed_files));
    if (false === file_put_contents($changed_files_file, $changed_files_file_content))
        throw new PermissionDeniedException($changed_files_file);
    ProviderLog::end('theme_set_preview_changed_files');
}

function theme_is_valid_name($name) {
    return !validate_file($name) && preg_replace('|[^a-z0-9_]|i', '', $name) === $name;
}

function theme_get_permissions_check_folders() {
    $theme_root = get_theme_root(get_template());
    $base_upload_dir = wp_upload_dir();
    $upload_dir = $base_upload_dir['basedir'];
    return array($theme_root, $upload_dir);
}

function theme_out_of_memory_handler($cannot_allocate) {
    if ($cannot_allocate) {
        wp_die(<<<EOL
            <h3>PHP Memory Configuration Error</h3>

            <p>Themler requires at least 64Mb of PHP memory. Please increase your PHP memory to continue.
            For more information, please check this <a href="http://answers.themler.io/articles/5826/out-of-memory" target="_blank">link</a>.</p>
EOL
        );
    } else {
        $current_memory = get_memory_limit() / 1024 / 1024 . 'Mb';
        wp_die(<<<EOL
            <h3>PHP Memory Configuration Error</h3>

            <p>Themler requires at least 64Mb of PHP memory (you have "$current_memory"). Please increase your PHP memory to continue.
            For more information, please check this <a href="http://answers.themler.io/articles/5826/out-of-memory" target="_blank">link</a>.</p>
EOL
        );
    }
}

//http://stackoverflow.com/questions/2726524/can-you-unregister-a-shutdown-function
function theme_memory_limit_shutdown() {
    $error = error_get_last();
    if ($error && $error['type'] === E_ERROR && !isset($GLOBALS['memory_test_passed'])) {
        theme_out_of_memory_handler(true);
    }
}

function theme_test_memory_size() {
    // try to allocate 16Mb
    $alloc_bytes = 16 * 1024 * 1024;
    register_shutdown_function('theme_memory_limit_shutdown');

    $tmp = @str_repeat('.', $alloc_bytes);
    unset($tmp);
    $GLOBALS['memory_test_passed'] = true;

    return true;
}

function theme_check_memory_limit($test_alloc_memory = false) {
    $need_memory_size = 64 * 1024 * 1024;
    $memory = get_memory_limit();

    // can't retrieve memory limit option
    if (-1 == $memory)
        return;

    // try to increase limit
    if ($memory < $need_memory_size) {
        if(!function_exists('ini_set'))
            theme_out_of_memory_handler(false);

        $ret = ini_set('memory_limit', '64M');
        if (!$ret)
            theme_out_of_memory_handler(false);
        $memory = $need_memory_size;
    }
    // try to increase more
    if ($memory < $need_memory_size * 2 && function_exists('ini_set')) {
        @ini_set('memory_limit', '128M');
    }

    // check real limits
    if ($test_alloc_memory)
        theme_test_memory_size();
}

function theme_check_lockfile() {
    if (!isset($_REQUEST['action']) || !isset($_REQUEST['instanceId'])) {
        return;
    }
    if ($_REQUEST['action'] === 'theme_update_preview') {
        return;
    }

    $lock_file = get_template_directory() . '/export/themler.lock';
    if (file_exists($lock_file) && ($instanceId = file_get_contents($lock_file)) !== $_REQUEST['instanceId']) {
        die('[themler.lock]' . $instanceId . '/' . $_REQUEST['instanceId'] . '[themler.lock]');
    }
}

function theme_make_lockfile() {
    $time = round(microtime(true));
    FilesHelper::write(get_template_directory() . '/export/themler.lock', $time);
    return $time;
}

function theme_get_plugins_info() {
    $plugins = get_plugins();
    $result = array();
    foreach ($plugins as $path => $info) {
        if (is_plugin_active($path)) {
            $str = '';
            if (isset($info['Name']))
                $str .= $info['Name'];
            if (isset($info['Version']))
                $str .= ', v=' . $info['Version'];
            if (isset($info['PluginURI']))
                $str .= ', ' . $info['PluginURI'];
            $result[] = $str;
        }
    }
    return $result;
}

global $theme_editor_messages;
$theme_editor_messages = array(
    'preview_edit' => '<p><b>Unable to open theme.</b></p>'.
        '<p>This may be due to one of the following reasons:</p>'.
        '<ol>'.
        '<li>You are trying to edit the preview-theme.</li>'.
        '<li><i>/export/project.json</i> file is missing or corrupted.</li>'.
        '<li>You may need to <a href="http://answers.themler.io/articles/14063/how-to-clear-appcache">clear HTML 5 App Cache</a>.</li>'.
        '</ol>',

    'invalid_name' => '<p>The theme name may contain only alphanumeric symbols and underscore (A-z, 0-9, \'_\').</p><p>Please rename <b>\'%s\'</b> and <b>\'%s\'</b> to the fitting name. Also you need to ensure that your name and <i>\'Theme Name\'</i> in style.css are the same.',
    'php_compatibility' => '<p>Themler requires PHP version %s or higher. You are running version %s.</p>'.
        '<p>Please upgrade your PHP version.</p>',

    'convert_complete' => '<b>For completing conversion you need to open Themler once.</b>',
    'convert_complete_dlg' => '<p>For completing conversion you need to open Themler once.</p><p>Do you want to do this now?</p><div id="yes-btn" class="button">Yes</div><div id="no-btn" class="button">No</div>'
);
?>