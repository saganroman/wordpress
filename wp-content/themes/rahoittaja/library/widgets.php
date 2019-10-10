<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// widget extra options
global $theme_widgets_style;
$theme_widgets_style = array(
	'default' => __('sidebar default', 'default'),
	'block'   => __('block', 'default'),
	'simple'  => __('simple text', 'default')
);

function theme_get_widget_style($id, $style = null) {
    if (theme_is_vmenu_widget($id))
        return 'vmenu';
    if (theme_is_products_slider_widget($id))
        return 'products_slider';
    $result = 'default';
    if ($style != null) {
        if (!in_array($style, array('block', 'simple'))) {
            $style = 'block';
        }
        $result = $style;
    }
    return $result;
}

function theme_widget_expand_control($id) {
	global $wp_registered_widget_controls;
	$controls = &$wp_registered_widget_controls[$id];
	if (!is_array($controls['params'])) {
		$controls['params'] = array($controls['params']);
	}
	$controls['params'][] = $id;
	if (isset($controls['callback'])) {
		$controls['callback_redirect'] = $controls['callback'];
	}
	$controls['callback'] = 'theme_widget_extra_control';
}

function theme_update_widget_additional($instance) {
	global $theme_widget_meta_options;
	foreach ($theme_widget_meta_options as $value) {
		$id = theme_get_array_value($value, 'id');
		$val = stripslashes(theme_get_array_value($_POST, $id));
		$type = theme_get_array_value($value, 'type');
		$options = theme_get_array_value($value, 'options');
		switch ($type) {
			case 'checkbox':
				$val = ($val ? 1 : 0);
				break;
			case 'numeric':
				$val = (int) $val;
				break;
			case 'select':
				if (!in_array($val, array_keys($options))) {
					$val = reset(array_keys($options));
				}
				break;
		}
		$instance[$id] = $val;
	}
	return $instance;
}
function theme_widget_process_control() {
	global $wp_registered_widget_controls;
	if ('post' == strtolower($_SERVER['REQUEST_METHOD']) && isset($_POST['widget-id'])) {
		theme_widget_expand_control($_POST['widget-id']);
		return;
	}
	foreach ($wp_registered_widget_controls as $id => $widget) {
		theme_widget_expand_control($id);
	}
}

function theme_widget_extra_control() {
	global $wp_registered_widget_controls, $theme_widgets_style, $theme_widget_meta_options;
	$_theme_widget_meta_options = $theme_widget_meta_options;
	$params = func_get_args();
	$widget_id = $params[count($params) - 1];
	$widget_controls = theme_get_array_value($wp_registered_widget_controls, $widget_id, array());
	if (isset($widget_controls['callback_redirect'])) {
		$callback = $widget_controls['callback_redirect'];
		if (is_callable($callback)) {
			call_user_func_array($callback, $params);
		}
	}
	if (!preg_match('/^(.*[^-])-([0-9]+)$/', $widget_id, $matches) || !isset($matches[1]) || !isset($matches[2])) {
		return false;
	}
	$id = $matches[1] . '-' . $params[0]['number'];
	theme_print_meta_box($id, $_theme_widget_meta_options);
}