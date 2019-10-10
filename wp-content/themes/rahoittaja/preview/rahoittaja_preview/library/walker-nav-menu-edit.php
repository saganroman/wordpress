<?php

class Theme_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $item_output = '';
        parent::start_el($item_output, $item, $depth, $args, $id);
        $output .= preg_replace(
            '/(?=<(p|fieldset)[^>]+class="[^"]*field-move)/',
            $this->get_fields($item, $depth, $args),
            $item_output
        );
    }

    public function get_fields($item, $depth, $args = array()) {
        ob_start();
        do_action('wp_nav_menu_item_custom_fields', $item->ID, $item, $depth, $args);
        return ob_get_clean();
    }
}