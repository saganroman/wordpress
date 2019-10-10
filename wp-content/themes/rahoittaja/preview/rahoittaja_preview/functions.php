<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

header('X-UA-Compatible: IE=edge');

remove_action('wp_head', 'wp_generator');

// set site locale according to Language switcher
if (!is_admin() && isset($_COOKIE['language'])) {
    // since 4.7.0 use WP_Locale_Switcher
    global $wp_locale_switcher;
    if (isset($wp_locale_switcher)) {
        $wp_locale_switcher->switch_to_locale($_COOKIE['language']);
    }

    function theme_set_locale($lang) {
        return $_COOKIE['language'];
    }
    add_filter('locale', 'theme_set_locale');
}
load_theme_textdomain('default', get_template_directory() . '/languages');

if (function_exists('mb_internal_encoding')) {
	mb_internal_encoding(get_bloginfo('charset'));
}
if (function_exists('mb_regex_encoding')) {
	mb_regex_encoding(get_bloginfo('charset'));
}

function theme_is_customizer() {
    return isset($_GET['wp_customize']) && isset($_GET['theme']);
}

function theme_is_preview() {
    return isset($_GET['preview']) && isset($_GET['template'])
    || theme_is_customizer(); // since 4.3
}

function theme_can_view_preview() {
    return theme_is_preview() && current_user_can('switch_themes');
}

function theme_is_export_action() {
    if (!current_user_can('switch_themes') || !isset($_REQUEST['action'])) {
        return false;
    }
    return strpos($_REQUEST['action'], 'theme_') === 0;
}

/**
 * Restore $wp_query->is_preview = true
 *
 * @see condition in wp-includes\canonical.php redirect_canonical
 */
function theme_fix_is_preview() {
    if (theme_is_customizer()
        && ($p = get_query_var('p'))
        && 'publish' == get_post_status($p)
        && isset($_GET['nonce'])
        && wp_verify_nonce($_GET['nonce'], 'preview-customize_' . $_GET['theme'])
    ) {
        $GLOBALS['_theme_set_preview_id'] = true;
        $_GET['preview_id'] = $p;
        $_GET['preview_nonce'] = wp_create_nonce("post_preview_$p");
    }
}
add_action('template_redirect', 'theme_fix_is_preview', 0);

/**
 * Set $wp_query->is_preview = false back
 * Restore changes by theme_fix_is_preview
 *
 * @see theme_fix_is_preview
 */
function theme_restore_is_preview() {
    if (isset($GLOBALS['_theme_set_preview_id'])) {
        unset($GLOBALS['_theme_set_preview_id'], $_GET['preview_id'], $_GET['preview_nonce']);
    }
}
add_action('template_redirect', 'theme_restore_is_preview', 1001);

function theme_woocommerce_enabled() {
    return isset($GLOBALS['woocommerce']) && file_exists(get_template_directory() . '/woocommerce/functions.php' );
}

theme_include_lib('logging.php', 'export');
theme_include_lib('defaults.php');
theme_include_lib('misc.php');
theme_include_lib('wrappers.php');
theme_include_lib('sidebars.php');
theme_include_lib('navigation.php');
if (!class_exists('ShortcodesUtility')) {
    if (theme_is_preview()) {
        theme_include_lib('shortcodes.php', 'export/themler-core/shortcodes');
    } else if (theme_is_export_action()) {
        theme_include_lib('shortcodes.php', '../' . get_template() . '_preview/export/themler-core/shortcodes');
    }
}
theme_include_lib('shortcodes-styles.php');
theme_include_lib('widgets.php');
theme_include_lib('rating.php');
theme_include_lib('post_templates.php');
theme_include_lib('class-tgm-plugin-activation.php');

if (get_option('theme_show_comments_anywhere')) {
    global $withcomments;
    $withcomments = true;
}

add_action('wp_enqueue_scripts', 'theme_update_scripts_and_styles', 1000);
remove_action('wp_enqueue_scripts', 'themler_add_scripts_and_styles');
remove_action('wp_enqueue_scripts', 'theme_add_scripts_and_styles'); // old plugin version
add_action('wp_head', 'theme_update_posts_styles', 1000);
add_action('media_upload_image_header', 'wp_media_upload_handler');

function theme_header_rel_link() {
	if (theme_get_option('theme_header_clickable')):
		?><link rel='header_link' href='<?php echo esc_url(theme_get_option('theme_header_link')); ?>' /><?php
	endif;
}
add_action('wp_head', 'theme_header_rel_link');
add_action('login_head', 'theme_header_rel_link');
add_action('init', 'theme_editor_auto_login');

add_theme_support('post-thumbnails');
add_theme_support('nav-menus');
add_theme_support('automatic-feed-links');
add_theme_support('post-formats', array('aside', 'gallery'));
add_theme_support('woocommerce');
add_theme_support('title-tag');
add_theme_support('themler-core', array('grid-columns-12'));

if ( ! function_exists( '_wp_render_title_tag' ) ) {
    function theme_slug_render_title() {
        ?>
        <title><?php wp_title( '|', true, 'right' ); ?></title>
    <?php
    }
    add_action( 'wp_head', 'theme_slug_render_title' );

    function theme_wp_title( $title, $sep ) {
        global $paged, $page;
        if ( is_feed() ) {
            return $title;
        }
        if ( $paged >= 2 || $page >= 2 ) {
            $title = "$title $sep " . sprintf( __( 'Page %s', 'default' ), max( $paged, $page ) );
        }
        return $title;
    }
    add_filter( 'wp_title', 'theme_wp_title', 10, 2 );
}



