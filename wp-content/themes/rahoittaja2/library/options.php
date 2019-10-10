<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $theme_options,
	   $theme_default_options, $theme_templates_options, $theme_template_type_priority,
	   $theme_template_query;

$theme_templates_options = array();
$theme_selectable_templates = array();
$theme_template_type_priority = array();
$theme_template_query = array();

function theme_add_template_option($type, $name, $caption, $type_priority = 10) {
    global $theme_templates_options, $theme_template_type_priority;
    $theme_template_type_priority[$type] = $type_priority;
    $theme_templates_options[$type][$name] = esc_attr(urldecode($caption));
}

function theme_add_template_query_option($type, $name, $caption) {
	global $theme_template_query;
	$theme_template_query[$name] = esc_attr(urldecode($caption));
}

theme_include_lib('templates_options.php');

$theme_menu_source_options = array(
	'Pages'      => __('Pages', 'default'),
	'Categories' => __('Categories', 'default'),
	'Products Categories' => __('Products Categories', 'default')
);

$theme_sidebars_style_options = array(
	'block'  => __('Block style', 'default'),
	'post'   => __('Post style', 'default'),
	'simple' => __('Simple text', 'default')
);

$theme_heading_options = array(
	'h1'  => __('<H1>', 'default'),
	'h2'  => __('<H2>', 'default'),
	'h3'  => __('<H3>', 'default'),
	'h4'  => __('<H4>', 'default'),
	'h5'  => __('<H5>', 'default'),
	'h6'  => __('<H6>', 'default'),
	'div' => __('<DIV>', 'default'),
);

$theme_widget_show_on = array(
	'all'           => __('All pages', 'default'),
	'selected'      => __('Selected pages', 'default'),
	'none_selected' => __('All pages except selected', 'default'),
);

$theme_search_modes = array(
    'post'    => __('Posts', 'default'),
	'product' => __('Products', 'default'),
    'all'    => __('All', 'default'),
);

$theme_options = array(
    array(
        'name' => __('Templates', 'default'),
        'type' => 'heading'
    )
);

function theme_compare_template_names($a, $b) {
    global $theme_template_type_priority;
    if ($theme_template_type_priority[$a] === $theme_template_type_priority[$b])
        return strnatcasecmp($a, $b);
    return $theme_template_type_priority[$b] - $theme_template_type_priority[$a];
}
uksort($theme_templates_options, 'theme_compare_template_names');

foreach($theme_templates_options as $template => $list) {
	natsort($list);
    $theme_options[] = array(
        'id'      => 'theme_template_' . get_option('stylesheet') . '_' . sanitize_title_with_dashes($template),
        'name'    => $template,
        'type'    => 'select',
        'options' => $list
    );
}

$theme_options[] = array(
	'name' => __('Templates blog pages', 'default'),
	'type' => 'heading',
	'desc' => __('Comma separated list of id\'s. Keep empty to use default.<br>Used as sample data for preview-theme.', 'default'),
);

foreach($theme_template_query as $template => $caption) {
	$theme_options[] = array(
		'id' => 'theme_template_' . $template . '_query_ids',
		'name' => $caption,
		'type' => 'text',
	);
}

