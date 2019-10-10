<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_get_menu($args = '') {
	$args = wp_parse_args($args, array(
        'source' => 'Pages',
        'depth' => 0,
        'menu' => null,
        'theme_location' => '',
        'responsive' => '',
        'responsive_levels' => '',
        'levels' => '',
        'popup_width' => '',
        'popup_custom_width' => '',
        'columns' => array(),
        'menu_function' => '',
        'menu_item_start_function' => '',
        'menu_item_end_function' => '',
        'submenu_start_function' => '',
        'submenu_end_function' => '',
        'submenu_item_start_function' => '',
        'submenu_item_end_function' => '',
        'class' => '',
    ));
    $args = apply_filters('wp_nav_menu_args', $args);

	$source = &$args['source'];
	$menu = &$args['menu'];
	$class = &$args['class'];
    $theme_location = &$args['theme_location'];

    if ($theme_location != null && is_string($theme_location)) { // theme location

        // remove empty-array-filter for preview theme
        $filter_name = 'pre_option_theme_mods_' . get_option( 'stylesheet' );
        $empty_array_filter_exists = remove_filter($filter_name, '__return_empty_array');

        $locations = get_nav_menu_locations();
        $location = theme_get_array_value($locations, $theme_location);

        if (!$location && !empty($locations)) {
            $location = max(array_values($locations));
        }

        if ($location) {
            $menu = wp_get_nav_menu_object($location);
            if ($menu) {
                $source = 'Custom Menu';
                $class = implode(' ', array($class, 'menu-' . $menu->term_id));
            }
        }

        // restore empty-array-filter
        if ($empty_array_filter_exists) {
            add_filter($filter_name, '__return_empty_array');
        }
    }

	if ($source == 'Custom Menu' && $menu != null) {
		return theme_get_list_menu($args);
	}

	if ($source == 'Pages') {
		return theme_get_list_pages(array_merge(array('sort_column' => 'menu_order, post_title'), $args));
	}

	if ($source == 'Categories') {
		return theme_get_list_categories(array_merge(array('title_li' => false), $args));
	}

	if ($source == 'Products Categories') {
		return theme_get_list_categories(array_merge(array('title_li' => false, 'taxonomy' => 'product_cat'), $args));
	}
    return '';
}

function theme_get_top_menu($args = '') {
    $args = wp_parse_args($args, array(
        'menu' => null,
        'theme_location' => '',
        'menu_function' => '',
        'menu_item_start_function' => '',
        'menu_item_end_function' => '',
        'submenu_start_function' => '',
        'submenu_end_function' => '',
        'submenu_item_start_function' => '',
        'submenu_item_end_function' => '',
        'class' => '',
    ));
    $args = apply_filters('wp_nav_menu_args', $args);

    $source = 'Manual';
    $menu = &$args['menu'];
    $class = &$args['class'];
    $theme_location = &$args['theme_location'];

    if ($theme_location != null && is_string($theme_location)) { // theme location
        $filter_name = 'pre_option_theme_mods_' . get_option('stylesheet');
        $empty_array_filter_exists = remove_filter($filter_name, '__return_empty_array');

        $location = theme_get_array_value(get_nav_menu_locations(), $theme_location);
        if ($location) {
            $menu = wp_get_nav_menu_object($location);
            if ($menu) {
                $source = 'Custom Menu';
                $class = implode(' ', array($class, 'menu-' . $menu->term_id));
            }
        }

        // restore empty-array-filter
        if ($empty_array_filter_exists) {
            add_filter($filter_name, '__return_empty_array');
        }
    }

    if ($source == 'Custom Menu' && $menu != null) {
        return theme_get_list_menu($args);
    }

    if ($source == 'Manual') {
        return theme_get_manual_menu($args);
    }
    return '';
}

function theme_get_dropdown_menu($args = '') {
    $args = wp_parse_args($args, array(
        'menu' => null,
        'class' => '',
        'items'=> array(),
        'menu_function' => '',
        'menu_item_start_function' => '',
        'menu_item_end_function' => '',
        'submenu_start_function' => '',
        'submenu_end_function' => '',
        'submenu_item_start_function' => '',
        'submenu_item_end_function' => ''
    ));

    $items = array();
    foreach ($args['items'] as $item) {
        $id = $item['id'];
        $title = $item['title'];
        $href = $item['href'];
        $item_id = isset($item['item_id']) ? $item['item_id'] : '' ;
        $parent = $item['parent'];
        $no_menu_trim_title = isset($item['no_menu_trim_title']) && $item['no_menu_trim_title'];
        $items[] = new Theme_MenuItem(
            array(
                'id' => $id,
                'attr' => array(
                    'class' => '',
                    'href' => $href,
                    'item_id' => $item_id,
                    'no_menu_trim_title' => $no_menu_trim_title
                ),
                'title' => $title,
                'parent' => $parent,
                'menu_item_start_function' => $args['menu_item_start_function'],
                'menu_item_end_function' => $args['menu_item_end_function'],
                'submenu_item_start_function' => $args['submenu_item_start_function'],
                'submenu_item_end_function' => $args['submenu_item_end_function']
            )
        );
    }
    $walker = new Theme_MenuWalker();
    return $walker->walk($items, $args);

}

