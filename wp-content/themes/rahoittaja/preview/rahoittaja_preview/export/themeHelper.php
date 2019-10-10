<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_get_theme_archive() {
    if (isset($_REQUEST['id'])) {
        $theme = wp_get_theme($_REQUEST['id']);
        if (!$theme->exists())
            throw new Exception('Error: Theme '.$_REQUEST['id'].' does not exists');
    } else {
        $theme = wp_get_theme();
    }

    $current_name = get_template();
    $name = $theme->get_template();

    $base_template_dir = $theme->get_template_directory();
    $preview_template_dir = $base_template_dir . '_preview';

    if (!file_exists($base_template_dir) || $current_name === $name && !file_exists($preview_template_dir)) {
        throw new Exception('Error: No Theme Folders');
    }

    $base_upload_dir = wp_upload_dir();
    if (false !== $base_upload_dir['error']) {
        throw new Exception('Upload folder error!');
    }

    $archive_name = 'theme_' . uniqid(time()) .  '.zip';
    $archive_file = $base_upload_dir['basedir'] . '/' . $archive_name;

    $new_name = isset($_REQUEST['themeName']) ? $_REQUEST['themeName'] : $name;
    $editable_version = !isset($_REQUEST['includeEditor']) || $_REQUEST['includeEditor'] === 'true';
    $include_content = isset($_REQUEST['includeContent']) && $_REQUEST['includeContent'] === 'true';

    if (!$new_name)
        throw new Exception('Error: theme name is empty');

    if ($current_name !== $name && $new_name !== $name)
        throw new Exception("Error: renaming not active theme does not supported ($current_name, $name, $new_name)");

    if ($current_name === $name) {
        theme_set_name($base_template_dir . '/style.css', $new_name);
        theme_set_name($preview_template_dir . '/style.css', theme_get_preview_theme_name($new_name));
        FilesHelper::empty_dir($base_template_dir . '/preview', true);
    }
    $preview_new_template = $new_name . '_preview';
    $archive = new PclZip($archive_file);

    // move old content to tmp folder
    $tmp_content_dir = $base_upload_dir['basedir'] . '/theme-content';
    FilesHelper::empty_dir($tmp_content_dir, true);
    FilesHelper::rename_if_exists($base_template_dir . '/content', $tmp_content_dir);

    if (0 == $archive->create($base_template_dir,
            PCLZIP_OPT_ADD_PATH,    $new_name,
            PCLZIP_OPT_REMOVE_PATH, $base_template_dir))
        throw new Exception("Error: " . $archive->errorInfo(true));

    if ($editable_version) {
        if ($current_name === $name && 0 == $archive->add($preview_template_dir,
                PCLZIP_OPT_ADD_PATH,    $new_name . '/preview/' . $preview_new_template,
                PCLZIP_OPT_REMOVE_PATH, $preview_template_dir))
            throw new Exception("Error: " . $archive->errorInfo(true));
    } else {
        if (($list = $archive->listContent()) == 0)
            throw new Exception("Error : " . $archive->errorInfo(true));

        $remove_list = array();
        foreach ($list as $i => $file) {
            if (
                strpos($file['filename'], "$new_name/export/") !== false
                || strpos($file['filename'], ".preview.") !== false
            )
                $remove_list[] = "$i";
        }

        if (!empty($remove_list) && 0 == $archive->delete(PCLZIP_OPT_BY_INDEX, implode(',', $remove_list)))
            throw new Exception("Error: cannot remove export dir");
    }

    if ($include_content) {
        $content_dir = theme_include_content();
        if (false !== $content_dir) {
            if (0 == $archive->add($content_dir,
                    PCLZIP_OPT_ADD_PATH,    $new_name . '/content/',
                    PCLZIP_OPT_REMOVE_PATH, $content_dir)) {
                throw new Exception("Content-zip error: " . $archive->errorInfo(true));
            }
        }
    }

    // restore content folder
    FilesHelper::rename_if_exists($tmp_content_dir, $base_template_dir . '/content');

    if ($current_name === $name) {
        theme_set_name($base_template_dir . '/style.css', $current_name);
        theme_set_name($preview_template_dir . '/style.css', theme_get_preview_theme_name($current_name));
    }
    return array(
        'path' => $archive_file,
        'name' => $new_name
    );
}

function theme_include_content() {
    if (!class_exists('ThemlerContentStorage')) {
        // plugin disabled
        return false;
    }

    $exporter = new ThemlerContentExporter();

    $content = $exporter->export(array(
        'posts' => array(
            'limit' => themler_get_option('themler_export_post_limit'),
        ),
        'pages' => array(
            'limit' => themler_get_option('themler_export_page_limit'),
        ),
    ));

    $content_storage = new ThemlerContentStorage();
    $content_storage->createFolder($content);
    $content_dir = $content_storage->getDataDirectory();
    return $content_dir;
}

function theme_prepare_theme_to_get() {
    if (isset($_REQUEST['id'])) {
        $theme = wp_get_theme($_REQUEST['id']);
        if (!$theme->exists())
            throw new Exception('Error: Theme '.$_REQUEST['id'].' does not exists');
    } else {
        $theme = wp_get_theme();
    }

    $include_content = isset($_REQUEST['includeContent']) && $_REQUEST['includeContent'] === 'true';

    $theme_dir = $theme->get_template_directory();
    $theme_content_dir = $theme_dir . '/content';

    FilesHelper::empty_dir($theme_content_dir, true);
    if ($include_content) {
        $content_dir = theme_include_content();
        if (false !== $content_dir) {
            FilesHelper::rename_if_exists($content_dir, $theme_content_dir);
        }
    }
    return array('result' => 'done');
}
theme_add_export_action('theme_prepare_theme_to_get');

function theme_rename_option($option_name, $new_option_name) {
    $value = get_option($option_name);
    if ($value !== false) {
        update_option($new_option_name, $value);
        delete_option($option_name);
    }
}