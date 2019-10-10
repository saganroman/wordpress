<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/content-importer.php');
require_once(dirname(__FILE__) . '/content-exporter.php');
require_once(dirname(__FILE__) . '/files-utility.php');

function themler_settings() {
    require_once THEMLER_PLUGIN_PATH . 'importer/settings.php';
}

function themler_import() {
    require_once THEMLER_PLUGIN_PATH . 'importer/import.php';
}

function themler_export() {
    require_once THEMLER_PLUGIN_PATH . 'importer/export.php';
}

function themler_add_importer_menu() {
    $menu_slug = THEMLER_PLUGIN_URL . 'importer/importer.php';
    $capability = 'edit_themes';

    add_menu_page(__('Themler tools', 'default'), __('Themler tools', 'default'), 'themler_settings', $menu_slug, 'themler_settings');

    add_submenu_page($menu_slug, __('Settings', 'default'), __('Settings', 'default'), $capability, 'themler_settings', 'themler_settings');
    add_submenu_page($menu_slug, __('Import', 'default'), __('Import', 'default'), $capability, 'themler_import', 'themler_import');
    add_submenu_page($menu_slug, __('Export', 'default'), __('Export', 'default'), $capability, 'themler_export', 'themler_export');
}
add_action('admin_menu', 'themler_add_importer_menu');

if (isset($_GET['themler-import'])) {
    add_action('load-themler-tools_page_themler_import', 'themler_import');
    add_action('load-themler-tools_page_themler_export', 'themler_export');
}

function themler_content_import_notice() {
    $import_href = admin_url() . 'admin.php?page=themler_import&tab=from-theme';
?>
    <div id="content-import-notice" class="updated">
        <p>
            <?php echo __('Do you want to import Content?', 'default'); ?>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <a style="text-decoration: none;" class="button" href="<?php echo $import_href; ?>"><?php echo __('Import content', 'default'); ?></a>
            <a style="text-decoration: none;" id="import-hide-notice" class="button" href="#"><?php echo __('Hide notice', 'default'); ?></a>
        </p>
    </div>
    <script>
        jQuery(document).ready(function ($) {
            $('#import-hide-notice').unbind("click").click(function() {
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'GET',
                    context: this,
                    data: ({
                        action: 'themler_hide_import_notice',
                        _ajax_nonce: '<?php echo wp_create_nonce('themler-importer'); ?>'
                    })
                }).always(function() {
                    $('#content-import-notice').remove();
                });
            });
        });
    </script>
<?php
}

function themler_add_import_notice() {
    if (!file_exists(get_template_directory() . '/content/content.json')) {
        return;
    }
    if (_at($_REQUEST, 'page') === 'themler_import') {
        return;
    }

    if (!get_option('themler_hide_import_notice')) {
        add_action('admin_notices', 'themler_content_import_notice');
    }
}
add_action('init', 'themler_add_import_notice');

function themler_remove_import_notice_option() {
    delete_option('themler_hide_import_notice');
}
add_action('after_switch_theme', 'themler_remove_import_notice_option');

function themler_add_import_settings() {
    $user = wp_get_current_user();
?>
    <script>
        var importerSettings = <?php echo json_encode(array(
            'actions' => array(
                'uploadZip' => add_query_arg(array('action' => 'themler_upload_chunk'), admin_url('admin-ajax.php')),
            ),
            'uid' => (int)$user->ID,
            'ajax_nonce' => wp_create_nonce('themler-importer'),
            'chunkSize' =>  min(wp_convert_hr_to_bytes(ini_get('post_max_size')),
                                wp_convert_hr_to_bytes(ini_get('upload_max_filesize')),
                                wp_convert_hr_to_bytes(ini_get('memory_limit'))
                                ),
        )); ?>;
    </script>
    <script type="text/javascript" src="<?php echo THEMLER_PLUGIN_URL . 'importer/assets/js/uploader.js?ver=' . THEMLER_PLUGIN_VERSION; ?>"></script>
<?php
}
add_action('admin_head', 'themler_add_import_settings');

function themler_hide_import_notice() {
    check_ajax_referer('themler-importer');
    update_option('themler_hide_import_notice', true);
}
add_action('wp_ajax_themler_hide_import_notice', 'themler_hide_import_notice', 9);

function themler_upload_chunk() {
    check_ajax_referer('themler-importer');

    try {
        $filename = _at($_REQUEST, 'filename', '');

        if ('' === $filename) {
            throw new Exception('Empty file name');
        }

        $is_last = _at($_REQUEST, 'last', '');
        $result = themler_upload_file_chunk($filename, $is_last);
        echo json_encode($result);
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    $uploads_info = wp_upload_dir();
    $tmp_dir = $uploads_info['basedir'] . '/themler-export';
    ThemlerFilesUtility::emptyDir($tmp_dir, true);

    die;
}
add_action('wp_ajax_themler_upload_chunk', 'themler_upload_chunk', 9);

function themler_upload_file_chunk($filename, $is_last) {
    if (!isset($_FILES['chunk']) || !file_exists($_FILES['chunk']['tmp_name'])) {
        throw new Exception('Empty chunk data');
    }

    $content_range = $_SERVER['HTTP_CONTENT_RANGE'];
    if ('' === $content_range && '' === $is_last) {
        throw new Exception('Empty Content-Range header');
    }

    $range_begin = 0;

    if ($content_range) {
        $content_range = str_replace('bytes ', '', $content_range);
        list($range, $total) = explode('/', $content_range);
        list($range_begin, $range_end) = explode('-', $range);
    }

    $uploads_info = wp_upload_dir();
    $tmp_dir = $uploads_info['basedir'] . '/themler-export';
    ThemlerFilesUtility::emptyDir($tmp_dir);
    ThemlerFilesUtility::createDir($tmp_dir);
    $tmp_path = $tmp_dir . '/' . basename($filename);

    $f = fopen($tmp_path, 'c');

    if (flock($f, LOCK_EX)) {
        fseek($f, (int) $range_begin);
        fwrite($f, file_get_contents($_FILES['chunk']['tmp_name']));

        flock($f, LOCK_UN);
        fclose($f);
    }

    if ($is_last) {
        if (_at($_REQUEST, 'fromTheme')) {
            $content_dir = get_template_directory() . '/content';
            themler_import_data($content_dir);
            update_option('themler_hide_import_notice', true);
        } else {
            ThemlerFilesUtility::extractZip($tmp_path, $tmp_dir);
            themler_import_data($tmp_dir);
            ThemlerFilesUtility::emptyDir($tmp_dir);
        }

        return array(
            'status' => 'done'
        );
    }

    return array(
        'status' => 'processed'
    );
}

function themler_import_data($path) {
    $import = new ThemlerContentImporter($path);
    $remove_prev = !!_at($_REQUEST, 'removePrev');
    $import->import($remove_prev);
}