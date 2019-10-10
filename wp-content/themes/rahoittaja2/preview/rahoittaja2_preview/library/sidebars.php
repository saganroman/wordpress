<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $theme_sidebars;
$theme_sidebars = array(
	'default' => array(
		'name' => __('Primary Widget Area', 'default'),
		'id' => 'primary-widget-area',
		'description' => __("This is the default sidebar. If no widgets are active, the default theme widgets will be displayed instead.", 'default')
	),
    'secondary' => array(
        'name' => __('Secondary Widget Area', 'default'),
        'id' => 'secondary-widget-area',
        'description' => __("This is the secondary sidebar. If no widgets are active, the default theme widgets will be displayed instead.", 'default')
    ),
    'footer1' => array(
        'name' => __('First Footer Widget Area', 'default'),
        'id' => 'footer1-widget-area',
        'description' => __("The first footer widget area. You can add a text widget for custom footer text.", 'default')
    ),
    'footer2' => array(
        'name' => __('Second Footer Widget Area', 'default'),
        'id' => 'footer2-widget-area',
        'description' => __("The second footer widget area.", 'default')
    ),
    'footer3' => array(
        'name' => __('Third Footer Widget Area', 'default'),
        'id' => 'footer3-widget-area',
        'description' => __("The third footer widget area.", 'default')
    ),
    'footer4' => array(
        'name' => __('Fourth Footer Widget Area', 'default'),
        'id' => 'footer4-widget-area',
        'description' => __("The fourth footer widget area.", 'default')
    )
);

global $theme_widget_args;

$theme_widget_args = array(
	'before_widget' => '<widget id="%1$s" name="%1$s" class="widget %2$s">',
    'after_title' => '</title>',
    'before_title' => '<title>',
	'after_widget' => '</widget>'
);

$sidebar_priority = 0;
foreach ($theme_sidebars as $sidebar) {
	register_sidebar(array_merge($sidebar, $theme_widget_args, array('priority' => ++$sidebar_priority)));
}

function theme_register_sidebar($layoutPosition, $layoutName){
    global $theme_widget_args, $theme_sidebars;
    $position_name = sanitize_title_with_dashes($layoutPosition);
    if ($position_name !== ''){
        $sidebar = array(
            'name' => $layoutName,
            'id' => $position_name . '-widget-area'
        );
        $theme_sidebars[$position_name] = $sidebar;
        register_sidebar(array_merge($sidebar, $theme_widget_args));
    }
}

global $_theme_current_sidebar_id;

function theme_sidebars_widgets_filter($sidebars_widgets) {
    global $_theme_current_sidebar_id;
    if (!empty($sidebars_widgets[$_theme_current_sidebar_id])) {
        $sidebars_widgets[$_theme_current_sidebar_id] = array_filter($sidebars_widgets[$_theme_current_sidebar_id], 'theme_is_displayed_widget');
    }
    return $sidebars_widgets;
}

function theme_get_dynamic_sidebar_data($name) {
	global $theme_widget_args, $theme_sidebars, $_theme_current_sidebar_id;
    $_theme_current_sidebar_id = $theme_sidebars[$name]['id'];

	ob_start();
    add_filter('sidebars_widgets', 'theme_sidebars_widgets_filter');
	$success = dynamic_sidebar($_theme_current_sidebar_id);
    remove_filter('sidebars_widgets', 'theme_sidebars_widgets_filter');
	$content = ob_get_clean();

	if (!$success) {
        return false;
    }

	$data = explode($theme_widget_args['after_widget'], $content);
	$widgets = array();
	for ($i = 0; $i < count($data); $i++) {
		$widget = $data[$i];
		if (theme_is_empty_html($widget)) {
            continue;
        }

		$id = null;
		$name = null;
		$class = null;
		$title = null;

		if (preg_match('/<widget(.*?)>/', $widget, $matches)) {
			if (preg_match('/id="(.*?)"/', $matches[1], $ids)) {
				$id = $ids[1];
			}
			if (preg_match('/name="(.*?)"/', $matches[1], $names)) {
				$name = $names[1];
			}
			if (preg_match('/class="(.*?)"/', $matches[1], $classes)) {
				$class = $classes[1];
			}
			$widget = preg_replace('/<widget[^>]+>/', '', $widget);

			if (preg_match('/<title>(.*)<\/title>/', $widget, $matches)) {
				$title = $matches[1];
				$widget = preg_replace('/<title>.*?<\/title>/', '', $widget);
			}
		}
		$widget = str_replace('<ul class="product-categories">', '<ul>', $widget);

		$widgets[] = array(
			'id' => $id,
			'name' => $name,
			'class' => $class,
			'title' => $title,
			'heading' => 'h3',
			'content' => $widget
		);
	}
	return $widgets;
}

function theme_print_widgets($widgets, $style, $classname = '') {
	if (!is_array($widgets) || count($widgets) < 1) {
        return false;
    }

	foreach ($widgets as $widget) {
		echo theme_get_widget_meta_option($widget['name'], 'theme_widget_styling');
		if ($widget['name']) {
			$widget_style = theme_get_widget_style($widget['name'], $style);
			theme_wrapper($widget_style, $widget, $classname);
		} else {
			echo $widget['content'];
		}
	}
	return true;
}

function theme_is_page($page_ids) {
    if (is_page($page_ids)) {
        return true;
    }
    if (function_exists('is_shop') && is_shop() && in_array((int)wc_get_page_id('shop'), $page_ids)) {
        return true;
    }
    return false;
}

function theme_is_displayed_widget($widget_id) {
    $show_on = theme_get_widget_meta_option($widget_id, 'theme_widget_show_on');

    $page_ids = explode(',', theme_get_widget_meta_option($widget_id, 'theme_widget_page_ids_list'));
    $page_ids = array_map('trim', $page_ids);
    $page_ids = array_filter($page_ids, 'is_numeric');
    $page_ids = array_map('intval', $page_ids);

    if ('all' != $show_on) {
        $selected = (theme_get_widget_meta_option($widget_id, 'theme_widget_front_page') && is_front_page()) ||
            (theme_get_widget_meta_option($widget_id, 'theme_widget_single_post') && is_single()) ||
            (theme_get_widget_meta_option($widget_id, 'theme_widget_single_page') && is_page()) ||
            (theme_get_widget_meta_option($widget_id, 'theme_widget_posts_page') && is_home()) ||
            (theme_get_widget_meta_option($widget_id, 'theme_widget_page_ids') && !empty($page_ids) && theme_is_page($page_ids));

        if ((!$selected && 'selected' == $show_on) || ($selected && 'none_selected' == $show_on)) {
            return false;
        }
    }
    return true;
}

function theme_print_sidebar($layoutPosition, $classname = '') {
    $name = sanitize_title_with_dashes($layoutPosition);
    $widgets = theme_get_dynamic_sidebar_data($name);
    if (is_array($widgets)) {
        if (count($widgets) > 0) {
            $style = theme_get_option('theme_sidebars_style_' . $name);
            theme_print_widgets($widgets, $style, $classname);
        }
    }
}

function theme_print_sidebar_content($content, $layout_position, $class, $attributes) {
    $is_preview = theme_is_preview();
    if (!$content && $is_preview) {
        echo '<!-- empty::begin -->';
    }
    if ($content || $is_preview) {
        printf('<div %s class="%s" data-position="%s">%s</div>', $attributes, $class, $layout_position, $content);
    }
    if (!$content && $is_preview) {
        echo '<!-- empty::end -->';
    }
}
?>