function theme_autoinclude($folder){
    $path = get_stylesheet_directory() . '/' . $folder;

    if (!is_dir($path)) {
        return ;
    }

    if ($handle = opendir($path)) {
        while (($name = readdir($handle)) !== false) {
            if (!preg_match("#.php$#", $name)) {
                continue;
            }
            locate_template(array($folder . '/' . $name), true);
        }
        closedir($handle);
    }
}

theme_autoinclude('includes');

global $theme_error_messages;
$theme_error_messages = array();

function theme_add_error($message) {
    global $theme_error_messages;
    $theme_error_messages[] = $message;
}

function theme_add_errors() {
    global $theme_error_messages;
    foreach($theme_error_messages as $message) {
        echo '<div class="error"><p>' . $message . '</p></div>';
    }
}
add_action('admin_notices', 'theme_add_errors');

function theme_add_preview_args($link) {
    if (theme_is_customizer()) {
        $theme = isset($_GET['theme']) ? $_GET['theme'] : '';
        $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
        $original = isset($_GET['original']) ? $_GET['original'] : '';
        return add_query_arg(array('preview' => 1, 'theme' => $theme, 'wp_customize' => 'on', 'nonce' => $nonce, 'original' => $original), $link);
    } else {
        $template = isset($_GET['template']) ? $_GET['template'] : '';
        $stylesheet = isset($_GET['stylesheet']) ? $_GET['stylesheet'] : '';
        return add_query_arg(array('preview' => 1, 'template' => $template, 'stylesheet' => $stylesheet, 'preview_iframe' => 1), $link);
    }
}

function theme_remove_preview_args($link) {
    return remove_query_arg(array(
        'preview', 'preview_iframe', 'template', 'stylesheet', 'page', 'TB_iframe', // preview-theme
        'original', 'theme', 'wp_customize', 'nonce', // wp-customize
        'custom_template', 'custom_page', 'default' // custom templates
    ), $link);
}
add_filter('themler_remove_preview_args', 'theme_remove_preview_args');

if (!theme_is_customizer()) {
    theme_include_lib('theme-customizer.php', 'library/customizer');
}

function theme_register_thumbnail_size() {
    $width = absint(theme_get_option('theme_metadata_thumbnail_width'));
    $height = absint(theme_get_option('theme_metadata_thumbnail_height'));
    if ($width && $height) {
        foreach(array('thumbnail', 'medium', 'large') as $_size) {
            $w = absint(get_option($_size . '_size_w'));
            if (abs($w - $width) <= 100)
                return;
        }
        add_image_size('post_image_thumbnail', $width, $height);
    }
}
add_action('after_setup_theme', 'theme_register_thumbnail_size');

function theme_sidebars_compare($sidebar1, $sidebar2) {

    $priority1 = theme_get_array_value($sidebar1, 'priority', 10000);
    $priority2 = theme_get_array_value($sidebar2, 'priority', 10000);
    if ($priority1 !== $priority2) {
        return $priority1 < $priority2 ? -1 : 1;
    }

    $name1 = theme_get_array_value($sidebar1, 'name', '');
    $name2 = theme_get_array_value($sidebar2, 'name', '');
    return strnatcmp($name1, $name2);
}
uasort($wp_registered_sidebars, 'theme_sidebars_compare');

if (!isset($content_width) && ($_content_width = apply_filters('theme_content_width', false))) {
    $content_width = $_content_width;
}

function theme_register_required_plugins() {

    $plugin_source = get_template_directory() . '/plugins/themler-core.zip';
    if (!file_exists($plugin_source)) {
        // noting to install
        return;
    }

    $plugins = array(
        array(
            'name'               => 'Themler-Core', // The plugin name.
            'slug'               => 'themler-core', // The plugin slug (typically the folder name).
            'source'             => $plugin_source, // The plugin source.
            'required'           => false,          // If false, the plugin is only 'recommended' instead of required.
            'version'            => '',             // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
            'force_activation'   => false,          // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false,          // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
            'external_url'       => '',             // If set, overrides default API URL and points to an external URL.
            'is_callable'        => '',             // If set, this callable will be be checked for availability to determine if a plugin is active.
        )
    );

    $config = array(
        'id'           => 'tgmpa-themler',         // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to bundled plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'parent_slug'  => 'themes.php',            // Parent menu slug.
        'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
        'strings' => array(
            'nag_type' => 'updated',
        ),
    );
    tgmpa($plugins, $config);
}
add_action('tgmpa_register', 'theme_register_required_plugins');

if (theme_woocommerce_enabled()) {
    include(dirname(__FILE__) . '/woocommerce/functions.php');
}

include(dirname(__FILE__) . '/functions-additional.php');

if (is_admin()) {
	theme_include_lib('options.php');
	theme_include_lib('admins.php');
    theme_include_lib('export.php', 'export');
	function theme_add_option_page() {
		add_theme_page(__('Theme Options', 'default'), __('Theme Options', 'default'), 'edit_themes', basename(__FILE__), 'theme_print_options');
	}

	add_action('admin_menu', 'theme_add_option_page');
	add_action('sidebar_admin_setup', 'theme_widget_process_control');
	add_filter('widget_update_callback', 'theme_update_widget_additional');
	add_action('add_meta_boxes', 'theme_add_meta_boxes');
	add_action('save_post', 'theme_save_post');

    theme_include_lib('content-importer.php', 'content');
    theme_include_lib('converter.php', 'export');
	return;
}

function theme_print_preview_signature() {
    echo "<!-- WP_CUSTOMIZER_SIGNATURE -->";
}

