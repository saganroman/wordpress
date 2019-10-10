<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_is_converted() {
    return !file_exists(get_template_directory() . '/export/converter.data');
}

function theme_mark_as_converted() {
    FilesHelper::remove_file(get_template_directory() . '/export/converter.data');
}

function theme_content_exists() {
    return file_exists(TEMPLATEPATH . '/content/content.xml');
}

function theme_convert_lightbox() {
    function theme_add_convert_msg() {
        wp_enqueue_style( 'theme-customizer', get_template_directory_uri() . '/export/theme-customizer.css');
        global $theme_editor_messages;
?>
        <script>
            jQuery(function($){
                $('body').append('<div class="theme_customize_bg"><div class="theme_customize_message"></div></div>');
                $('.theme_customize_message').html('<?php echo $theme_editor_messages['convert_complete_dlg']; ?>');
                $('#no-btn').click(function() {
                    $('.theme_customize_bg').remove();
                });
                $('#yes-btn').click(function() {
                    window.location.href = '<?php echo theme_get_editor_link(); ?>';
                });
            });
        </script>
<?php
    }
    add_action('admin_notices', 'theme_add_convert_msg');
    wp_enqueue_style('theme-customizer', get_template_directory_uri() . '/export/theme-customizer.css');
}
if (!theme_is_converted()) {
    add_action('after_switch_theme', 'theme_convert_lightbox');
}

function theme_options_import_notice_action() {
?>
    <h3><?php echo __('Do you want to import Content?', 'default'); ?></h3>
    <p id="theme-options-content-import-notice">
        <button class="button start-import-without-cleanup"><?php echo __('Add Content', 'default'); ?></button>
        <button class="button start-import"><?php echo __('Replace imported Content', 'default'); ?></button>
    </p>
    <script>
        jQuery(document).ready(function ($) {
            $('#theme-options-content-import-notice button.button').unbind("click").click(function() {

                var command = $(this).clone().removeClass('button').attr('class').replace(/-/g,'_');
                this.disabled = true;
                $(this).prepend('<image style="height:50%;margin-right:5px" src="<?php echo get_bloginfo('template_url', 'display') . '/images/preloader01.gif'; ?>" />');
                var that = this;
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'GET',
                    context: this,
                    data: ({
                        action: 'theme_content_' + command,
                        _ajax_nonce: '<?php echo wp_create_nonce('theme_content_importer'); ?>'
                    }),
                    success: function(data) {
                        $("img", that).remove();
                        that.disabled = false;

                    },
                    error: function(data) {
                        $("img", that).remove();
                        that.disabled = false;
                    }
                });
            });
        });
    </script>
<?php
}

function theme_content_import_notice_action() {
?>
    <div id="content-import-notice" class="updated">
        <p>
            <?php echo __('Do you want to import Content?', 'default'); ?>
            &nbsp; &nbsp; &nbsp; &nbsp;
            <button class="button start-import-without-cleanup"><?php echo __('Add Content', 'default'); ?></button>
            <button class="button start-import"><?php echo __('Replace imported Content', 'default'); ?></button>
            <button class="button hide-notice"><?php echo __('Close', 'default'); ?></button>
        </p>
        <script>
            jQuery(document).ready(function ($) {
                $('#content-import-notice button.button').unbind("click").click(function() {
                    var command = $(this).clone().removeClass('button').attr('class').replace(/-/g,'_');
                    $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        type: 'GET',
                        context: this,
                        data: ({
                            action: 'theme_content_' + command,
                            _ajax_nonce: '<?php echo wp_create_nonce('theme_content_importer'); ?>'
                        }),
                        success: function(data) {
                            $("#content-import-notice").remove();
                        },
                        error: function(data) {
                            $("#content-import-notice").remove();
                        }
                    });
                    $('#content-import-notice button.button').last().after('<image src="<?php echo get_bloginfo('template_url', 'display') . '/images/preloader01.gif'; ?>" />').end().remove();
                });
            });
        </script>
    </div>
<?php
}

function theme_check_content_import_action() {
    if (has_action('admin_notices', 'theme_content_import_notice')) {
        remove_action('admin_notices', 'theme_content_import_notice');
        add_action('admin_notices', 'theme_content_import_notice_action');
    }
    if (has_action('theme_options', 'theme_options_import_notice')) {
        remove_action('theme_options', 'theme_options_import_notice');
        add_action('theme_options', 'theme_options_import_notice_action');
    }
}
add_action('init', 'theme_check_content_import_action', 200);

if (!theme_is_converted()) {
    remove_action('init', 'theme_check_content_import', 199);
    global $theme_editor_messages;
    theme_add_error($theme_editor_messages['convert_complete']);
}