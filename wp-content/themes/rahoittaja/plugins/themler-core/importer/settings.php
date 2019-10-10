<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function themler_print_settings() {
    global $themler_options;
?>
    <div class="wrap">
        <div id="icon-themes" class="icon32"><br /></div>
        <h2><?php _e('Themler Settings', 'default'); ?></h2>
<?php
        if (isset($_REQUEST['Submit'])) {
            foreach ($themler_options as $value) {
                $id = _at($value, 'id');
                $val = stripslashes(_at($_REQUEST, $id, ''));
                $type = _at($value, 'type');
                switch ($type) {
                    case 'checkbox':
                        $val = $val ? 1 : 0;
                        break;
                    case 'numeric':
                        $val = (int)$val;
                        break;
                }
                update_option($id, $val);
            }
            echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.', 'default') . '</strong></p></div>' . "\n";
        }
        if (isset($_REQUEST['Reset'])) {
            foreach ($themler_options as $value) {
                delete_option(_at($value, 'id'));
            }
            echo '<div id="message" class="updated fade"><p><strong>' . __('Settings restored.', 'default') . '</strong></p></div>' . "\n";
        }
        echo '<form method="post" id="themler_options_form">' . "\n";
        $in_form_table = false;
        $dependent_fields = array();
        $op_by_id = array();
        $used_when = __('Used when <strong>"%s"</strong> is enabled', 'default');

        foreach ($themler_options as $op) {
            $id = _at($op, 'id');
            $type = _at($op, 'type');
            $name = _at($op, 'name');
            $desc = _at($op, 'desc');
            $script = _at($op, 'script');
            $depend = _at($op, 'depend');
            $show = _at($op, 'show', true);

            if (is_bool($show) && !$show || is_callable($show) && !call_user_func($show)) {
                continue;
            }

            $op_by_id[$id] = $op;
            if($depend) {
                $dependent_fields[] = array($depend, $id);
                $desc = (!$desc ? '' : $desc . '<br />') . sprintf($used_when, _at(_at($op_by_id, $depend), 'name', 'section'));
            }
            if ($type == 'heading') {
                if ($in_form_table) {
                    echo '</table>' . "\n";
                    $in_form_table = false;
                }
                echo '<h3>' . $name . '</h3>' . "\n";
                if ($desc) {
                    echo "\n" . '<p class="description">' . $desc .  '</p>' . "\n";
                }
            } else {
                if (!$in_form_table) {
                    echo '<table class="form-table">' . "\n";
                    $in_form_table = true;
                }
                echo '<tr valign="top">' . "\n";
                echo '<th scope="row">' . $name . '</th>' . "\n";
                echo '<td>' . "\n";
                $val = themler_get_option($id);
                themler_print_option_control($op, $val);
                if ($desc) {
                    echo '<span class="description">' . $desc . '</span>' . "\n";
                }
                if ($script) {
                    echo '<script>' . $script . '</script>' . "\n";
                }
                echo '</td>' . "\n";
                echo '</tr>' . "\n";
            }
        }
        if ($in_form_table) {
            echo '</table>' . "\n";
        }
        echo "<script>\r\n";
        for($i = 0; $i < count($dependent_fields); $i++) {
            echo "makeDependentField('{$dependent_fields[$i][0]}', '{$dependent_fields[$i][1]}');" . PHP_EOL;
        }
        echo "jQuery('#themler_options_form').bind('submit', function() {" . PHP_EOL .
            "    jQuery('input, textarea', this).each(function() {" . PHP_EOL .
            "        jQuery(this).removeAttr('disabled').removeClass('disabled');" . PHP_EOL .
            "    });" . PHP_EOL .
            "});" . PHP_EOL;
        echo "</script>" . PHP_EOL;
        ?>
        <p class="submit">
            <input name="Submit" type="submit" class="button-primary" value="<?php echo esc_attr(__('Save Changes', 'default')) ?>" />
            <input name="Reset" type="submit" class="button-secondary" value="<?php echo esc_attr(__('Reset to Default', 'default')) ?>" />
        </p>
        </form>
        <?php do_action('themler_options'); ?>
    </div>
    <?php
}

function themler_print_option_control($op, $val) {
    $id = _at($op, 'id');
    $type = _at($op, 'type');

    switch ($type) {
        case "numeric":
            echo '<input	name="' . $id . '" id="' . $id . '" type="text" value="' . absint($val) . '" class="small-text" />' . "\n";
            break;
        case "select":
            echo '<select name="' . $id . '" id="' . $id . '">' . "\n";
            foreach ($op['options'] as $key => $option) {
                $selected = ($val == $key ? ' selected="selected"' : '');
                echo '<option' . $selected . ' value="' . $key . '">' . esc_html($option) . '</option>' . "\n";
            }
            echo '</select>' . "\n";
            break;
        case "textarea":
            echo '<textarea name="' . $id . '" id="' . $id . '" rows="10" cols="50" class="large-text code">' . esc_html($val) . '</textarea><br />' . "\n";
            break;
        case "radio":
            foreach ($op['options'] as $key => $option) {
                $checked = ( $key == $val ? 'checked="checked"' : '');
                echo '<input type="radio" name="' . $id . '" id="' . $id . '" value="' . esc_attr($key) . '" ' . $checked . '/>' . esc_html($option) . '<br />' . "\n";
            }
            break;
        case "checkbox":
            $checked = ($val ? 'checked="checked" ' : '');
            echo '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="1" ' . $checked . '/>' . "\n";
            break;
        default:
            if ($type == 'text') {
                $class = 'regular-text';
            } else {
                $class = 'large-text';
            }
            echo '<input	name="' . $id . '" id="' . $id . '" type="text" value="' . esc_attr($val) . '" class="' . $class . '" />' . "\n";
            break;
    }
}


function themler_admin_styles() {
    ?>
    <style>
        #themler_options_form .form-table {
            margin-bottom: 30px;
        }
    </style>
    <?php
}
add_action('admin_head', 'themler_admin_styles');

function themler_dependent_field_scripts() {
    ?>
    <script>
        function makeDependentField(master, slave) {
            var $ = jQuery;
            master = $('#' + master);
            slave = $('#' + slave);
            master.bind('click', switchDependentField);
            switchDependentField.call(master);
            function switchDependentField() {
                if($(this).attr('checked')) {
                    slave.removeAttr('disabled').removeClass('disabled');
                } else {
                    slave.attr('disabled', 'disabled').addClass('disabled');
                }
            }
        }
    </script>
    <?php
}
add_action('admin_print_scripts-appearance_page_functions', 'themler_dependent_field_scripts');


themler_print_settings();