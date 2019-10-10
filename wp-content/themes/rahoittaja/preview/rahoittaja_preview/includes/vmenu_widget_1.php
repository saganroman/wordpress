<?php

function theme_vmenu_widget_init() {
    register_widget('VMenuWidget');
}
add_action('widgets_init', 'theme_vmenu_widget_init');

function theme_vmenu_block($title = '', $content = '', $class = '', $id = '') {
    ob_start();
    ?>
    
    <div class="data-control-id-3501 bd-vmenu-1" data-responsive-menu="true" data-responsive-levels="expand on click">
        <?php theme_vmenu_block_1($title, $content, $class, $id); ?>
    </div>
    
    <?php
    return ob_get_clean();
}

function theme_vmenu_menu_1_23($content = '') {
    ob_start();
    ?><ul class="data-control-id-127689 bd-menu-23 nav nav-pills">
    <?php echo $content; ?>
</ul><?php
    return ob_get_clean();
}

function theme_vmenu_menu_item_start_1_23($class = '', $title = '', $attrs = '', $link_class='') {
    ob_start();
    ?><li class="data-control-id-127690 bd-menuitem-23 <?php echo $class; ?>">
    <a class="<?php echo $link_class; ?>" <?php echo $attrs; ?>>
        <span>
            <?php echo $title; ?>
        </span>
    </a><?php
    return ob_get_clean();
}

function theme_vmenu_menu_item_end_1_23() {
    ob_start();
?>
    </li>
    
<?php
    return ob_get_clean();
}

function theme_vmenu_submenu_start_1_24($class = '') {
    ob_start();
    ?><div class="bd-menu-24-popup">
    
    <ul class="data-control-id-127708 bd-menu-24 bd-no-margins nav  <?php echo $class; ?>"><?php
    return ob_get_clean();
}

function theme_vmenu_submenu_end_1_24() {
    ob_start();
?>
        </ul>
        
    </div>
<?php
    return ob_get_clean();
}

function theme_vmenu_submenu_item_start_1_24($class = '', $title = '', $attrs = '', $link_class = '') {
    ob_start();
    ?><li class="data-control-id-127709 bd-menuitem-24 <?php echo $class; ?>">
    <a class="<?php echo $link_class; ?>" <?php echo $attrs; ?>>
        <span>
            <?php echo $title; ?>
        </span>
    </a><?php
    return ob_get_clean();
}

function theme_vmenu_submenu_item_end_1_24() {
    ob_start();
?>
    </li>
<?php
    return ob_get_clean();
}

class VMenuWidget extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'vmenu', 'description' => __('Use this widget to add one of your custom menus as a widget.', 'default'));
        parent::__construct(false, __('Vertical Menu', 'default'), $widget_ops);
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
        echo $args['before_widget'];
        echo $args['before_title'] . $title . $args['after_title'];
        ?>
        <div class="data-control-id-3499 bd-blockcontent bd-tagstyles shape-only">
            <div class="data-control-id-127688 bd-verticalmenu-3">
                <div class="bd-container-inner">
                    <?php echo theme_get_menu(array(
                        'source' => $instance['source'],
                        'depth' => theme_get_option('theme_vmenu_depth'),
                        'menu' => wp_get_nav_menu_object($instance['nav_menu']),
                        'menu_function' => 'theme_vmenu_menu_1_23',
                        'menu_item_start_function' => 'theme_vmenu_menu_item_start_1_23',
                        'menu_item_end_function' => 'theme_vmenu_menu_item_end_1_23',
                        'submenu_start_function' => 'theme_vmenu_submenu_start_1_24',
                        'submenu_end_function' => 'theme_vmenu_submenu_end_1_24',
                        'submenu_item_start_function' => 'theme_vmenu_submenu_item_start_1_24',
                        'submenu_item_end_function' => 'theme_vmenu_submenu_item_end_1_24'
                    )); ?>
                </div>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function update($new_instance, $old_instance) {
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['source'] = $new_instance['source'];
        $instance['nav_menu'] = (int)$new_instance['nav_menu'];
        return $instance;
    }

    public function form($instance) {
        //Defaults
        $instance = wp_parse_args((array)$instance, array('title' => '', 'source' => 'Pages', 'nav_menu' => ''));
        $title = esc_attr($instance['title']);
        $source = $instance['source'];
        $nav_menu = $instance['nav_menu'];

        // Get menus
        $menus = get_terms('nav_menu', array('hide_empty' => false));
        $sources = array('Pages' => __('Pages', 'default'), 'Categories' => __('Categories', 'default'), 'Custom Menu' => __('Custom Menu', 'default'));
        if (theme_woocommerce_enabled())
            $sources['Products Categories'] = __('Products Categories', 'default');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'default') ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>"/>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('source'); ?>"><?php echo __('Source', 'default') . ':'; ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('source'); ?>"
                    name="<?php echo $this->get_field_name('source'); ?>"
                    onchange="var s = jQuery('.p-<?php echo $this->get_field_id('nav_menu'); ?>'); if (this.value == 'Custom Menu') s.show(); else s.hide();">
                <?php
                foreach ($sources as $s => $t) {
                    $selected = ($source == $s ? ' selected="selected"' : '');
                    echo '<option' . $selected . ' value="' . $s . '">' . $t . '</option>';
                }
                ?>
            </select>
        </p>
        <p class="p-<?php echo $this->get_field_id('nav_menu'); ?>" <?php if ($source !== 'Custom Menu') echo ' style="display:none"' ?>>
            <?php
            // If no menus exists, direct the user to go and create some.
            if (!$menus) {
                printf(__('No menus have been created yet. <a href="%s">Create some</a>.', 'default'), admin_url('nav-menus.php'));
            } else {
                ?>
                <label
                    for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:', 'default'); ?></label>
                <br/>
                <select class="widefat" id="<?php echo $this->get_field_id('nav_menu'); ?>"
                        name="<?php echo $this->get_field_name('nav_menu'); ?>">
                    <?php
                    foreach ($menus as $menu) {
                        $selected = $nav_menu == $menu->term_id ? ' selected="selected"' : '';
                        echo '<option' . $selected . ' value="' . $menu->term_id . '">' . $menu->name . '</option>';
                    }
                    ?>
                </select>
            <?php
            }
            ?>
        </p>
    <?php
    }

}
?>