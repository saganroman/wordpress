<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_customize_preview_js() {
    wp_enqueue_script('theme-customizer', get_template_directory_uri() . '/library/customizer/theme-customizer.js', array(), '', true);
    wp_enqueue_style('theme-customizer', get_template_directory_uri() . '/library/customizer/theme-customizer.css');
}

add_action('customize_preview_init', 'theme_customize_preview_js');