function theme_item_classes_filter($class) {
    return $class && $class !== "active" && $class !== "current-menu-item";
}

/**
 * Custom menu
 *
 * @param array $args
 * @return string
 */
function theme_get_list_menu($args = array()) {
    global $wp_query;
    $menu_items = wp_get_nav_menu_items($args['menu']->term_id);
    if (empty($menu_items))
        return '';

    $home_page_id = (int) get_option('page_for_posts');
    $queried_object_id = (int) $wp_query->queried_object_id;

    $active_candidates1 = array();
    $active_candidates2 = array();
    $active_candidates3 = array();

    foreach ((array) $menu_items as $key => $menu_item) {
        if (function_exists('is_woocommerce') && is_woocommerce() && is_shop()) {
            $shop_page = (int)wc_get_page_id('shop');
            if ($menu_item->object_id == $shop_page) {
                $active_candidates1[] = $menu_item->ID;
                continue;
            }
        }
        if ($menu_item->object_id == $queried_object_id &&
            (
                (!empty($home_page_id) && 'post_type' == $menu_item->type && $wp_query->is_home && $home_page_id == $menu_item->object_id ) ||
                ( 'post_type' == $menu_item->type && $wp_query->is_singular ) ||
                ( 'taxonomy' == $menu_item->type && ( $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax ))
            )
        ) {
            $active_candidates1[] = $menu_item->ID;
        } else if ('custom' == $menu_item->object && ThemeUrlsHelper::compareUrls(ThemeUrlsHelper::getCurrentUrl(), $menu_item->url)) {
            if ($menu_item->url === '#') {
                $active_candidates2[] = $menu_item->ID;
            } else if (key_exists('fragment', parse_url($menu_item->url))) {
                $active_candidates3[] = $menu_item->ID;
            } else if ($menu_item->url) {
                $active_candidates1[] = $menu_item->ID;
            }
        }
    }

    $sorted_menu_items = array();
    foreach ((array) $menu_items as $key => $menu_item) {
        $sorted_menu_items[$menu_item->menu_order] = wp_setup_nav_menu_item($menu_item);
    }
    $sorted_menu_items = apply_filters('wp_nav_menu_objects', $sorted_menu_items, $args);

    $id_to_item = array();
    $active_ids = array();

    foreach ($sorted_menu_items as $menu_item) {
        $id_to_item[$menu_item->ID] = $menu_item;

        $classes = empty($menu_item->classes) ? array() : (array) $menu_item->classes;
        $classes = explode(' ', implode(' ', $classes)); // $classes[i] may contain multiple classes
        if (in_array('active', $classes) || in_array('current-menu-item', $classes)) {
            $active_ids[] = $menu_item->ID;
        }
    }

    // if active item already selected
    // no need to change selection
    if (empty($active_ids)) {
        if (!empty($active_candidates1)) {
            $active_ids[] = $active_candidates1[0];
        } else if (!empty($active_candidates2)) {
            $active_ids[] = $active_candidates2[0];
        } else if (!empty($active_candidates3)) {
            $active_ids[] = $active_candidates3[0];
        }
    }

    foreach ($active_ids as $active_id) {
        $current_id = $active_id;
        while ($current_id && isset($id_to_item[$current_id])) {
            $current_item = $id_to_item[$current_id];
            $current_item->classes[] = 'active';
            $current_id = $current_item->menu_item_parent;
        }
    }

    $items = array();
    foreach ($sorted_menu_items as $menu_item) {
        $classes = empty($menu_item->classes) ? array() : (array) $menu_item->classes;
        $classes = explode(' ', implode(' ', $classes)); // $classes[i] may contain multiple classes
        $items[] = new Theme_MenuItem(array(
            'id' => $menu_item->db_id,
            'active' => in_array('active', $classes) || in_array('current-menu-item', $classes),
            'attr' => array(
                'target' => $menu_item->target,
                'rel' => $menu_item->xfn,
                'href' => $menu_item->url,
                'class' => join(' ', apply_filters('nav_menu_css_class', array_filter($classes, 'theme_item_classes_filter'), $menu_item))
            ),
            'title' => $menu_item->title,
            'parent' => $menu_item->menu_item_parent,
            'menu_item_start_function' => $args['menu_item_start_function'],
            'menu_item_end_function' => $args['menu_item_end_function'],
            'submenu_item_start_function' => $args['submenu_item_start_function'],
            'submenu_item_end_function' => $args['submenu_item_end_function'],
            'allow_megamenu' => isset($menu_item->theme_allow_megamenu) ? $menu_item->theme_allow_megamenu : '',
            'megamenu_width' => isset($menu_item->theme_megamenu_width) ? $menu_item->theme_megamenu_width : '',
            'megamenu_custom_width' => isset($menu_item->theme_megamenu_custom_width) ? $menu_item->theme_megamenu_custom_width : '',
            'megamenu_columns' => array(
                'lg' => isset($menu_item->theme_megamenu_columns_lg) ? $menu_item->theme_megamenu_columns_lg : '',
                'md' => isset($menu_item->theme_megamenu_columns_md) ? $menu_item->theme_megamenu_columns_md : '',
                'sm' => isset($menu_item->theme_megamenu_columns_sm) ? $menu_item->theme_megamenu_columns_sm : '',
                'xs' => isset($menu_item->theme_megamenu_columns_xs) ? $menu_item->theme_megamenu_columns_xs : '',
            ),
        ));
    }

    $walker = new Theme_MenuWalker();
    $items = $walker->walk($items, $args);
    $items = apply_filters('wp_nav_menu_items', $items, $args);
    return apply_filters('wp_nav_menu', $items, $args);
}


