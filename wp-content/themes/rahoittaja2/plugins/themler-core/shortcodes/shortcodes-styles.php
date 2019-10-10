<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ShortcodesStyles {

    public static function getStyleClassname($type, $style) {
        $data = apply_filters('theme_shortcodes_styles_' . strtolower($type) . '_' . $style, array('', ''));
        return $data[0];
    }

    public static function getMixinClassname($type, $style) {
        $data = apply_filters('theme_shortcodes_styles_' . strtolower($type) . '_' . $style, array('', ''));
        return $data[1];
    }
}