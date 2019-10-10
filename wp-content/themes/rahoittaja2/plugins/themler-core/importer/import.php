<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$import_href = admin_url() . 'admin.php?page=themler_import';
$current_tab = _at($_REQUEST, 'tab', '');
?>

<div class="wrap">
    <h1><?php _e('Import', 'default'); ?></h1>
    <ul class="subsubsub" style="float:none;">
        <li><a href="<?php echo $import_href; ?>" class="<?php if (!$current_tab) echo 'current'; ?>">From zip</span></a> |</li>
        <li><a href="<?php echo $import_href; ?>&tab=from-theme" class="<?php if ($current_tab === 'from-theme') echo 'current'; ?>">From theme</span></a></li>
    </ul>
<?php

$upload_dir = wp_upload_dir();
if (!empty($upload_dir['error'])) {
?>
    <div class="error">
        <p><?php _e('Before you can upload your import file, you will need to fix the following error:', 'default'); ?></p>
        <p><strong><?php echo $upload_dir['error']; ?></strong></p>
    </div>
<?php
} else if ($current_tab === 'from-theme') {
    if (file_exists(get_template_directory() . '/content/content.json')) { ?>
        <p>
            <strong>There is content included to current theme.</strong><br>
            Would you like to install it?
        </p>
        <p>
            <label for="themler-remove-prev">Remove previously imported content</label>
            <input type="checkbox" id="themler-remove-prev" style="margin-left: 5px;" name="remove" value="0">
        </p>
        <p>
            <input type="submit" name="themler-install-from-theme" id="themler-install-from-theme"
                   class="button button-primary" value="Install content from theme">
            <br><br>
        </p>
    <?php } else { ?>
        <p>
            <strong>There is no content included to this theme.</strong><br>
        </p>
    <?php }
} else { ?>

    <p>
        <?php _e('Upload your (.zip) file and we&#8217;ll import the posts, pages and images into this site.', 'default'); ?>
    </p>
    <p>
        <?php _e('Choose a (.zip) file from your computer, then click Upload file and import.', 'default' ); ?>
    </p>

    <p>
        <input type="file" name="file" id="themler-file-field" />
    </p>
    <p>
        <label for="themler-remove-prev">Remove previously imported content</label>
        <input type="checkbox" id="themler-remove-prev" style="margin-left: 5px;" name="remove" value="0">
    </p>
    <p>
        <input type="submit" name="themler-upload" id="themler-upload" class="button button-primary" value="Upload file and import" disabled>
    </p>
<?php
}
?>
    <p id="themler-upload-progress" style="color: green; font-size: 14px;"></p>
    <style>
        #themler-upload-progress.upload-progress:before {
            background-image: url(<?php echo THEMLER_PLUGIN_URL; ?>importer/assets/images/preloader-01.gif);
            background-size: 15px 15px;
            display: inline-block;
            width: 15px;
            height: 15px;
            content:"";
            margin-right: 5px;
        }
    </style>
    <p id="themler-upload-error" class="disabled" style="color: red; font-size: 14px;"></p>
</div>