/**
 * Pages menu
 *
 * @param array $args
 * @return string
 */
function theme_get_list_pages($args = array()) {
    global $wp_query;
    $pages = get_pages($args);
    if (empty($pages))
        return '';

    $id_to_key = array();
    $current_id = null;

    foreach ($pages as $key => $page) {
        $id_to_key[$page->ID] = $key;
    }

    $front_id = null;
    $blog_id = null;

    if ('page' == get_option('show_on_front')) {

        $front_id = get_option('page_on_front');
        if ($front_id && isset($id_to_key[$front_id])) {
            $front_key = $id_to_key[$front_id];
            $front_page = $pages[$front_key];
            unset($pages[$front_key]);
            $front_page->post_parent = 0;
            $front_page->menu_order = 0;
            array_unshift($pages, $front_page);
            $id_to_key = array();
            foreach ($pages as $key => $page) {
                $id_to_key[$page->ID] = $key;
            }
        }

        if (is_home()) {
            $blog_id = get_option('page_for_posts');
            if ($blog_id && isset($id_to_key[$blog_id])) {
                $current_id = $blog_id;
            }
        }
    }

    if (function_exists('is_woocommerce') && is_woocommerce() && is_shop()) {
        $current_id = (int)wc_get_page_id('shop');
    }

    if ($wp_query->is_page) {
        $current_id = $wp_query->get_queried_object_id();
    }

    $active_id = $current_id;
    $active_ids = array();
    while ($active_id && isset($id_to_key[$active_id])) {
        $active = $pages[$id_to_key[$active_id]];
        if ($active && $active->post_status == 'private')
            break;
        $active_ids[] = $active->ID;
        $active_id = $active->post_parent;
    }

    $items = array();
    if (theme_get_option('theme_menu_showHome') && ('page' != get_option('show_on_front') || (!get_option('page_on_front') && !get_option('page_for_posts')))) {
        $title = theme_get_option('theme_menu_homeCaption');
        $active = is_home();
        $items[] = new Theme_MenuItem(array(
            'id' => 'home',
            'active' => $active,
            'attr' => array('class' => '', 'href' => get_home_url()),
            'title' => $title,
            'menu_item_start_function' => $args['menu_item_start_function'],
            'menu_item_end_function' => $args['menu_item_end_function'],
            'submenu_item_start_function' => $args['submenu_item_start_function'],
            'submenu_item_end_function' => $args['submenu_item_end_function']
        ));
    }
    foreach ($pages as $page) {
        $id = $page->ID;
        $title = $page->post_title;
        $active = in_array($id, $active_ids);
        $href = (($front_id && $front_id == $id) ? home_url() : get_page_link($id));
        $separator = theme_get_meta_option($id, 'theme_show_as_separator');
        if ($separator) {
            $href = '#';
        }
        $items[] = new Theme_MenuItem(array(
            'id' => $id,
            'active' => $active,
            'attr' => array('class' => '', 'href' => $href),
            'title' => $title,
            'parent' => $page->post_parent,
            'menu_item_start_function' => $args['menu_item_start_function'],
            'menu_item_end_function' => $args['menu_item_end_function'],
            'submenu_item_start_function' => $args['submenu_item_start_function'],
            'submenu_item_end_function' => $args['submenu_item_end_function']
        ));
    }
    $walker = new Theme_MenuWalker();
    return $walker->walk($items, $args);
}

/**
 * Categories menu
 *
 * @param array $args
 * @return string
 */