function theme_remove_customize_preview_signature() {
    global $wp_customize;
    if (isset($wp_customize) && method_exists($wp_customize, 'remove_preview_signature')) {
        remove_action('wp_redirect_status', array($wp_customize, 'wp_redirect_status'), 1000 );

        // wrap WP_CUSTOMIZER_SIGNATURE in html-comments
        $wp_customize->remove_preview_signature();
        add_action('shutdown', 'theme_print_preview_signature', 1000);
    }
}
add_action('customize_preview_init', 'theme_remove_customize_preview_signature');

if (theme_is_customizer()) {
    add_filter('show_admin_bar', false);
}

if (theme_can_view_preview() && theme_is_customizer() && isset($_REQUEST['original'])) {
    function theme_get_template_preview_filter() {
        return $_REQUEST['theme'];
    }

    function theme_pre_option_template_preview_filter() {
        return $_REQUEST['original'];
    }

    add_filter('pre_option_template', 'theme_pre_option_template_preview_filter');
    add_filter('template', 'theme_get_template_preview_filter');

    add_filter('pre_option_stylesheet', 'theme_pre_option_template_preview_filter');
    add_filter('stylesheet', 'theme_get_template_preview_filter');
}

// remove widgets overriding by wp-customizer
global $wp_customize;
if (isset($wp_customize) && property_exists($wp_customize, 'widgets')) {
    remove_action('wp_loaded', array($wp_customize->widgets, 'override_sidebars_widgets_for_theme_switch'));
}

function theme_is_preview_url($location) {
    if (strpos($location, 'wp_customize=') !== false && strpos($location, 'theme=') !== false) {
        return true;
    }
    if (strpos($location, 'preview=') !== false && strpos($location, 'template=') !== false) {
        return true;
    }
    return false;
}

function theme_fix_woocommerce_redirect($location) {
	if (!theme_is_preview()) {
		return $location;
    }

	if (!theme_is_preview_url($location)) {
        return theme_add_preview_args($location);
	}
	return $location;
}
add_filter('wp_redirect', 'theme_fix_woocommerce_redirect', 100);