$theme_options = array_merge($theme_options, array(
	array(
		'name' => __('Search', 'default'),
		'type' => 'heading'
	),
	array(
		'id'      => 'theme_search_mode',
		'name'    => __('Search mode', 'default'),
		'type'    => 'select',
		'options' => $theme_search_modes,
	),
	array(
		'name' => __('Header', 'default'),
		'type' => 'heading'
	),
	array(
		'id'   => 'theme_header_clickable',
		'name' => __('Make the header clickable', 'default'),
		'type' => 'checkbox',
        'desc' => __('Yes', 'default'),
	),
	array(
		'id'   => 'theme_header_link',
		'name' => __('Header link', 'default'),
		'type' => 'text',
		'depend' => 'theme_header_clickable',
	),
    array(
        'id'   => 'theme_logo_url',
        'name' => __('Logo image url', 'default'),
        'type' => 'text',
    ),
    array(
        'id'   => 'theme_logo_link',
        'name' => __('Logo link href', 'default'),
        'type' => 'text',
    ),
    array(
        'id'   => 'theme_home_page_title',
        'name' => __('Home Page Title', 'default'),
        'desc' => __('Used on the Home Page in the Page Title Control', 'default'),
        'type' => 'text',
    ),
	array(
		'id'   => 'theme_page_title_separator',
		'name' => __('Page Title separator', 'default'),
		'desc' => __('Used in the Page Title Control', 'default'),
		'type' => 'text',
	),
	array(
		'id'   => 'theme_use_document_title',
		'name' => __('Use Document Title as Page Title', 'default'),
		'desc' => __('Based on <a href="https://developer.wordpress.org/reference/functions/wp_get_document_title/">wp_get_document_title</a> function', 'default'),
		'type' => 'checkbox',
		'show' => 'theme_is_document_title_supported',
	),
	array(
		'name' => __('Bootstrap', 'default'),
		'type' => 'heading'
	),
	array(
		'id'   => 'theme_disable_bootstrap_scripts',
		'name' => __('Disable bootstrap.js from plugins', 'default'),
		'type' => 'checkbox',
		'desc' => __('Use this option to avoid conflicts with theme bootstrap.js<br><i><strong>Note:</strong> option won\'t work if there are <a href="https://developer.wordpress.org/reference/functions/wp_enqueue_script/">dependencies</a> on the plug-in\'s scripts</i>', 'default'),
	),
	array(
		'name' => __('Navigation Menu', 'default'),
		'type' => 'heading'
	),
	array(
		'id'   => 'theme_menu_showHome',
		'name' => __('Show home item', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_menu_homeCaption',
		'name' => __('Home item caption', 'default'),
		'type' => 'text',
		'depend' => 'theme_menu_showHome',
	),
	array(
		'id'   => 'theme_menu_highlight_active_categories',
		'name' => __('Highlight active categories', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_menu_trim_title',
		'name' => __('Trim long menu items', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_menu_trim_len',
		'name' => __('Limit each item to [N] characters', 'default'),
        'desc' =>__('(characters). ', 'default'),
		'type' => 'numeric',
		'depend' => 'theme_menu_trim_title',
	),
	array(
		'id'   => 'theme_submenu_trim_len',
		'name' => __('Limit each subitem to [N] characters', 'default'),
        'desc' =>__('(characters). ', 'default'),
		'type' => 'numeric',
		'depend' => 'theme_menu_trim_title',
	),
	array(
        'id'   => 'theme_menu_use_tag_filter',
        'name' => __('Apply menu item tag filter', 'default'),
        'desc' => __('Yes', 'default'),
        'type' => 'checkbox'
    ),
    array(
        'id'   => 'theme_menu_allowed_tags',
        'name' => __('Allowed menu item tags', 'default'),
        'type' => 'widetext',
        'depend' => 'theme_menu_use_tag_filter',
    ),
	array(
		'id'      => 'theme_menu_source',
		'name'    => __('Default menu source', 'default'),
		'type'    => 'select',
		'options' => $theme_menu_source_options,
		'desc'    => __('Displayed when Appearance > Menu > Primary menu is not set', 'default'),
	),
    array(
        'id'   => 'theme_use_default_menu',
        'name' => __('Use not stylized menu', 'default'),
        'desc' => __('Used standart <a href="http://codex.wordpress.org/Function_Reference/wp_nav_menu">wp_nav_menu</a>, when option is enabled (need for some third-party plugins).', 'default'),
        'type' => 'checkbox'
    ),
	array(
		'name' => __('Language switcher', 'default'),
		'type' => 'heading'
	),
	array(
		'id'   => 'theme_languages',
		'name' => 'Languages list',
		'type' => 'textarea',
		'desc' => 'List of locales and display names separated by space. Used in Language Control.',
		'options' => array(
			'rows' => 3,
			'placeholder' => "en_US English\nfr_FR French\nde_DE German\n",
		),
	),
	array(
		'name' => __('Posts', 'default'),
		'type' => 'heading'
	),
	array(
		'id'   => 'theme_single_navigation_trim_title',
		'name' => __('Trim long navigation links in single post view', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_single_navigation_trim_len',
		'name' => __('Limit each link to [N] characters', 'default'),
        'desc' =>__('(characters). ', 'default'),
		'type' => 'numeric',
		'depend' => 'theme_single_navigation_trim_title',
	),
	array(
		'name' => __('Featured Image', 'default'),
		'type' => 'heading'
	),
	array(
		'id'   => 'theme_metadata_use_featured_image_as_thumbnail',
		'name' => __('Use featured image as thumbnail', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_metadata_thumbnail_auto',
		'name' => __('Use auto thumbnails', 'default'),
		'desc' => __('Generate post thumbnails automatically (use the first image from the post gallery)', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_metadata_thumbnail_width',
		'name' => __('Thumbnail width', 'default'),
		'desc' => __('(px)', 'default'),
		'type' => 'numeric'
	),
	array(
		'id'   => 'theme_metadata_thumbnail_height',
		'name' => __('Thumbnail height', 'default'),
		'desc' => __('(px)', 'default'),
		'type' => 'numeric'
	),
    array(
        'name' => __('Excerpt', 'default'),
        'type' => 'heading'
    ),
    array(
        'id'   => 'theme_metadata_excerpt_auto',
        'name' => __('Use auto excerpts', 'default'),
        'desc' => __('Generate post excerpts automatically (When neither more-tag nor post excerpt is used)', 'default'),
        'type' => 'checkbox'
    ),
    array(
        'id'   => 'theme_metadata_excerpt_words',
        'name' => __('Excerpt length', 'default'),
        'desc' =>__('(words). ', 'default'),
        'type' => 'numeric',
        'depend' => 'theme_metadata_excerpt_auto'
    ),
    array(
        'id'   => 'theme_metadata_excerpt_min_remainder',
        'name' => __('Excerpt balance', 'default'),
        'desc' =>__('(words). ', 'default'),
        'type' => 'numeric',
        'depend' => 'theme_metadata_excerpt_auto'
    ),
	array(
		'id'   => 'theme_metadata_excerpt_strip_shortcodes',
		'name' => __('Remove shortcodes from excerpt', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_metadata_excerpt_use_tag_filter',
		'name' => __('Apply excerpt tag filter', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
    array(
        'id'   => 'theme_metadata_excerpt_allowed_tags',
        'name' => __('Allowed excerpt tags', 'default'),
        'type' => 'widetext',
        'depend' => 'theme_metadata_excerpt_use_tag_filter',
    ),
    array(
        'id'   => 'theme_show_morelink',
        'name' => __('Show More Link', 'default'),
        'desc' => __('Yes', 'default'),
        'type' => 'checkbox'
    ),
    array(
        'id'   => 'theme_morelink_template',
        'name' => __('More Link Template', 'default'),
        'desc' => sprintf(__('<strong>ShortTags:</strong><code>%s</code>', 'default'), '[url], [text]'),
        'type' => 'widetext',
        'depend' => 'theme_show_morelink',
    ),
    array(
        'name' => __('Shop', 'default'),
        'type' => 'heading'
    ),
    array(
        'id' => 'theme_shop_products_per_page',
        'name' => __('Number of products to show', 'default'),
        'desc' => __('Select the number of products to show on the pages. Set 0 to show all products.', 'default'),
        'type' => 'numeric'
    ),
    array(
        'id' => 'theme_products_newness_period',
        'name' => __('Product newness period', 'default'),
        'desc' => __('Select the number of days', 'default'),
        'type' => 'numeric'
    ),
	array(
		'name' => __('Pages', 'default'),
		'type' => 'heading'
	),
	array(
		'id'   => 'theme_show_random_posts_on_404_page',
		'name' => __('Show random posts on 404 page', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_show_random_posts_title_on_404_page',
		'name' => __('Title for random posts', 'default'),
		'type' => 'text',
		'depend' => 'theme_show_random_posts_on_404_page',
	),
	array(
		'id'   => 'theme_show_tags_on_404_page',
		'name' => __('Show tags on 404 page', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
	array(
		'id'   => 'theme_show_tags_title_on_404_page',
		'name' => __('Title for tags', 'default'),
		'type' => 'text',
		'depend' => 'theme_show_tags_on_404_page',
	),
	array(
		'name' => __('Comments', 'default'),
		'type' => 'heading',
	),
	array(
		'id'   => 'theme_allow_comments',
		'name' => __('Allow Comments', 'default'),
		'desc' => __('Yes', 'default'),
		'type' => 'checkbox'
	),
    array(
        'id'   => 'theme_show_comments_anywhere',
        'name' => __('Show comments anywhere', 'default'),
        'desc' => __('Yes', 'default'),
        'type' => 'checkbox',
        'depend' => 'theme_allow_comments',
    ),
	array(
		'id'   => 'theme_comment_use_smilies',
		'name' => __('Use smilies in comments', 'default'),
		'type' => 'checkbox',
        'desc' => __('Yes', 'default'),
		'depend' => 'theme_allow_comments',
	),
	array(
		'name' => __('Footer', 'default'),
		'type' => 'heading'
	),
	array(
		'id'     => 'theme_override_default_footer_content',
		'name' => __('Override default theme footer content', 'default'),
		'type' => 'checkbox',
		'desc' => __('Yes', 'default'),
	),
	array(
		'id'     => 'theme_footer_content',
		'name'   => __('Footer content', 'default'),
		'desc'   => sprintf(__('<strong>XHTML:</strong> You can use these tags: <code>%s</code>', 'default'), 'a, abbr, acronym, em, b, i, strike, strong, span') . '<br />'
		. sprintf(__('<strong>ShortTags:</strong><code>%s</code>', 'default'), '[year], [top], [rss], [login_link], [blog_title], [xhtml], [css], [rss_url], [rss_title]'),
		'type'   => 'textarea',
		'depend' => 'theme_override_default_footer_content',
	),
	array(
		'name' => __('Advertisement', 'default'),
		'type' => 'heading',
		'desc' => sprintf(__('Use the %s short code to insert these ads into posts, text widgets or footer', 'default'), '<code>[ad]</code>') . '<br/>'
		. '<span>' . __('Example:', 'default') .'</span><code>[ad code=4 align=center]</code>'
	),
	array(
		'id'   => 'theme_ad_code_1',
		'name' => sprintf(__('Ad code #%s:', 'default'), 1),
		'type' => 'textarea'
	),
	array(
		'id'   => 'theme_ad_code_2',
		'name' => sprintf(__('Ad code #%s:', 'default'), 2),
		'type' => 'textarea'
	),
	array(
		'id'   => 'theme_ad_code_3',
		'name' => sprintf(__('Ad code #%s:', 'default'), 3),
		'type' => 'textarea'
	),
	array(
		'id'   => 'theme_ad_code_4',
		'name' => sprintf(__('Ad code #%s:', 'default'), 4),
		'type' => 'textarea'
	),
	array(
		'id'   => 'theme_ad_code_5',
		'name' => sprintf(__('Ad code #%s:', 'default'), 5),
		'type' => 'textarea'
	),
));

function theme_page_meta_options($options) {
    return array_merge(
        $options,
        array(
            array(
                'id' => 'theme_show_page_title',
                'name' => __('Show Title on Page', 'default'),
                'desc' => __('Yes', 'default'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'theme_show_in_menu',
                'name' => __('Show in Menu', 'default'),
                'desc' => __('Yes', 'default'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'theme_show_as_separator',
                'name' => __('Show as Separator in Menu', 'default'),
                'desc' => __('Yes', 'default'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'theme_title_in_menu',
                'name' => __('Title in Menu', 'default'),
                'type' => 'widetext'
            ),
            array(
                'id' => 'theme_show_categories',
                'name' => __('Show Custom Categories', 'default'),
                'desc' => __('Yes', 'default'),
                'type' => 'checkbox'
            ),
            array(
                'id' => 'theme_categories',
                'name' => __('Comma separated list of Category slugs', 'default'),
                'type' => 'widetext',
                'desc' => __('Keep empty to show all posts.', 'default'),
                'depend' => 'theme_show_categories',
            ),
            array(
                'id' => 'theme_use_wpautop',
                'name' => __('Automatically add paragraphs', 'default'),
                'desc' => __('Yes', 'default'),
                'type' => 'checkbox'
            ),
        )
    );
}
add_filter('theme_page_meta_options', 'theme_page_meta_options');

function theme_post_meta_options($options) {
    return array_merge(
        $options,
        array(
            array(
                'id' => 'theme_use_wpautop',
                'name' => __('Automatically add paragraphs', 'default'),
                'desc' => __('Yes', 'default'),
                'type' => 'checkbox'
            ),
        )
    );
}
add_filter('theme_post_meta_options', 'theme_post_meta_options');

global $theme_widget_meta_options, $theme_widgets_style;
$theme_widget_meta_options = array(
	array(
		'id'      => 'theme_widget_show_on',
		'name'    => __('Show widget on:', 'default'),
		'type'    => 'select',
		'options' => $theme_widget_show_on
	),
	array(
		'id'   => 'theme_widget_front_page',
		'name' => '',
		'type' => 'checkbox',
		'desc' => __('Front page', 'default'),
		'depend' => 'theme_widget_show_on:selected,none_selected',
	),
	array(
		'id'   => 'theme_widget_single_post',
		'name' => '',
		'type' => 'checkbox',
		'desc' => __('Single posts', 'default'),
		'depend' => 'theme_widget_show_on:selected,none_selected',
	),
	array(
		'id'   => 'theme_widget_single_page',
		'name' => '',
		'type' => 'checkbox',
		'desc' => __('Single pages', 'default'),
		'depend' => 'theme_widget_show_on:selected,none_selected',
	),
	array(
		'id'   => 'theme_widget_posts_page',
		'name' => '',
		'type' => 'checkbox',
		'desc' => __('Posts page', 'default'),
		'depend' => 'theme_widget_show_on:selected,none_selected',
	),
	array(
		'id'   => 'theme_widget_page_ids',
		'name' => '',
		'type' => 'checkbox',
		'desc' => __('Page IDs (comma separated)', 'default'),
		'depend' => 'theme_widget_show_on:selected,none_selected',
	),
	array(
		'id'   => 'theme_widget_page_ids_list',
		'name' => '',
		'type' => 'text',
		'desc' => '',
		'depend' => 'theme_widget_page_ids;theme_widget_show_on:selected,none_selected',
	),
);

function theme_is_document_title_supported() {
    return function_exists('wp_get_document_title');
}