function theme_get_list_categories($args = array()) {
    global $wp_query, $post;
    $categories = get_categories($args);
    if (empty($categories)) {
        return '';
    }
    $taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : 'category';
    if ($taxonomy === 'product_cat' && !theme_woocommerce_enabled()) {
        return '';
    }

    $id_to_key = array();
    foreach ($categories as $key => $category) {
        $id_to_key[$category->term_id] = $key;
    }

    $current_id = null;
    if ($wp_query->is_category) {
        $current_id = $wp_query->get_queried_object_id();
    }

    if (function_exists('is_woocommerce') && is_woocommerce() && is_product_category()) {
        $product_cat_slug = get_query_var('product_cat');
        if ($product_cat_slug) {
            $product_cat = get_term_by('slug', $product_cat_slug, 'product_cat');
            $current_id = $product_cat->term_id;
        }
    }

    $active_ids = theme_get_category_branch($current_id, $categories, $id_to_key);
    if (theme_get_option('theme_menu_highlight_active_categories') && is_single()) {
        if ($taxonomy === 'category') {
            $cats = get_the_category($post->ID);
        } else {
            $cats = wp_get_post_terms($post->ID, $taxonomy);
        }
        foreach ($cats as $cat) {
            $active_ids = array_merge($active_ids, theme_get_category_branch($cat->term_id, $categories, $id_to_key));
        }
    }

    $items = array();
    foreach ($categories as $category) {
        $id = $category->term_id;
        $title = $category->name;
        $active = in_array($id, $active_ids);
        $items[] = new Theme_MenuItem(array(
            'id' => $id,
            'active' => $active,
            'attr' => array('class' => '', 'href' => get_term_link((int)$id, $taxonomy)),
            'title' => $title,
            'parent' => $category->parent,
            'menu_item_start_function' => $args['menu_item_start_function'],
            'menu_item_end_function' => $args['menu_item_end_function'],
            'submenu_item_start_function' => $args['submenu_item_start_function'],
            'submenu_item_end_function' => $args['submenu_item_end_function']
        ));
    }
    $walker = new Theme_MenuWalker();
    return $walker->walk($items, $args);
}

// Helper, return array( 'id', 'parent_id', ... , 'root_id' )
function theme_get_category_branch($id, $categories, $id_to_key) {
    $result = array();
    while ($id && isset($id_to_key[$id])) {
        $result[] = $id;
        $id = $categories[$id_to_key[$id]]->parent;
    }
    return $result;
}

/**
 * Manual menu (sample menu)
 * 2 items:
 * - 'Register' or 'Site Admin'
 * - 'Log in' or 'Log out'
 *
 * @param array $args
 * @return string
 */
function theme_get_manual_menu($args = array()) {
    $pages = array();

    $page = array();
    if (!is_user_logged_in()) {
        if (get_option('users_can_register')) {
            $page['href'] = site_url('wp-login.php?action=register', 'login');
            $page['post_title'] = __('Register', 'default');
            $page['id'] = 'register';
        }
    } else {
        $page['href'] = admin_url();
        $page['post_title'] = __('Site Admin', 'default');
        $page['id'] = 'site-admin';
    }
    if (isset($page['href'])) {
        $pages[] = $page;
    }

    $page = array();
    if (!is_user_logged_in()) {
        $page['href'] = esc_url(wp_login_url());
        $page['post_title'] = __('Log in', 'default');
        $page['id'] = 'login';
    } else {
        $page['href'] = esc_url(wp_logout_url());
        $page['post_title'] = __('Log out', 'default');
        $page['id'] = 'logout';
    }
    if (isset($page['href'])) {
        $pages[] = $page;
    }

    $items = array();
    foreach ($pages as $page) {
        $id = $page['id'];
        $title = $page['post_title'];
        $href = $page['href'];
        $items[] = new Theme_MenuItem(array(
            'id' => $id,
            'attr' => array('class' => '', 'href' => $href),
            'title' => $title,
            'parent' => 0,
            'menu_item_start_function' => $args['menu_item_start_function'],
            'menu_item_end_function' => $args['menu_item_end_function'],
            'submenu_item_start_function' => $args['submenu_item_start_function'],
            'submenu_item_end_function' => $args['submenu_item_end_function']
        ));
    }
    $walker = new Theme_MenuWalker();
    return $walker->walk($items, $args);
}

/* menu item */

class Theme_MenuItem {

	public $id;
	public $active;
	public $parent;
	public $attr;
	public $title;
    public $classes;
    public $object_id;//TODO: duplicate with $id