function theme_fix_preview_redirect($redirect_url) {
    if (theme_is_preview()) {
        $new_redirect_url = add_query_arg('preview', '1', $redirect_url);
        if (theme_is_preview_url($new_redirect_url)) {
            $redirect_url = $new_redirect_url;
        }
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'theme_fix_preview_redirect');

function theme_update_posts_styles() {
    global $wp_query;
    if (!is_singular()) {
        $post_id = get_queried_object_id();
        if ($post_id == 0 && theme_is_home()) {
            $post_id = get_option('page_for_posts');
        }
        echo get_post_meta($post_id, 'theme_head', true);
    }
    ob_start();
    while ($wp_query->have_posts()) {
        the_post();
        $post_id = theme_get_the_ID();
        echo get_post_meta($post_id, 'theme_head', true);
    }
    ob_get_clean();
    wp_reset_postdata();
}

function theme_get_option($name) {
	global $theme_default_options;
	$result = get_option($name);
	if ($result === false) {
		$result = theme_get_array_value($theme_default_options, $name);
	}
	return $result;
}

function theme_get_widget_meta_option($widget_id, $name) {
	global $theme_default_meta_options;
	if (!preg_match('/^(.*[^-])-([0-9]+)$/', $widget_id, $matches) || !isset($matches[1]) || !isset($matches[2])) {
		return theme_get_array_value($theme_default_meta_options, $name);
	}
	$type = $matches[1];
	$id = $matches[2];
	$wp_widget = get_option('widget_' . $type);
	if (!$wp_widget || !isset($wp_widget[$id])) {
		return theme_get_array_value($theme_default_meta_options, $name);
	}
	if (!isset($wp_widget[$id][$name])) {
		$wp_widget[$id][$name] = theme_get_array_value(get_option($name), $widget_id, theme_get_array_value($theme_default_meta_options, $name));
	}
	return $wp_widget[$id][$name];
}

function theme_set_widget_meta_option($widget_id, $name, $value) {
	if (!preg_match('/^(.*[^-])-([0-9]+)$/', $widget_id, $matches) || !isset($matches[1]) || !isset($matches[2])) {
		return;
	}
	$type = $matches[1];
	$id = $matches[2];
	$wp_widget = get_option('widget_' . $type);
	if (!$wp_widget || !isset($wp_widget[$id])) {
		return;
	}
	$wp_widget[$id][$name] = $value;
	update_option('widget_' . $type, $wp_widget);
}

function theme_get_meta_option($id, $name) {
	global $theme_default_meta_options;
	if (!is_numeric($id)) {
        return theme_get_array_value($theme_default_meta_options, $name);
    }
	$value = get_post_meta($id, '_' . $name, true);
	if ('' === $value) {
		$value = theme_get_array_value(get_option($name), $id, theme_get_array_value($theme_default_meta_options, $name));
		theme_set_meta_option($id, $name, $value);
	}
	return $value;
}

function theme_set_meta_option($id, $name, $value) {
	update_post_meta($id, '_' . $name, $value);
}

function theme_get_post_id() {
	$post_id = theme_get_the_ID();
	if ($post_id != '') {
		$post_id = 'post-' . $post_id;
	}
	return $post_id;
}

function theme_get_the_ID() {
	global $post;
	return $post->ID;
}

function theme_get_post_class() {
	return implode(' ', get_post_class());
}

function theme_include_lib($name, $dir = 'library') {
	locate_template(array($dir . '/' . $name), true);
}

function theme_get_post_thumbnail($args = array()) {
	global $post;

	$size = theme_get_array_value($args, 'size', array(theme_get_option('theme_metadata_thumbnail_width'), theme_get_option('theme_metadata_thumbnail_height')));
	$auto = theme_get_array_value($args, 'auto', theme_get_option('theme_metadata_thumbnail_auto'));
	$featured = theme_get_array_value($args, 'featured', theme_get_option('theme_metadata_use_featured_image_as_thumbnail'));
	$title = esc_attr(theme_get_array_value($args, 'title', get_the_title($post) ));
    $img_class = esc_attr(theme_get_array_value($args, 'img_class', ''));
    $link_class = esc_attr(theme_get_array_value($args, 'class', ''));
    $img_attributes = theme_get_array_value($args, 'img_attributes', '');
    $attributes = theme_get_array_value($args, 'attributes', '');

	$result = '';

	if ($featured && (has_post_thumbnail())) {
		ob_start();
		the_post_thumbnail($size, array('alt' => $title, 'title' => $title, 'class' => $img_class));
		$result = ob_get_clean();
	} elseif ($auto) {
		$attachments = get_children(array('post_parent' => $post->ID, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID'));
		if ($attachments) {
			$attachment = array_shift($attachments);
			$img = wp_get_attachment_image_src($attachment->ID, $size);
			if (isset($img[0])) {
				$result = '<img src="' . $img[0] . '" alt="' . $title . '" width="' . $img[1] . '" height="' . $img[2] . '" title="' . $title . '" class="'. $img_class .'" />';
			}
		}
	}
    $result = str_replace('<img', '<img ' . $img_attributes, $result);
	if ($result !== '') {
		$result = '<a href="' . get_permalink($post->ID) . '" title="' . $title . '" class="'. $link_class . '" ' . $attributes .'>' . $result . '</a>';
	}
	return $result;
}

function theme_get_content($args = array()) {
    $post_id = get_queried_object_id();
    $more_tag = theme_get_array_value($args, 'more_tag', __('Continue reading <span class="meta-nav">&rarr;</span>', 'default'));

    $ignore_wpautop = theme_get_meta_option($post_id, 'theme_use_wpautop') === '0';
    if ($ignore_wpautop) {
        remove_filter('the_content', 'wpautop');
    }
    ob_start();
    the_content($more_tag);
    $content = ob_get_clean();
    if ($ignore_wpautop) {
        add_filter('the_content', 'wpautop');
    }
    return $content . wp_link_pages(array(
        'before' => '<p><span class="page-navi-outer page-navi-caption"><span class="page-navi-inner">' . __('Pages', 'default') . ': </span></span>',
        'after' => '</p>',
        'link_before' => '<span class="page-navi-outer"><span class="page-navi-inner">',
        'link_after' => '</span></span>',
        'echo' => 0
    ));
}

define('THEME_MORE_TOKEN', '%%theme_more%%');
define('THEME_TAG_TOKEN', '%%theme_tag_token%%');
define('THEME_TOKEN_TYPE_WORD', 0);
define('THEME_TOKEN_TYPE_TAG', 1);
define('THEME_TOKEN_TYPE_SPACE', 2);
define('THEME_TOKEN_TYPE_IGNORE', 3);

if (theme_get_option('theme_metadata_excerpt_strip_shortcodes')) {
    add_filter('get_the_excerpt', 'strip_shortcodes');
}

function theme_create_excerpt($excerpt, $max_tokens_count, $min_remainder, $count_symbols = false) {
    $content_parts = explode(THEME_TAG_TOKEN, str_replace(array('<', '>'), array(THEME_TAG_TOKEN . '<', '>' . THEME_TAG_TOKEN), $excerpt));
    $content = array();
    $tokens_count = 0;
    $style_balance = 0;
    $script_balance = 0;
    foreach ($content_parts as $part) {
        if (theme_strpos($part, '<') !== false || theme_strpos($part, '>') !== false) {
            if ($part === '<style>') {
                $style_balance++;
            } else if ($part === '</style>') {
                $style_balance--;
            } else if ($part === '<script>') {
                $script_balance++;
            } else if ($part === '</script>') {
                $script_balance--;
            }
            $content[] = array(THEME_TOKEN_TYPE_TAG, $part);
        } else {
            $all_chunks = preg_split('/([\s])/u', $part, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($all_chunks as $chunk) {
                if ('' != trim($chunk)) {
                    if ($style_balance > 0 || $script_balance > 0) {
                        $content[] = array(THEME_TOKEN_TYPE_IGNORE, $chunk);
                    } else {
                        $content[] = array(THEME_TOKEN_TYPE_WORD, $chunk);
                        $tokens_count += $count_symbols ? theme_strlen($chunk) : 1;
                    }
                } elseif ($chunk != '') {
                    $content[] = array(THEME_TOKEN_TYPE_SPACE, $chunk);
                }
            }
        }
    }

    if ($max_tokens_count < $tokens_count && $max_tokens_count + $min_remainder <= $tokens_count) {
        $current_count = 0;
        $excerpt = '';
        foreach ($content as $node) {
            if ($node[0] === THEME_TOKEN_TYPE_WORD) {
                $current_count += $count_symbols ? theme_strlen($node[1]) : 1;
            }
            $excerpt .= $node[1];
            if ($current_count >= $max_tokens_count) {
                break;
            }
        }
        return $excerpt;
    }
    return false;
}

function theme_get_excerpt($args = array()) {
    global $post;
    $more_tag = theme_get_array_value($args, 'more_tag', __('Continue reading <span class="meta-nav">&rarr;</span>', 'default'));
    $auto = theme_get_array_value($args, 'auto', theme_get_option('theme_metadata_excerpt_auto'));
    $all_words = theme_get_array_value($args, 'all_words', theme_get_option('theme_metadata_excerpt_words'));
    $min_remainder = theme_get_array_value($args, 'min_remainder', theme_get_option('theme_metadata_excerpt_min_remainder'));
    $allowed_tags = theme_get_array_value($args, 'allowed_tags',
        (theme_get_option('theme_metadata_excerpt_use_tag_filter')
            ? explode(',',str_replace(' ', '', theme_get_option('theme_metadata_excerpt_allowed_tags')))
            : null));
    $perma_link = get_permalink($post->ID);
    $show_more_tag = false;
    $tag_disbalance = false;
    if (post_password_required($post)) {
        return get_the_excerpt();
    }
    if ($auto && has_excerpt($post->ID)) {
        $excerpt = get_the_excerpt();
        $show_more_tag = theme_strlen($post->post_content) > 0;
    } else {
        $excerpt = get_the_content(THEME_MORE_TOKEN);
        if (theme_get_option('theme_metadata_excerpt_strip_shortcodes')) {
            $excerpt = strip_shortcodes($excerpt);
        }
        // hack for badly written plugins
        ob_start();
        echo apply_filters('the_content', $excerpt);
        $excerpt = ob_get_clean();
        global $multipage;
        if ($multipage && theme_strpos($excerpt, THEME_MORE_TOKEN) === false) {
            $show_more_tag = true;
        }
        if (theme_is_empty_html($excerpt))
            return $excerpt;
        if ($allowed_tags !== null) {
            $allowed_tags = '<' . implode('><', $allowed_tags) . '>';
            $excerpt = strip_tags($excerpt, $allowed_tags);
        }
        if (theme_strpos($excerpt, THEME_MORE_TOKEN) !== false) {
            $excerpt = str_replace(THEME_MORE_TOKEN, '', $excerpt);
            $show_more_tag = true;
        } elseif ($auto && is_numeric($all_words)) {
            $all_words = intval($all_words);
            $min_remainder = intval($min_remainder);

            $new_excerpt = theme_create_excerpt($excerpt, $all_words, $min_remainder);
            if (is_string($new_excerpt)) {
                $excerpt = $new_excerpt;
                $show_more_tag = true;
                $tag_disbalance = true;
            }
        }
    }
    if ($show_more_tag && theme_get_option('theme_show_morelink')) {
        $excerpt = $excerpt . ' ' . str_replace(array('[url]', '[text]'), array($perma_link, $more_tag), theme_get_option('theme_morelink_template'));
    }
    if ($tag_disbalance) {
        $excerpt = force_balance_tags($excerpt);
    }
    return $excerpt;
}

function theme_get_search() {
	ob_start();
	get_search_form();
	return ob_get_clean();
}

function theme_is_home() {
	return (is_home() && !is_paged());
}

function theme_404_content($args = '') {
    $args = wp_parse_args($args, array(
        'error_title' => __('Not Found', 'default'),
        'error_message' => __('Apologies, but the page you requested could not be found. Perhaps searching will help.', 'default')
    ));
    extract($args);
    echo '<h4>' . $args['error_title'] . '</h4>';
    echo '<p class="center">' . $args['error_message'] . '</p>';

    if (theme_get_option('theme_show_random_posts_on_404_page')) {
        ob_start();
        echo '<h4 class="box-title">' . theme_get_option('theme_show_random_posts_title_on_404_page') . '</h4>';
        ?>
        <ul>
            <?php
            global $post;
            $rand_posts = get_posts('numberposts=5&orderby=rand');
            foreach ($rand_posts as $post) :
                ?>
                <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php
        echo ob_get_clean();
    }
    if (theme_get_option('theme_show_tags_on_404_page')) {
        ob_start();
        echo '<h4 class="box-title">' . theme_get_option('theme_show_tags_title_on_404_page') . '</h4>';
        wp_tag_cloud('smallest=9&largest=22&unit=pt&number=200&format=flat&orderby=name&order=ASC');
        echo ob_get_clean();
    }
}

function theme_get_previous_post_link($format = '&laquo; %link', $link = '%title', $in_same_cat = false, $excluded_categories = '') {
	return theme_get_adjacent_post_link($format, $link, $in_same_cat, $excluded_categories, true);
}

function theme_get_next_post_link($format = '%link &raquo;', $link = '%title', $in_same_cat = false, $excluded_categories = '') {
	return theme_get_adjacent_post_link($format, $link, $in_same_cat, $excluded_categories, false);
}

function theme_get_adjacent_post_link($format, $link, $in_same_cat = false, $excluded_categories = '', $previous = true) {
	if ($previous && is_attachment())
		$post = & get_post($GLOBALS['post']->post_parent);
	else
		$post = get_adjacent_post($in_same_cat, $excluded_categories, $previous);

	if (!$post)
		return '';

	$title = strip_tags($post->post_title);

	if (empty($post->post_title))
		$title = $previous ? __('Previous Post', 'default') : __('Next Post', 'default');

	$title = apply_filters('the_title', $title, $post->ID);
	$short_title = $title;
	if (theme_get_option('theme_single_navigation_trim_title')) {
		$short_title = theme_trim_long_str($title, theme_get_option('theme_single_navigation_trim_len'));
	}
	$date = mysql2date(get_option('date_format'), $post->post_date);
	$rel = $previous ? 'prev' : 'next';

	$string = '<a href="' . get_permalink($post) . '" title="' . esc_attr($title) . '" rel="' . $rel . '">';
	$link = str_replace('%title', $short_title, $link);
	$link = str_replace('%date', $date, $link);
	$link = $string . $link . '</a>';

	$format = str_replace('%link', $link, $format);

	$adjacent = $previous ? 'previous' : 'next';
	return apply_filters("{$adjacent}_post_link", $format, $link);
}

function theme_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;

    switch ($comment->comment_type) {

    case '' :
?>
        <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
            <?php ob_start(); ?>
            <div class="comment-author vcard">
                <?php echo theme_get_avatar(array('id' => $comment, 'size' => 48)); ?>
                <?php printf(__('%s <span class="says">says:</span>', 'default'), sprintf('<cite class="fn">%s</cite>', get_comment_author_link())); ?>
            </div>
            <?php if ($comment->comment_approved == '0') : ?>
            <em><?php _e('Your comment is awaiting moderation.', 'default'); ?></em>
            <br />
            <?php endif; ?>

            <div class="comment-meta commentmetadata"><a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>">
                <?php printf(__('%1$s at %2$s', 'default'), get_comment_date(), get_comment_time()); ?></a><?php edit_comment_link(__('(Edit)', 'default'), ' '); ?>
            </div>

            <div class="comment-body"><?php comment_text(); ?></div>

            <div class="reply">
                <?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
            </div>
            <?php echo '<div id="comment-'.get_comment_ID().'">' . ob_get_clean() . '</div>'; ?>
<?php
        break;
    case 'pingback' :
    case 'trackback' :
?>
        <li class="post pingback">
            <?php ob_start(); ?>
            <p><?php _e('Pingback:', 'default'); ?> <?php comment_author_link(); ?><?php edit_comment_link(__('(Edit)', 'default'), ' '); ?></p>
<?php
            echo '<div class="' . $comment->comment_type . '">' . ob_get_clean() . '</div>';
        break;
    }
}

function theme_replace_attr($subject, $attr, $value) {
    if ($value) {
        return preg_replace("/(" . $attr . "=)\'(.*?)\'/", '$1\'' . $value . '\'', $subject);
    } else {
        return preg_replace("/(" . $attr . "=)\'(.*?)\'/", '', $subject);
    }
}

function theme_get_avatar($args, $remove_size = false) {
	$args = wp_parse_args($args, array(
        'class' => '',
        'attributes' => '',
        'id' => false,
        'size' => 96,
        'default' => '',
        'alt' => false,
        'url' => false
    ));
	$result = get_avatar($args['id'], $args['size'], $args['default'], $args['alt']);
    if ($args['class']) {
        $result = theme_replace_attr($result, 'class', $args['class']);
    }
    if ($remove_size) {
        $result = theme_replace_attr($result, 'height', '');
        $result = theme_replace_attr($result, 'width', '');
    }
    $result = str_replace('<img', '<img ' . $args['attributes'], $result);
	if ($result && $args['url']) {
        $result = '<a href="' . esc_url($args['url']) . '">' . $result . '</a>';
	}
	return $result;
}

function theme_get_next_post() {
	static $ended = false;
	if (!$ended) {
		if (have_posts()) {
			the_post();
			get_template_part('content', get_post_format());
		} else {
			$ended = true;
		}
	}
}

function theme_get_path() {
    $template = get_template();
    $theme_root = get_theme_root( $template );
    return $theme_root . '/' . $template;
}

function theme_print_content() {
    global $theme_content_function;
    if (!isset($theme_content_function)) {
        $theme_content_function = 'theme_blog';
    }
    if (function_exists($theme_content_function)) {
        call_user_func($theme_content_function);
        return;
    }
}

function theme_get_image_path($image) {
    $template = get_template();
    $theme_root = get_theme_root($template);
    $template_dir = $theme_root . '/' . $template;
    $image_path = $template_dir . '/' . $image;

    if (theme_is_preview() && !file_exists($image_path)) {
        return $base_image_url = preg_replace('/(.*)(_preview$)/', '$1', get_template_directory_uri()) . '/' . $image;
    }
    return get_template_directory_uri() . '/' . $image;
}
add_filter('theme_image_path', 'theme_get_image_path');

function theme_get_optimal_path($name, $ext){
    $suffix = '.min';
    $file = '/' . $name . '.' . $ext;
    $min_file = '/' . $name . $suffix . '.' . $ext;
    return (isset($_GET['preview']) || !file_exists(theme_get_path() . $min_file) || filemtime(theme_get_path() . $min_file) < filemtime (theme_get_path() . $file))
        ? $file : $min_file;
}

function theme_update_scripts_and_styles() {
    $template_url = str_replace( array( 'http:', 'https:' ), '', get_bloginfo('template_url', 'display'));
    $version = wp_get_theme()->get('Version');
    wp_enqueue_script( 'jquery', false, array(), $version, 'all' );

    wp_register_style( 'theme-bootstrap',  $template_url . theme_get_optimal_path('bootstrap','css'), array(), $version, 'all' );
    wp_enqueue_style("theme-bootstrap");

    if (theme_is_preview() && file_exists(theme_get_path() . '/style.preview.php')) {
        wp_register_style( 'theme-style', $template_url . '/style.preview.php', array('theme-bootstrap'), $version, 'all' );
    } else {
        wp_register_style( 'theme-style', $template_url . theme_get_optimal_path('style','css'), array('theme-bootstrap'), $version, 'all' );
    }
    wp_enqueue_style("theme-style");

    wp_register_script("theme-bootstrap", $template_url . '/bootstrap.min.js', array('jquery'), $version);
    wp_enqueue_script("theme-bootstrap");

    wp_register_script("theme-script", $template_url . '/script.js', array('jquery', 'theme-bootstrap'), $version);
    wp_enqueue_script("theme-script");

    if (theme_is_preview()) {
        wp_register_script("script.preview.js", $template_url . '/script.preview.js', array('jquery'), $version);
        wp_enqueue_script("script.preview.js");
    }

    if (theme_woocommerce_enabled() && is_checkout()) {
        wp_dequeue_script('wc-checkout');
        wp_enqueue_script('wc-checkout', $template_url . '/checkout.min.js', array('jquery'), false, true);
    }

    if (theme_woocommerce_enabled() && is_product() && !theme_wc_disabled_button_supported()) {
        wp_register_script('add-to-cart-button', $template_url . '/woocommerce/add-to-cart-button.js', array('wc-add-to-cart-variation'), false, true);
        wp_enqueue_script('add-to-cart-button');
    }

    if (is_singular() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

function theme_remove_customize_scripts(&$scripts) {
    $scripts->remove('customize-preview');
}
add_action('wp_default_scripts', 'theme_remove_customize_scripts');

/**
 * Remove other bootstrap scripts according to theme_disable_bootstrap_scripts option
 */
function theme_check_bootstrap() {
    global $wp_scripts, $theme_removed_scripts;

    if (!is_object($wp_scripts)) {
        return;
    }

    $theme_removed_scripts = array();
    foreach($wp_scripts->queue as $handle) {
        if ($handle === 'theme-bootstrap') {
            continue;
        }
        $dependency = $wp_scripts->registered[$handle];
        if (property_exists($dependency, 'src')) {
            $src = $dependency->src;
            if (preg_match('#bootstrap.js|bootstrap.min.js#', $src)) {
                $theme_removed_scripts[] = $handle;
            }
        }
    }
    if (!empty($theme_removed_scripts) && theme_get_option('theme_disable_bootstrap_scripts')) {
        echo '<!-- removed scripts: ' . implode(', ', $theme_removed_scripts) . ' -->';
        foreach($theme_removed_scripts as $handle) {
            wp_dequeue_script($handle);
        }
    }
}
add_action('wp_print_scripts', 'theme_check_bootstrap');

function theme_show_double_bootstrap_message() {
    global $wp_scripts, $theme_removed_scripts;
    if (property_exists($wp_scripts, 'done') && is_array($wp_scripts->done) && is_array($theme_removed_scripts)) {
        $printed_handles = array_intersect($theme_removed_scripts, $wp_scripts->done);
        if ($printed_handles) {
            $options_url = admin_url() . 'themes.php?page=functions.php#heading-bootstrap';
            $msg = '<strong>Warning!</strong>'
                . '<p>You have several conflicting bootstrap.js files loaded on the site.</p>'
                . '<p>Various unexpected issues in site functionality may arise.</p>'
                . '<p>Please disable all additional bootstrap.js files using the following <a href="' . $options_url . '">option</a>.</p>';

            $msg .= '<ul>';
            foreach($printed_handles as $handle) {
                $msg .= '<li><strong>' . $wp_scripts->registered[$handle]->src . '</strong></li>';
            }
            $msg .= '</ul>';
            $msg .= '<p>For more information please refer to <a href="http://answers.themler.io/articles/82654/bootstrap-conflicts">this article</a>.</p>';

            ob_start();
            theme_warning_message($msg);
            $html = ob_get_clean();
            ?>
            <script>
                var cookieKey = 'theme_hide_bootstrap_message';
                var html = jQuery(<?php echo json_encode($html); ?>);
                html.on('click', '.close', function() {
                    document.cookie = cookieKey + '=1';
                });

                jQuery(function() {
                    if (document.cookie.indexOf(cookieKey) === -1) {
                        $('body').prepend(html);
                    }
                });
            </script>
            <?php
        }
    }
}
if (theme_can_view_preview()) {
    add_action('wp_head', 'theme_show_double_bootstrap_message', 100);
}

function theme_editor_auto_login() {
    if(isset($_GET['editor_auto_login'])) {
        $_POST['log'] = $_GET['log'];
        $_POST['pwd'] = $_GET['pwd'];
    }
}

global $wp_locale;
if (isset($wp_locale)) {
    $wp_locale->text_direction = 'ltr';
}

function theme_get_languages() {
    $languages_string = theme_get_option('theme_languages');
    $result = array();
    foreach (explode("\n", $languages_string) as $str) {
        $pair = explode(' ', trim($str), 2);
        if (count($pair) == 2) {
            $result[] = array(
                'locale' => $pair[0],
                'display_name' => $pair[1],
            );
        }
    }
    return $result;
}

function theme_get_locale_display_name($locale) {
    foreach(theme_get_languages() as $lang) {
        if ($lang['locale'] === $locale) {
            return esc_html($lang['display_name']);
        }
    }
    return '';
}

function theme_get_language_title($text_type, $show_label, $show_arrow) {
    $label = $show_label ? __('Language', 'default') : '';
    $locale = get_locale();

    switch ($text_type) {
        case 'short':
            $value = preg_replace('/.+_(.+)/', '$1', $locale);
            break;
        case 'full':
            $value = theme_get_locale_display_name($locale);
            break;
        default:
            $value = '';
    }

    if ($value && $label) {
        $label .= ': ';
    }

    $title = "<span>$label$value</span>";

    if ($show_arrow) {
        $title .= ' <span class="caret"></span>';
    }
    return $title;
}

function theme_page_title_separator_filter($sep) {
    $sep = get_option('theme_page_title_separator');
    if (false === $sep) {
        $sep = ' ';
    }
    return $sep;
}

function theme_document_title_filter($parts) {
    unset($parts['tagline'], $parts['site']);
    return $parts;
}

function theme_get_page_title() {
    $title = '';

    if (function_exists('wp_get_document_title') && get_option('theme_use_document_title')) {
        add_filter('document_title_separator', 'theme_page_title_separator_filter');
        add_filter('document_title_parts', 'theme_document_title_filter');
        $title = wp_get_document_title();
        remove_filter('document_title_parts', 'theme_document_title_filter');
        remove_filter('document_title_separator', 'theme_page_title_separator_filter');
    }

    if (!$title) {
        $separator = get_option('theme_page_title_separator');
        if (false === $separator) {
            $separator = ' ';
        }
        $title = trim(wp_title($separator, false));
    }

    if (is_home()) {
        $home_title = trim(get_option('theme_home_page_title'));
        if ($home_title)
            $title = $home_title;
    }

    if (!$title) {
        $title = get_bloginfo('name');
    } else if (function_exists('is_shop') && is_shop()) {
        $shop_page = get_post(wc_get_page_id('shop'));
        $title = apply_filters('the_title', ($shop_page_title = get_option('woocommerce_shop_page_title')) ? $shop_page_title : $shop_page->post_title);
    }
    return $title;
}

function theme_store_jquery_script() {
    echo '<script>window.wpJQuery = window.jQuery;</script>';
}
add_action('wp_head', 'theme_store_jquery_script');

function theme_do_action($tag, $avoid_hooks = array()) {
    $add_hooks = array();
    foreach($avoid_hooks as $func) {
        if (remove_action($tag, $func[0], $func[1])) {
            $add_hooks[] = $func;
        }
    }
    do_action($tag);
    foreach($add_hooks as $func) {
        add_action($tag, $func[0], $func[1]);
    }
}

function theme_get_footer_name($name) {
    for($id = 1; $id <= 4; $id++) {
        if ('footer'.$id === $name) {
            return $name;
        }
    }
    return 'footer1';
}

function theme_custom_menu_filter($args) {
    // need to remove class attribute for applying default list styles
    $args['items_wrap'] = '<ul id="%1$s">%3$s</ul>';
    return $args;
}
if (version_compare($GLOBALS['wp_version'], '4.2', '<' )) {
    add_filter('wp_nav_menu_args', 'theme_custom_menu_filter');
} else {
    add_filter('widget_nav_menu_args', 'theme_custom_menu_filter', 9);
}

function theme_need_convert_preview_links() {
    if (theme_is_customizer())
        return true;
    if (!function_exists('preview_theme_ob_filter'))
        return true;
    $test_data = '<a href="#">';
    return preview_theme_ob_filter($test_data) === $test_data;
}

if (theme_is_preview()) {
    if (!theme_need_convert_preview_links()) { // compatibility with < 4.3 versions
        function theme_preview_theme_ob_filter($content) {
            return preg_replace_callback("|(<a.*?href)(=[\"'])(.*?)([\"'].*?>)|", 'theme_preview_theme_ob_filter_callback', $content);
        }
        function theme_preview_theme_ob_filter_callback($matches) {
            return $matches[1] . ' ' . $matches[2] . $matches[3] . $matches[4];
        }
        ob_start('theme_preview_theme_ob_filter');
    }
}

function theme_social_icons_filter($content) {
    $url   = urlencode(theme_remove_preview_args($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
    $title = urlencode(theme_get_page_title());
    return str_replace(array('[DYNAMIC-URL]', '[DYNAMIC-TITLE]'), array($url, $title), $content);
}
add_filter('theme_body', 'theme_social_icons_filter');

/**
 * Forcibly load specified custom template for sample page.
 * Used for preview only.
 *
 * @param $template
 * @return string Specified template path
 */
function theme_catch_template($template) {
    if (isset($_REQUEST['custom_page'])) {
        $template_file = $_REQUEST['custom_page'];
        if ($template_file === 'default') {
            $template_file = 'page.php';
        }
        $custom_template = get_stylesheet_directory() . '/' . $template_file;
        if (file_exists($custom_template)) {
            $template = $custom_template;
        }
    }
    return $template;
}
if (isset($_REQUEST['custom_page']) && theme_can_view_preview()) {
    add_filter('template_include', 'theme_catch_template', 100);
}

function theme_get_selected_template($type) {
    $type = sanitize_title_with_dashes($type);
    if (theme_can_view_preview() && isset($_GET['custom_template'])) {
        return $_GET['custom_template'];
    }
    return theme_get_option('theme_template_' . get_option('stylesheet') . '_' . $type);
}

global $theme_custom_templates;
$theme_custom_templates = array();

theme_include_lib('templates.php', 'templates');

/*
 * Include the template depends on value stored in database
 *
 * @global array $theme_custom_templates
 *
 * @param string $type The type of template (Home, Products, 404, ect)
 * @param string $default_name Name of the template
 */
function theme_load_template($type, $default_name) {
    global $theme_custom_templates;
    $name = theme_get_selected_template($type);
    if (!$name)
        $name = $default_name;

    $path = theme_get_array_value($theme_custom_templates[$type], $name, $theme_custom_templates[$type][$default_name]);
    require_once(get_template_directory() . '/' . $path);
}

function theme_register_template($type, $name, $path) {
    global $theme_custom_templates;
    if (!isset($theme_custom_templates[$type]))
        $theme_custom_templates[$type] = array();
    $theme_custom_templates[$type][$name] = $path;
}