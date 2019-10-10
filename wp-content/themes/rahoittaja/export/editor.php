<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

header("Content-type: text/html");
?>

<!DOCTYPE html>
<?php
    $base_template_dir = get_template_directory();
    load_template($base_template_dir . '/export/filesHelper.php');
    load_template($base_template_dir . '/export/editorHelper.php');

    global $theme_editor_messages;

    $base_template_name = get_template();
    $template_name = $base_template_name . '_preview';

    if (!theme_is_valid_name($base_template_name)) {
        wp_die(sprintf($theme_editor_messages['invalid_name'], $base_template_name, $template_name));
    }

    $check_folders = theme_get_permissions_check_folders();
    try {
        foreach($check_folders as $path)
            FilesHelper::test_permission($path);
    } catch(PermissionDeniedException $e) {
        wp_die(str_replace('{folders}', '<ol><li>' . implode('</li><li>', $check_folders) . '</li></ol>', $e->getExtendedMessage()));
    }

    theme_check_memory_limit(true);

$project = get_theme_project($base_template_dir);

    if (!isset($project['project_data'])) {
        wp_die($theme_editor_messages['preview_edit']);
    }

    $buildTime = round(microtime(true));
$ver = theme_get_theme_version();
if ($ver !== false) {
    $manifests_dir = theme_get_manifests_dir();
    if (!file_exists("$manifests_dir/$ver.manifest") && file_exists("$base_template_dir/export/$ver.manifest")) {
        FilesHelper::create_dir($manifests_dir);
        FilesHelper::copy_recursive("$base_template_dir/export/$ver.manifest", "$manifests_dir/$ver.manifest");
    }
}

$manifest_attr = $ver !== false
    ? ' manifest="' . add_query_arg(array('action' => 'theme_get_manifest', 'ver' => $ver), admin_url('admin-ajax.php', 'relative')) . '"'
    : '';

$project_params = array(
    'action' => 'theme_get_project',
    'version' => $buildTime
);

if (isset($_GET['domain'])) {
    $project_params['domain'] = urlencode($_GET['domain']);
}

?>
<html<?php echo $manifest_attr; ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script type="text/javascript" src="<?php echo add_query_arg($project_params, admin_url('admin-ajax.php')); ?>"></script>
    <script type="text/javascript" src="<?php echo get_bloginfo('template_url', 'display'); ?>/export/DataProvider.js?version=<?php echo $buildTime; ?>"></script>
    <script type="text/javascript" src="<?php echo get_bloginfo('template_url', 'display'); ?>/export/loader.js?version=<?php echo $buildTime; ?>"></script>
</head>
<body>
<div id="theme_editor"></div>
</body>
</html>