	function __construct($args = '') {
		$args = wp_parse_args($args, array(
            'id' => '',
            'active' => false,
            'parent' => 0,
            'attr' => array(),
            'title' => '',
            'menu_item_start_function' => '',
            'menu_item_end_function' => '',
            'submenu_item_start_function' => '',
            'submenu_item_end_function' => '',
            'allow_megamenu' => '',
            'megamenu_width' => '',
            'megamenu_custom_width' => '',
            'megamenu_columns' => array(),
        ));

		$this->id = $args['id'];
		$this->object_id = $args['id'];
		$this->active = $args['active'];
		$this->parent = $args['parent'];
		$this->attr = $args['attr'];
		$this->classes = array();
		$this->title = $args['title'];
        $this->menu_item_start_function = $args['menu_item_start_function'];
        $this->menu_item_end_function = $args['menu_item_end_function'];
        $this->submenu_item_start_function = $args['submenu_item_start_function'];
        $this->submenu_item_end_function = $args['submenu_item_end_function'];
        $this->allow_megamenu = $args['allow_megamenu'];
        $this->megamenu_width = $args['megamenu_width'];
        $this->megamenu_custom_width = $args['megamenu_custom_width'];
        $this->megamenu_columns = $args['megamenu_columns'];
	}

    function get_start($level, $item_type = '') {
        if ($level == 0) {
            $item_start_function = $this->menu_item_start_function;
        } else {
            $item_start_function = $this->submenu_item_start_function;
        }

        $link_class = implode(' ', $this->classes) . ' ' . ($this->active ? 'active' : '');
        $class = theme_get_array_value($this->attr, 'class', '');
        unset($this->attr['class']);
        $title = apply_filters('the_title', $this->title, $this->id);

        if (theme_get_option('theme_menu_use_tag_filter')) {
            $allowed_tags = explode(',', str_replace(' ', '', theme_get_option('theme_menu_allowed_tags')));
            $title = strip_tags($title, $allowed_tags ? '<' . implode('><', $allowed_tags) . '>' : '');
        }

        if (!theme_get_array_value($this->attr, 'no_menu_trim_title') && theme_get_option('theme_menu_trim_title')) {
            $title = theme_trim_long_str($title, theme_get_option($level == 0 ? 'theme_menu_trim_len' : 'theme_submenu_trim_len'));
        }

        $output = call_user_func_array($item_start_function, array(
            'class' => $class,
            'title' => $title,
            'attrs' => theme_prepare_attr($this->attr),
            'link_class' => $link_class,
            'item_type' => $item_type,
        ));

        return $output;
	}

	function get_end($level, $item_type = '') {
        if ($level == 0){
            $item_end_function = $this->menu_item_end_function;
        } else {
            $item_end_function = $this->submenu_item_end_function;
        }
        $output = call_user_func_array($item_end_function, array('item_type' => $item_type));
        return $output;
	}

}

/* menu walker */

class Theme_MenuWalker {

    public $child_ids = array();
    public $item_by_id = array();
    public $level;
    public $is_megamenu;
    public $items;
    public $depth;
    public $args;
    public $menu_function;
    public $menu_item_start_function;
    public $menu_item_end_function;
    public $submenu_start_function;
    public $submenu_end_function;
    public $submenu_item_start_function;
    public $submenu_item_end_function;

    public $responsive;
    public $responsive_levels;
    public $levels;
    public $popup_width;
    public $popup_custom_width;
    public $columns;

	function walk($items = array(), $args = '') {
		$args = wp_parse_args($args, array(
            'depth' => 0,
            'class' => '',
            'responsive' => '',
            'responsive_levels' => '',
            'levels' => '',
            'popup_width' => '',
            'popup_custom_width' => '',
            'columns' => array(),
        ));

		$this->items = &$items;
		$this->depth = (int) $args['depth'];
		$this->menu_function = $args['menu_function'];
		$this->menu_item_start_function = $args['menu_item_start_function'];
		$this->menu_item_end_function   = $args['menu_item_end_function'];
		$this->submenu_start_function = $args['submenu_start_function'];
		$this->submenu_end_function = $args['submenu_end_function'];
		$this->submenu_item_start_function = $args['submenu_item_start_function'];
		$this->submenu_item_end_function   = $args['submenu_item_end_function'];

        $this->responsive = $args['responsive'];
        $this->responsive_levels = $args['responsive_levels'];
        $this->levels = $args['levels'];
        $this->popup_width = $args['popup_width'];
        $this->popup_custom_width = $args['popup_custom_width'];
        $this->columns = $args['columns'];

        $this->level = 0;
        $this->is_megamenu = false;

		foreach ($items as $key => $item) {
			$this->item_by_id[$item->id] = $item;
			if (!isset($this->child_ids[$item->parent])) {
				$this->child_ids[$item->parent] = array();
			}
			$parent = $item->parent;
			if (!$parent)
				$parent = 0;
			$this->child_ids[$parent][] = $item->id;
		}

		$output = '';
		if (isset($this->child_ids[0])) {
            $top_items = $this->_getChilds(0);

            foreach($top_items as $item) {
                if ($this->levels !== 'megamenu') {
                    $item->allow_megamenu = '0';
                }
            }
			$this->display($output, 0);
		}
		$output = apply_filters('wp_list_pages', $output, $args);
		if (theme_is_empty_html($output))
			return '';
		$return = "\n";

        if (isset($this->menu_function) && strlen($this->menu_function) > 0){
            $return .= call_user_func_array($this->menu_function, array('content' => $output));
        }

        return $return;
	}

