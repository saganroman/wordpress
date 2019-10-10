<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// $style = 'post' or 'block' or 'vmenu' or 'simple' or 'products_slider'
function theme_wrapper($style, $args, $classname = '') {
	$func_name = "theme_{$style}_wrapper";
	if (function_exists($func_name)) {
		call_user_func_array($func_name, array($args, $classname) );
	} else {
		theme_block_wrapper($args, $classname);
	}
}

function theme_simple_wrapper($args = '') {
	$args = wp_parse_args($args, array(
			'id'      => '',
			'class'   => '',
			'title'   => '',
			'heading' => 'div',
			'content' => '',
		)
	);
	extract($args);
	if (theme_is_empty_html($title) && theme_is_empty_html($content))
		return;
	if ($id) {
		$id = ' id="' . $id . '" ';
	}
	if ($class) {
		$class = ' ' . $class;
	}
	echo "<div class=\"bd-widget{$class}\"{$id}>";
	if (!theme_is_empty_html($title))
		echo '<' . $heading . ' class="bd-widget-title">' . $title . '</' . $heading . '>';
	echo '<div class="bd-widget-content">' . $content . '</div>';
	echo '</div>';
}

function theme_block_wrapper($args, $classname = '') {
    $args = wp_parse_args($args, array(
            'id' => '',
            'class' => '',
            'title' => '',
            'heading' => 'div',
            'content' => '',
        )
    );
    extract($args);
    if (theme_is_empty_html($title) && theme_is_empty_html($content))
        return;
    if (function_exists('theme_block_' . $classname)){
        echo call_user_func_array('theme_block_' . $classname, array($title, $content, $class, $id));
    } else {
        echo $content;
    }
}

function theme_vmenu_wrapper($args) {
    $args = wp_parse_args($args, array(
            'id' => '',
            'class' => '',
            'title' => '',
            'heading' => 'div',
            'content' => '',
        )
    );
    extract($args);
    if (theme_is_empty_html($title) && theme_is_empty_html($content))
        return;
    $function_name = 'theme_vmenu_block';
    if (function_exists($function_name)) {
        echo call_user_func_array($function_name, array($title, $content, $class, $id));
    } else {
        echo $content;
    }
}

function theme_products_slider_wrapper($args) {
    $args = wp_parse_args($args, array(
            'id' => '',
            'class' => '',
            'title' => '',
            'heading' => 'div',
            'content' => '',
        )
    );
    extract($args);
    if (theme_is_empty_html($title) && theme_is_empty_html($content))
        return;
    $function_name = 'theme_products_slider_block';
    if (function_exists($function_name)) {
        echo call_user_func_array($function_name, array($title, $content, $class, $id));
    } else {
        echo $content;
    }
}