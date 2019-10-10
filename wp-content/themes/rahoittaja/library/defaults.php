<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$template = get_option('stylesheet');

global $theme_default_options;
$theme_default_options = array(

	'theme_header_clickable' => 0,
	'theme_header_link' => trailingslashit(  home_url() ),

    'theme_logo_url' => '',
    'theme_logo_link' => '',

	'theme_disable_bootstrap_scripts' => 1,

	'theme_languages' => "en_US English\nfr_FR French\nde_DE German\n",

    'theme_use_default_menu' => 0,
	'theme_menu_showHome' => 1,
	'theme_menu_highlight_active_categories' => 1,
	'theme_menu_homeCaption' => 'home',
	
	'theme_menu_trim_title' => 1,
	'theme_menu_trim_len' => 45,
	'theme_submenu_trim_len' => 40,
    'theme_menu_use_tag_filter' => 1,
    'theme_menu_allowed_tags' => 'span, img',

	'theme_menu_depth' => 0,
	'theme_menu_source' => 'Pages',
	
	'theme_vmenu_depth' => 0,

	'theme_metadata_use_featured_image_as_thumbnail' => 1,
	'theme_metadata_thumbnail_auto' => 0,
	'theme_metadata_thumbnail_width' => 128,
	'theme_metadata_thumbnail_height' => 128,

	'theme_shop_products_per_page' => 8,
	'theme_products_newness_period' => 7,

	'theme_metadata_separator' => ' | ',
	'theme_metadata_excerpt_auto' => 1,
	'theme_metadata_excerpt_min_remainder' => 5,
	'theme_metadata_excerpt_words' => 40,

    'theme_show_morelink' => 1,
    'theme_morelink_template' => '<a class="more-link" href="[url]">[text]</a>',

	'theme_show_tags_on_404_page' => 0,
	'theme_show_tags_title_on_404_page' => __('Tag Cloud', 'default'),
	'theme_show_random_posts_on_404_page' => 0,
	'theme_show_random_posts_title_on_404_page' => __('Random posts', 'default'),
	'theme_comment_use_smilies' => 0,
	'theme_allow_comments' => 1,

	'theme_metadata_excerpt_strip_shortcodes' => 0,
	'theme_metadata_excerpt_use_tag_filter' => 0,
	'theme_metadata_excerpt_allowed_tags' => 'a, abbr, blockquote, b, cite, pre, code, em, label, i, p, strong, ul, ol, li, h1, h2, h3, h4, h5, h6, object, param, embed',
	'theme_single_navigation_trim_title' => 1,
	'theme_single_navigation_trim_len' => 80,
	'theme_attachment_size' => 600,
	'theme_override_default_footer_content' => 0,

    'theme_template_'.$template.'_404' => 'template404',
    'theme_template_'.$template.'_blog' => 'blogTemplate',
	'theme_template_'.$template.'_home' => 'home',
	'theme_template_'.$template.'_products' => 'products',
	'theme_template_'.$template.'_product-overview' => 'productOverview',
	'theme_template_'.$template.'_shopping-cart' => 'shoppingCartTemplate',

    'theme_search_mode' => 'all',
);

global $theme_default_meta_options;
$theme_default_meta_options = array(
    'theme_use_wpautop' => 1,
	'theme_show_in_menu' => 1,
	'theme_show_as_separator' => 0,
	'theme_title_in_menu' => '',
	'theme_show_page_title' => 1,
	'theme_show_categories' => 0,
	'theme_categories' => '',
	'theme_widget_show_on' => 'all',
	'theme_widget_front_page' => 0,
	'theme_widget_single_post' => 0,
	'theme_widget_single_page' => 0,
	'theme_widget_posts_page' => 0,
	'theme_widget_page_ids' => 0,
	'theme_widget_page_ids_list' => '',
	'theme_widget_styling' => '',

	'theme_allow_megamenu' => '', // Auto
	'theme_megamenu_width' => '',
	'theme_megamenu_custom_width' => '',
	'theme_megamenu_columns_lg' => '',
	'theme_megamenu_columns_md' => '',
	'theme_megamenu_columns_sm' => '',
	'theme_megamenu_columns_xs' => '',
);