	function display(&$output, $id) {
        $childs = $this->_getChilds($id);
        $childs_count = count($childs);
        $columns_max_number = 12;
        $parent = isset($this->item_by_id[$id]) ? $this->item_by_id[$id] : null;

		foreach ($childs as $item) {

            $has_sub_items =
                ($this->responsive_levels != 'one level' || $this->levels != 'one level')
                && ($this->depth == 0 || $this->level < $this->depth - 1)
                && !empty($this->child_ids[$item->id]);

            // i.e. current item starts MegaMenu
            $show_megamenu = $this->level === 0 && $item->allow_megamenu !== '0' && count($this->_getChilds($item->id, 2)) > 0;
            $item_type = '';

            if ($has_sub_items) {
                $item->attr['class'] .= ' bd-submenu-icon-only';
            }

            if ($this->level === 0 && $show_megamenu) {
                $item->attr['data-mega-width'] = empty($item->megamenu_width) ? $this->popup_width : $item->megamenu_width;
                $item->attr['data-mega-width-value'] = $item->megamenu_width === 'custom' && $item->megamenu_custom_width ? $item->megamenu_custom_width : $this->popup_custom_width;
                $item_type = 'mega';
            }

            if ($this->level === 1 && $this->is_megamenu) {
                $item_type = 'category';
                $columns_class = '';
                $next_mode = '';
                foreach(array('lg', 'md', 'sm', 'xs') as $mode) {
                    if ($this->responsive === $mode) {
                        break;
                    }
                    $next_mode = $mode;

                    if ($parent && !empty($parent->megamenu_columns[$mode])) {
                        $columns_class .= " col-$mode-" . ($columns_max_number / $parent->megamenu_columns[$mode]);
                    } else if (!empty($this->columns[$mode])) {
                        $columns_class .= " col-$mode-" . ($columns_max_number / $this->columns[$mode]);
                    }
                }
                if (!$columns_class && $next_mode) {
                    for($i = $childs_count; $i <= $columns_max_number; $i++) {
                        if ($i && $columns_max_number % $i === 0) {
                            $columns_class .= " col-$next_mode-" . ($columns_max_number / $i);
                            break;
                        }
                    }
                }
                $item->attr['class'] .= $columns_class;
            }

            if ($this->level === 2 && $this->is_megamenu) {
                $item_type = 'subcategory';
            }

            $output .= $item->get_start($this->level, $item_type);

			if ($has_sub_items) {
                $this->level++;
                $tmp = $this->is_megamenu;
                $this->is_megamenu = $this->is_megamenu || $show_megamenu;

                if (!empty($this->submenu_start_function)) {
                    $output .= call_user_func_array($this->submenu_start_function, array('class' => $item->active ? ' active' : '', 'item_type' => $item_type));
                }

                $this->display($output, $item->id);

                if (!empty($this->submenu_end_function)) {
                    $output .= call_user_func_array($this->submenu_end_function, array('item_type' => $item_type));
                }

                $this->is_megamenu = $tmp;
                $this->level--;
			}
            $output .= $item->get_end($this->level, $item_type);
		}
	}

    /**
     * Returns menu items by specified depth from $id
     *
     * @param int $id - menu id
     * @param int $depth
     * @return array
     */
    private function _getChilds($id, $depth = 1) {
        if ($depth <= 0) {
            return array($this->item_by_id[$id]);
        }

        $result = array();
        if (!empty($this->child_ids[$id])) {
            foreach($this->child_ids[$id] as $child_id) {
                $result = array_merge($result, $this->_getChilds($child_id, $depth - 1));
            }
        }
        return $result;
    }
}


function theme_get_pages($pages) {
	if (is_admin())
		return $pages;

	$excluded_ids = array();
	foreach ($pages as $page) {
		if (!theme_get_meta_option($page->ID, 'theme_show_in_menu')) {
			$excluded_ids[] = $page->ID;
		}
	}
	$excluded_parent_ids = array();
	foreach ($pages as $page) {

		$title = theme_get_meta_option($page->ID, 'theme_title_in_menu');
		if ($title) {
			$page->post_title = $title;
		}

		if (in_array($page->ID, $excluded_ids)) {
			$excluded_parent_ids[$page->ID] = $page->post_parent;
		}
	}

	$length = count($pages);
	for ($i = 0; $i < $length; $i++) {
		$page = & $pages[$i];
		if (in_array($page->post_parent, $excluded_ids)) {
			$page->post_parent = theme_get_array_value($excluded_parent_ids, $page->post_parent, $page->post_parent);
		}
		if (in_array($page->ID, $excluded_ids)) {
			unset($pages[$i]);
		}
	}

	if (!is_array($pages))
		$pages = (array) $pages;
	$pages = array_values($pages);

	return $pages;
}

add_filter('get_pages', 'theme_get_pages');

function theme_add_megamenu_data($menu_item) {
    if (!$menu_item->menu_item_parent) {
        $menu_item->theme_allow_megamenu = theme_get_meta_option($menu_item->ID, 'theme_allow_megamenu');
        $menu_item->theme_megamenu_width = theme_get_meta_option($menu_item->ID, 'theme_megamenu_width');
        $menu_item->theme_megamenu_custom_width = theme_get_meta_option($menu_item->ID, 'theme_megamenu_custom_width');
        $menu_item->theme_megamenu_columns_lg = theme_get_meta_option($menu_item->ID, 'theme_megamenu_columns_lg');
        $menu_item->theme_megamenu_columns_md = theme_get_meta_option($menu_item->ID, 'theme_megamenu_columns_md');
        $menu_item->theme_megamenu_columns_sm = theme_get_meta_option($menu_item->ID, 'theme_megamenu_columns_sm');
        $menu_item->theme_megamenu_columns_xs = theme_get_meta_option($menu_item->ID, 'theme_megamenu_columns_xs');
    }
    return $menu_item;
}
add_filter('wp_setup_nav_menu_item', 'theme_add_megamenu_data');

function theme_edit_nav_menu_walker() {
    if (!class_exists('Theme_Walker_Nav_Menu_Edit')) {
        require_once(dirname(__FILE__) . '/walker-nav-menu-edit.php');
    }
    return 'Theme_Walker_Nav_Menu_Edit';
}
add_filter('wp_edit_nav_menu_walker', 'theme_edit_nav_menu_walker');

function theme_add_megamenu_fields($item_id, $item, $depth, $args) {
    if ($depth === 0) {
        $selected = isset($item->theme_allow_megamenu) ? $item->theme_allow_megamenu : '';
        $selected_width = isset($item->theme_megamenu_width) ? $item->theme_megamenu_width : '';
        $selected_custom_width = isset($item->theme_megamenu_custom_width) ? $item->theme_megamenu_custom_width : '';
        $selected_columns = array(
            'lg' => isset($item->theme_megamenu_columns_lg) ? $item->theme_megamenu_columns_lg : '',
            'md' => isset($item->theme_megamenu_columns_md) ? $item->theme_megamenu_columns_md : '',
            'sm' => isset($item->theme_megamenu_columns_sm) ? $item->theme_megamenu_columns_sm : '',
            'xs' => isset($item->theme_megamenu_columns_xs) ? $item->theme_megamenu_columns_xs : '',
        );
        $columns_number = array(
            '' => '', // configure in theme
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '6' => '6',
            '12' => '12',
        );
        $options = array(
            '' => __('Enable', 'default'),
            '0' => __('Disable', 'default'),
        );
        $width_options = array(
            '' => '', // configure in theme
            'sheet' => __('Sheet', 'default'),
            'custom' => __('Custom (px)', 'default'),
        );
?>
        <div class="theme-megamenu-options">
            <p class="description description-wide">
                <label for="edit-menu-theme-allow-megamenu-<?php echo $item_id; ?>">
                    <strong>Allow Mega Menu</strong> (Available for 3 levels menu)
                    <select name="menu-item-theme-allow-megamenu[<?php echo $item_id; ?>]">
                        <?php foreach ($options as $value => $str): ?>
                            <option<?php if($value . '' === $selected) echo ' selected'; ?> value="<?php echo $value; ?>"><?php echo $str; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>
            <p class="description description-wide theme-width-options">
                <label for="edit-menu-theme-megamenu-width-<?php echo $item_id; ?>">
                    <strong>Mega Menu width</strong>
                    <select name="menu-item-theme-megamenu-width[<?php echo $item_id; ?>]">
                        <?php foreach ($width_options as $value => $str): ?>
                            <option<?php if($value . '' === $selected_width) echo ' selected'; ?> value="<?php echo $value; ?>"><?php echo $str; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <input type="number" <?php if ($selected_custom_width) echo 'value="'.$selected_custom_width.'"'; ?> name="menu-item-theme-megamenu-custom-width[<?php echo $item_id; ?>]" style="vertical-align:middle;width:60px;height:28px;" />
                </label>
            </p>

            <table class="theme-columns-options">
                <tbody>
                    <tr>
                        <td></td>
                        <td>Desktops</td>
                        <td>Laptops</td>
                        <td>Tablets</td>
                        <td>Phones</td>
                    </tr>
                    <tr>
                        <td><strong>Columns</strong></td>
                        <?php foreach(array('lg', 'md', 'sm', 'xs') as $mode): ?>
                            <td>
                                <label for="menu-item-theme-megamenu-columns-<?php echo $mode; ?>-[<?php echo $item_id; ?>]">
                                    <select style="width:100%;" name="menu-item-theme-megamenu-columns-<?php echo $mode; ?>[<?php echo $item_id; ?>]">
                                        <?php foreach($columns_number as $col => $col_caption): ?>
                                            <option <?php if ($selected_columns[$mode] === (string)$col) echo 'selected'; ?> value="<?php echo $col; ?>"><?php echo $col_caption; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
<?php
    }
}
add_action('wp_nav_menu_item_custom_fields', 'theme_add_megamenu_fields', 10, 4);


function theme_print_megamenu_scripts() {
    global $pagenow;
    if ($pagenow !== 'nav-menus.php')
        return;
?>
    <script>
        jQuery(document).ready(function() {
            jQuery('.theme-megamenu-options').each(function() {
                var optionsArea = jQuery(this);
                var select = optionsArea.find('[name*="menu-item-theme-allow-megamenu"]');
                var widthSelect = optionsArea.find('[name*="menu-item-theme-megamenu-width"]');

                var onAllowChanged = function() {
                    if (this.value === '1' || this.value === '') {
                        optionsArea.find('.theme-width-options').show();
                        optionsArea.find('.theme-columns-options').show();
                    } else {
                        optionsArea.find('.theme-width-options').hide();
                        optionsArea.find('.theme-columns-options').hide();
                    }
                };
                var onWidthChanged = function() {
                    if (this.value === 'custom') {
                        optionsArea.find('[name*="menu-item-theme-megamenu-custom-width"]').show();
                    } else {
                        optionsArea.find('[name*="menu-item-theme-megamenu-custom-width"]').hide();
                    }
                };
                select.change(onAllowChanged);
                widthSelect.change(onWidthChanged);
                select.each(onAllowChanged);
                widthSelect.each(onWidthChanged);
            });
        });
    </script>
<?php
}
add_action('admin_head', 'theme_print_megamenu_scripts');

function theme_save_megamenu_fields($menu_id, $menu_item_db_id, $args) {
    $selected = isset($_REQUEST['menu-item-theme-allow-megamenu'][$menu_item_db_id]) ? $_REQUEST['menu-item-theme-allow-megamenu'][$menu_item_db_id] : '';
    $selected_width = isset($_REQUEST['menu-item-theme-megamenu-width'][$menu_item_db_id]) ? $_REQUEST['menu-item-theme-megamenu-width'][$menu_item_db_id] : '';
    $selected_custom_width = isset($_REQUEST['menu-item-theme-megamenu-custom-width'][$menu_item_db_id]) ? $_REQUEST['menu-item-theme-megamenu-custom-width'][$menu_item_db_id] : '';
    $selected_columns_lg = isset($_REQUEST['menu-item-theme-megamenu-columns-lg'][$menu_item_db_id]) ? $_REQUEST['menu-item-theme-megamenu-columns-lg'][$menu_item_db_id] : '';
    $selected_columns_md = isset($_REQUEST['menu-item-theme-megamenu-columns-md'][$menu_item_db_id]) ? $_REQUEST['menu-item-theme-megamenu-columns-md'][$menu_item_db_id] : '';
    $selected_columns_sm = isset($_REQUEST['menu-item-theme-megamenu-columns-sm'][$menu_item_db_id]) ? $_REQUEST['menu-item-theme-megamenu-columns-sm'][$menu_item_db_id] : '';
    $selected_columns_xs = isset($_REQUEST['menu-item-theme-megamenu-columns-xs'][$menu_item_db_id]) ? $_REQUEST['menu-item-theme-megamenu-columns-xs'][$menu_item_db_id] : '';

    theme_set_meta_option($menu_item_db_id, 'theme_allow_megamenu', $selected);
    theme_set_meta_option($menu_item_db_id, 'theme_megamenu_width', $selected_width);
    theme_set_meta_option($menu_item_db_id, 'theme_megamenu_custom_width', $selected_custom_width);
    theme_set_meta_option($menu_item_db_id, 'theme_megamenu_columns_lg', $selected_columns_lg);
    theme_set_meta_option($menu_item_db_id, 'theme_megamenu_columns_md', $selected_columns_md);
    theme_set_meta_option($menu_item_db_id, 'theme_megamenu_columns_sm', $selected_columns_sm);
    theme_set_meta_option($menu_item_db_id, 'theme_megamenu_columns_xs', $selected_columns_xs);
}
add_action('wp_update_nav_menu_item', 'theme_save_megamenu_fields', 10, 3);