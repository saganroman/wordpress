<?php
/**
 *
 * shortcodes.php
 *
 * Used to add custom shortcodes.
 *
 * To add custom shortcode please use the following code:
 * add_shortcode("my_shortcode_name", "my_shortcode_func");
 * function my_shortcode_func($atts) { // your code here... }
 *
 * More detailed information about shortcodes: http://codex.wordpress.org/Shortcode_API
 *
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/shortcodes-helper.php');
require_once(dirname(__FILE__) . '/shortcodes-effects.php');
require_once(dirname(__FILE__) . '/shortcodes-styles.php');

function themler_init_filters() {
    if (false !== has_filter('theme_the_content', 'theme_column_filter')) {
        // another themler theme active
        return;
    }

    add_filter('widget_text', 'do_shortcode', 11); // Allow [SHORTCODES] in Widgets

    add_filter('the_content', 'ShortcodesUtility::convertBadContent', 0);
    add_filter('widget_text', 'ShortcodesUtility::convertBadContent', 0);
    add_filter('theme_the_content', 'ShortcodesUtility::convertBadContent', 0);

    // collect such shortcodes as [row_1], [column_1], ect
    add_filter('the_content', 'ShortcodesUtility::collectShortcodes', 0);
    add_filter('theme_the_content', 'ShortcodesUtility::collectShortcodes', 0);
    add_filter('widget_text', 'ShortcodesUtility::collectShortcodes', 0);

    // convert old columns syntax to new
    themler_add_converter_filters('themler_column_filter');

    // [row] => [columns]
    themler_add_converter_filters('themler_old_row_filter');

    // [box_absolute] => [layoutbox]
    themler_add_converter_filters('themler_old_box_absolute_filter');

    // remove [align_content]
    themler_add_converter_filters('themler_old_align_content_filter');

    // remove [container_inner_effect] => [fluid]
    themler_add_converter_filters('themler_old_container_inner_effect_filter');

    themler_add_converter_filters('themler_old_section_filter');

    // replace themler shortcodes
    add_filter('the_content', 'ShortcodesUtility::beforeTheContent', 0);
    add_filter('widget_text', 'ShortcodesUtility::beforeTheContent', 0);
    add_filter('the_content', 'ShortcodesUtility::afterTheContent', 100000);
    add_filter('widget_text', 'ShortcodesUtility::afterTheContent', 100000);
}
add_action('init', 'themler_init_filters');

function themler_add_converter_filters($filter) {
    add_filter('the_content',           $filter, 0);
    add_filter('theme_the_content',     $filter, 0);
    add_filter('widget_text',           $filter, 0);
    add_filter('themler_column_filter', $filter, 0);
}

require_once(dirname(__FILE__) . '/deprecated-shortcodes.php');

function themler_shortcodes_icon_state_style($id, $args) {
    $picture     = empty($args['picture'])     ? ''       : $args['picture'];
    $icon        = empty($args['icon'])        ? ''       : $args['icon'];
    $align       = empty($args['align'])       ? 'before' : $args['align'];
    $selector    = empty($args['selector'])    ? ''       : $args['selector'];
    $atts        = empty($args['atts'])        ? array()  : $args['atts'];
    $icon_prefix = empty($args['icon_prefix']) ? ''       : $args['icon_prefix'];
    
    list($main_styles, ) = ShortcodesEffects::css($id, $atts, $icon_prefix, "{selector}:$align", substr($selector, 1));
    $result = '';

    if ($picture) {
        if (!preg_match('#^(http|\/\/|data:)#', $picture)) {
            $picture = apply_filters('theme_image_path', $picture);
        }
        $result .= "$selector:$align {
            content: url($picture);
            font-size: 0 !important;
            line-height: 0 !important;
        }";
    } else {
        if ($icon === 'none') {
            $result .= "$selector:$align {visibility: hidden;}";
        } else {
            $result .=
                "$selector {
                    text-decoration: inherit;
	                display: inline-block;
	                speak: none;
	            }

                $selector:$align {
                    font-family: 'Billion Web Font';
	                font-style: normal;
	                font-weight: normal;
	                text-decoration: inherit;
	                text-align: center;
	                text-transform: none;
	                width: 1em;
                }";

            $result .= str_replace('{selector}:before', "$selector:$align", ShortcodesEffects::getIconStyle($icon));
            $result .= "$selector:before {width: auto; visibility: inherit;}";
        }
    }
    $result .= "$selector:$align{display: inline-block;}";
    $result = '<style>' . $result . '</style>' . $main_styles;

    $font_size = ShortcodesEffects::css_prop($atts, 'font-size', '', $icon_prefix);
    $line_height_factor = ShortcodesEffects::css_prop($atts, 'line-height-factor', '', $icon_prefix);
    if (!$picture && $icon && $icon !== 'none' && $font_size && !$line_height_factor) {
        $result .= "<style>$selector:$align{line-height: $font_size;}</style>";
    }

    $result .= "<style>$selector:$align {
        vertical-align: middle;
        text-decoration: none;
    }</style>";

    return $result;
}

function themler_page_title_separator_filter($sep) {
    $sep = get_option('theme_page_title_separator');
    if (false === $sep) {
        $sep = ' ';
    }
    return $sep;
}

function themler_document_title_filter($parts) {
    unset($parts['tagline'], $parts['site']);
    return $parts;
}

function themler_get_page_title() {
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

function themler_get_page_url() {
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return apply_filters('themler_remove_preview_args', $url);
}

// anchor
function themler_shortcodes_anchor($atts) {
    $atts = shortcode_atts(array(
        'name' => ''
    ), $atts);

    return '<div class="bd-anchor" id="' . ShortcodesUtility::escape($atts['name']) . '"></div>';
}
add_shortcode('anchor', 'themler_shortcodes_anchor');

function themler_shortcodes_subscribe_rss() {
    return '<a class="button rss-subscribe" href="' . get_bloginfo('rss2_url') . '" title="' . __('RSS Feeds', 'default') . '">' . __('RSS Feeds', 'default') . '</a>';
}
add_shortcode('rss', 'themler_shortcodes_subscribe_rss');

// ads
function themler_shortcode_advertisement($atts) {
    $atts = shortcode_atts(array(
        'code' => 1,
        'align' => 'left',
        'inline' => 0
    ), $atts);

    $ad = get_option('theme_ad_code_' . $atts['code']);
    if (!empty($ad)):
        $ad = '<div class="ad align' . ShortcodesUtility::escape($atts['align']) . '">' . $ad . '</div>';
        if (!$atts['inline'])
            $ad .= '<div class="cleared"></div>';
        return $ad;
    else:
        return '<p class="error"><strong>[ad]</strong> ' . sprintf(__("Empty ad slot (#%s)!", 'default'), ShortcodesUtility::escape($atts['code'])) . '</p>';
    endif;
}
add_shortcode('ad', 'themler_shortcode_advertisement');

function themler_shortcode_go_to_top() {
    return sprintf('<a class="button" href="#">' . __('Top', 'default') . '</a>');
}
add_shortcode('top', 'themler_shortcode_go_to_top');
// login
function themler_shortcode_login_link() {
    if (is_user_logged_in())
        return sprintf('<a class="login-link" href="%1$s">%2$s</a>', admin_url(), __('Site Admin', 'default'));
    else
        return sprintf('<a class="login-link" href="%1$s">%2$s</a>', wp_login_url(), __('Log in', 'default'));
}
add_shortcode('login_link', 'themler_shortcode_login_link');
// blog title
function themler_shortcode_blog_title() {
    return '<span class="blog-title">' . get_bloginfo('name') . '</span>';
}
add_shortcode('blog_title', 'themler_shortcode_blog_title');
// validate xhtml
function themler_shortcode_validate_xhtml() {
    return '<a class="button valid-xhtml" href="http://validator.w3.org/check?uri=referer" title="Valid XHTML">XHTML 1.1</a>';
}
add_shortcode('xhtml', 'themler_shortcode_validate_xhtml');
// validate css
function themler_shortcode_validate_css() {
    return '<a class="button valid-css" href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3" title="Valid CSS">CSS 3.0</a>';
}
add_shortcode('css', 'themler_shortcode_validate_css');
// current year
function themler_shortcode_current_year() {
    return date('Y');
}
add_shortcode('year', 'themler_shortcode_current_year');

function themler_shortcode_rss_url() {
    return get_bloginfo('rss2_url', 'raw');
}
add_shortcode('rss_url', 'themler_shortcode_rss_url');

function themler_shortcode_rss_title() {
    return sprintf(__('%s RSS Feed', 'default'), get_bloginfo('name'));
}
add_shortcode('rss_title', 'themler_shortcode_rss_title');

function themler_shortcode_template_url() {
    return get_bloginfo('template_url', 'display');
}
add_shortcode('template_url', 'themler_shortcode_template_url');

function themler_shortcode_post_link($atts) {
    $atts = shortcode_atts(array(
        'name' => '/',
    ), $atts);

    $raw_name = $atts['name'];
    $type = 'page';
    if(strpos($atts['name'], '/Blog%20Posts/') === 0) {
        $name = substr($atts['name'], strlen('/Blog%20Posts/'));
        $type = 'post';
    }
    $target = get_page_by_path($name, OBJECT, $type);
    if(null !== $target) {
        return get_permalink($target->ID);
    } else {
        return $raw_name;
    }
}
add_shortcode('post_link', 'themler_shortcode_post_link');

function themler_shortcode_search() {
    ob_start();
    get_search_form();
    return ob_get_clean();
}
add_shortcode('search', 'themler_shortcode_search');
?>
<?php

// Affix
function themler_shortcode_affix($atts, $content='', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'offset' => '',
        'fixatscreen' => 'top',
        'clipatcontrol' => 'top',
        'enable_lg' => 'yes',
        'enable_md' => 'yes',
        'enable_sm' => 'yes',
        'enable_xs' => 'no',
    ), $atts);

    $enable_lg = ShortcodesUtility::getBool($atts['enable_lg']);
    $enable_md = ShortcodesUtility::getBool($atts['enable_md']);
    $enable_sm = ShortcodesUtility::getBool($atts['enable_sm']);
    $enable_xs = ShortcodesUtility::getBool($atts['enable_xs']);
    $offset = ShortcodesUtility::escape($atts['offset']);
    $fixatscreen = ShortcodesUtility::escape($atts['fixatscreen']);
    $clipatcontrol = ShortcodesUtility::escape($atts['clipatcontrol']);

    $id = ShortcodesEffects::init('bd-affix', $atts, ShortcodesEffects::HTML_EFFECT);

    $data_attrs = "data-affix data-offset='$offset' data-fix-at-screen='$fixatscreen' data-clip-at-control='$clipatcontrol'" .
        ($enable_lg ? ' data-enable-lg' : '') .
        ($enable_md ? ' data-enable-md' : '') .
        ($enable_sm ? ' data-enable-sm' : '') .
        ($enable_xs ? ' data-enable-xs' : '')
        ;

    $content = ShortcodesUtility::doShortcode($content);
    list(, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts, '', '.affix{selector}');

    $class = substr($selector, 1);
    list($style_tag_transition,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'transition'), '', '{selector}', $class);
    list($style_tag_other,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, '!transition,!left,!right,!top,!width'), '', '.affix{selector}', $class);
    list($style_tag_arrange,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'left,right,top,width'), '', '.affix{selector}', $class);
    $style_tag_arrange = str_replace(';', ' !important;', $style_tag_arrange);

    return "<!--[$tag]-->" . $style_tag_other . $style_tag_arrange . $style_tag_transition .
                '<div ' . $data_attrs . ' class="bd-no-margins bd-margins ' . $additional_classes . '">' .
                    '<!--{content}-->' .
                        $content .
                    '<!--{/content}-->' .
                '</div>' .
            "<!--[/$tag]-->";
}
ShortcodesUtility::addEffectShortcode('affix', 'themler_shortcode_affix');
?>
<?php

// BoxAlign
function themler_shortcode_box_align($atts, $content = '', $tag) {
    $atts = ShortcodesUtility::atts(array(
        'type' => 'center'
    ), $atts);

    $type = ShortcodesUtility::escape($atts['type']);

    $id = ShortcodesEffects::init('bd-boxalign', $atts, true);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_css, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts, '', '{selector}');

    $alignCss = array(
        'text-align: ' . ($type ? $type : 'left') . ' !important;'
    );

    $alignChildrenCss = array(
        'display: inline-block !important;',
        'text-align: left !important;'
    );

    ob_start();
    ?>

    <!--[<?php echo $tag ?>]-->
    <?php echo $style_css ?>
    <style>
        <?php if ($type): ?>
        <?php echo $selector ?>
        {
                <?php echo join("\n", $alignCss) ?>
        }
        <?php endif ?>

        <?php echo $selector ?> > *
        {
            <?php echo join("\n", $alignChildrenCss) ?>
        }
    </style>
    <div class="<?php echo $additional_classes ?>">
        <!--{content}-->
            <?php echo $content ?>
        <!--{/content}-->
    </div>
    <!--[/<?php echo $tag ?>]-->

    <?php
    return ob_get_clean();
}

ShortcodesUtility::addEffectShortcode('box_align', 'themler_shortcode_box_align');
?>
<?php

// Animation
function themler_shortcode_animation($atts, $content = '', $tag) {
    $atts = ShortcodesUtility::atts(array(
        'name'           => 'bounce',
        'infinited'      => 'false',
        'event'          => 'hover',
        'duration'       => '1000ms',
        'delay'          => '0ms',
        '_display'       => ''
    ), $atts);

    $name = ShortcodesUtility::escape($atts['name']);
    $infinited = ShortcodesUtility::escape($atts['infinited']);
    $event = ShortcodesUtility::escape($atts['event']);
    $duration = ShortcodesUtility::escape($atts['duration']);
    $delay = ShortcodesUtility::escape($atts['delay']);
    $_display = ShortcodesUtility::escape($atts['_display']);

    $id = ShortcodesEffects::init('bd-animation', $atts, ShortcodesEffects::CSS_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);
    $targetControl = ShortcodesEffects::target_control($id);
    list($style_tag, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts);
    $content = ShortcodesEffects::addClassesAndAttrs(
        $content,
        $targetControl,
        array('animated', $additional_classes),
        array(
            'data-animation-name' => $name,
            'data-animation-event' => $event,
            'data-animation-duration' => $duration,
            'data-animation-delay' => $delay,
            'data-animation-infinited' => $infinited
        )
    );

    $infinited_css = $infinited === 'true' ?
        "-webkit-animation-iteration-count: infinite;\n" .
        "animation-iteration-count: infinite;"
        : '';
    $style_tag .= "<style>
        $selector.animated.$name {
            -webkit-animation-duration: $duration;
            animation-duration: $duration;
            -webkit-animation-delay: $delay;
            animation-delay: $delay;
            $infinited_css
        }
        </style>";

    ob_start();
?>
    <!--[<?php echo $tag ?>]-->
        <?php echo $style_tag ?>
        <!--{content}-->
            <?php echo $content ?>
        <!--{/content}-->
    <!--[/<?php echo $tag ?>]-->

    <?php
    return ob_get_clean();
}

ShortcodesUtility::addEffectShortcode('animation', 'themler_shortcode_animation');
?>
<?php

// BackgroundWidth
function themler_shortcode_background_width($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(), $atts);

    ShortcodesEffects::init('bd-background-width', $atts, ShortcodesEffects::CSS_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);

    return "<!--[$tag]--><!--{content}-->" . $content . "<!--{/content}--><!--[/$tag]-->";
}

ShortcodesUtility::addEffectShortcode('background_width', 'themler_shortcode_background_width');
?>
<?php

// Balloon
function themler_shortcode_balloon($atts, $content = '', $tag) {
    $atts = ShortcodesUtility::atts(array(
        'align' => 'bottom',
        'size' => '20px',
        'position' => '50%'
    ), $atts);

    $align = $atts['align'];
    $size = $atts['size'];
    $position = $atts['position'];

    $id = ShortcodesEffects::init('bd-balloon', $atts, ShortcodesEffects::HTML_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts);

    if ($align !== 'top' && $align !== 'right' && $align !== 'left' && $align !== 'bottom')
        $align = 'bottom';

    $inverse = array(
        'left' => 'right',
        'right' => 'left',
        'top' => 'bottom',
        'bottom' => 'top'
    );

    $_target = ShortcodesEffects::target_control($id);
    $_controlBorderWidth = ShortcodesEffects::css_prop($_target['css'], "border-$align-width");
    $_controlBorderColor = ShortcodesEffects::css_prop($_target['css'], "border-$align-color");
    $_controlBorderStyle = ShortcodesEffects::css_prop($_target['css'], "border-$align-style");

    $borderWidth = intval($_controlBorderWidth);
    $baseArrowSize = $_controlBorderWidth !== '' && $borderWidth ? ($borderWidth * 1.5) + intval($size) : 0;

    $_t = "$selector {position: relative;}";

    $_background_color = ShortcodesEffects::css_prop($_target['css'], "background-color");

    if ($_background_color === '') {
        $_t .= "$selector {background-color: #ddd;}";
        $_t .= "$selector:after {border-" . $inverse[$align] . "-color: #ddd !important;}";
    }

    $_t .= "
        $selector {
            position: relative;
        }
        $selector:after,
        $selector:before {
            border-color: transparent;
            border-style: solid;
            content: \" \";
            height: 0;
            position: absolute;
            pointer-events: none;
            width: 0;
            ".$inverse[$align] . " 100%;
        }";


    if ($_controlBorderStyle)
        $_t .= "$selector:before {border-style: $_controlBorderStyle;}";
    else
        $_t .= "$selector:before {border-style: solid;}";


    $_t .= "$selector:after {";
    $_t .= 'border-color: transparent;';

    if ($_background_color) {
        $_t .= "border-".$inverse[$align]."-color: $_background_color;";
    }

    $_t .= "border-width: $size;";

    if ($align === 'top' || $align === 'bottom') {
        $_t .= "left: $position;";
        $_t .= "margin-left: -$size;";
    }

    if ($align === 'right' || $align === 'left') {
        $_t .= "margin-top: -$size;";
        $_t .= "top: $position;";
    }
    $_t .= '}';

    if ($baseArrowSize > 0) {
        $_t .= "$selector:before {
            border-".$inverse[$align]."-color: $_controlBorderColor;
            border-width: {$baseArrowSize}px;
            ";

        if ($align === 'top' || $align === 'bottom') {
            $_t .= "left: $position;";
            $_t .= "margin-left: -{$baseArrowSize}px;";
        }
        if ($align === 'right' || $align === 'left') {
            $_t .= "margin-top: -{$baseArrowSize}px;";
            $_t .= "top: $position;";
        }
        $_t .= '}';
    }

    ob_start();
?>

    <!--[<?php echo $tag ?>]-->
        <style><?php echo $_t; ?></style>
        <?php echo $style_tag ?>
        <div class="<?php echo $additional_classes ?>">
            <!--{content}-->
                <?php echo $content ?>
            <!--{/content}-->
        </div>
    <!--[/<?php echo $tag ?>]-->

<?php
    return ob_get_clean();
}

ShortcodesUtility::addEffectShortcode('balloon', 'themler_shortcode_balloon');
?>
<?php

// BootstrapProgressbars
function themler_shortcode_progress($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'complete' => '50%',
        'show_label' => false,
        'striped' => false,
        'animated' => false
    ), $atts);

    $id = ShortcodesEffects::init('progress', $atts);
    list(, $additional_class, $selector) = ShortcodesEffects::css($id, $atts);
    $class = substr($selector, 1);

    list($tag1,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'positioning,size'), '', '{selector}', $class);
    list($tag2,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'background,color'), '', '{selector} .progress-bar', $class);

    $_animation = ($atts['striped'] ? 'progress-striped' : '') . ($atts['striped'] && $atts['animated'] ? ' active' : '');

    return  "<!--[$tag]-->" . $tag1 . $tag2 .
                '<div class="progress ' . $_animation . ' ' . $additional_class . '">' .
                    '<div class="progress-bar" role="progressbar" style="width: ' . $atts['complete'] . ';">' .
                        '<!--{content}-->' .
                            ($atts['show_label'] ? $atts['complete'] : '') .
                        '<!--{/content}-->' .
                    '</div>' .
                '</div>' .
            "<!--[/$tag]-->";
}

ShortcodesUtility::addShortcode('progress', 'themler_shortcode_progress');
?>
<?php

// LayoutBox
function themler_shortcode_layoutbox($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(), $atts);

    $id = ShortcodesEffects::init('', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts);

    return "<!--[$tag]-->" . $style_tag .
                '<div class="clearfix ' . $additional_class . ' bd-no-margins">' .
                    '<div class="bd-container-inner">' .
                        '<!--{content}-->' .
                            $content .
                        '<!--{/content}-->' .
                    '</div>' .
                '</div>' .
            "<!--[/$tag]-->";
}

ShortcodesUtility::addShortcode('layoutbox', 'themler_shortcode_layoutbox');
?>
<?php

// LinkButton
function themler_shortcode_button($atts, $content = '', $tag = '', $parent = array()) {
    if ($parent) {
        $id = $parent['id'];
        $prefix = $parent['prefix'];
    } else {
        $atts = ShortcodesUtility::atts(array(
            'link' => '',
            'href' => '',
            'type' => 'default',
            'style' => '',
            'size' => '',
            'rel' => '',
            'title' => '',
            'screen_tip' => '',
            'target' => '',

            'icon' => '',
            'picture' => '',
            'align' => 'before',
            'icon_hovered' => '',
            'picture_hovered' => '',
            'align_hovered' => 'before',
        ), $atts, array('', 'icon_', 'icon_hovered_'));

        $id = ShortcodesEffects::init('', $atts);
        $prefix = '';
    }

    $size = strtolower($atts[$prefix . 'size']);
    $style = $atts[$prefix . 'style'];
    $type = $atts[$prefix . 'type'];
    $link = ShortcodesUtility::escape($atts['link']);
    if (!$link) {
        $link = ShortcodesUtility::escape($atts['href']);
    }
    $rel = ShortcodesUtility::escape($atts[$prefix . 'rel']);
    $title = ShortcodesUtility::escape($atts['title']);
    if (!$title) {
        $title = ShortcodesUtility::escape($atts['screen_tip']);
    }
    $target = ShortcodesUtility::escape($atts['target']);

    $icon_passive = ShortcodesUtility::escape($atts[$prefix . 'icon']);
    $picture_passive = ShortcodesUtility::escape($atts[$prefix . 'picture']);
    $align_passive = ShortcodesUtility::escape($atts[$prefix . 'align']);
    $icon_hovered = ShortcodesUtility::escape($atts[$prefix . 'icon_hovered']);
    $picture_hovered = ShortcodesUtility::escape($atts[$prefix . 'picture_hovered']);
    $align_hovered = ShortcodesUtility::escape($atts[$prefix . 'align_hovered']);

    $link_content = $content;
    $sizes = array('large' => 'btn-lg', 'small' => 'btn-sm', 'xsmall' => 'btn-xs');

    $classes = array();

    if ($type === 'bootstrap') {
        $classes[] = 'btn';
        $classes[] = ShortcodesStyles::getStyleClassname('Button', $style ? $style : 'default');

        if (array_key_exists($size, $sizes)) {
            $classes[] = $sizes[$size];
        }
    } else {
        $classes[] = ShortcodesStyles::getStyleClassname('Button', $style);
    }

    list($style_tag, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts, $prefix);
    $classes[] = $additional_classes;

    if ($icon_passive && $icon_passive !== 'none' || $picture_passive ||
        $icon_hovered && $icon_hovered !== 'none' || $picture_hovered) {

        $classes[] = 'bd-icon';
    }

    $style_tag .= themler_shortcodes_icon_state_style($id, array(
        'picture' => $picture_passive,
        'icon' => $icon_passive,
        'align' => $align_passive,
        'selector' => $selector,
        'atts' => $atts,
        'icon_prefix' => $prefix . 'icon_'
    ));
    $style_tag .= themler_shortcodes_icon_state_style($id, array(
        'picture' => $picture_hovered,
        'icon' => $icon_hovered,
        'align' => $align_hovered,
        'selector' => "$selector:hover",
        'atts' => $atts,
        'icon_prefix' => $prefix . 'icon_hovered_'
    ));

    $html_atts = array();
    if ($rel) {
        $html_atts[] = 'rel="' . $rel . '"';
    }
    if ($title) {
        $html_atts[] = 'title="' . $title . '"';
    }
    if ($target) {
        $html_atts[] = 'target="' . $target . '"';
    }
    $html_atts[] = 'href="' . $link . '"';
    $html_atts[] = 'class="' . implode(' ', $classes) . ' bd-own-margins bd-content-element"';
    $content = "<a " . implode(' ', $html_atts) . "><!--{content}-->\n$link_content\n<!--{/content}--></a>";

    return $parent ? array('html' => $content, 'css' => $style_tag) :
        '<!--[button]-->' .
            $style_tag .
            $content .
        '<!--[/button]-->';
}
ShortcodesUtility::addShortcode('button', 'themler_shortcode_button');
?>
<?php

// ContainerEffect
function themler_shortcode_container_effect($atts, $content, $tag) {
    $atts = ShortcodesUtility::atts(array(), $atts);

    $id = ShortcodesEffects::init('bd-containereffect', $atts, ShortcodesEffects::HTML_EFFECT);

    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_classes) = ShortcodesEffects::css($id, $atts, '', '{selector}');

    return "<!--[$tag]--> $style_tag" .
            '<div class="container-effect container ' . $additional_classes . '">' .
                '<!--{content}-->' .
                    $content .
                '<!--{/content}-->' .
            '</div>' .
        "<!--[/$tag]-->";
}

ShortcodesUtility::addEffectShortcode('container_effect', 'themler_shortcode_container_effect');
?>
<?php

// ContainerInnerEffect
function themler_shortcode_fluid($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(), $atts);

    ShortcodesEffects::init('bd-page-width', $atts, ShortcodesEffects::CSS_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);

    return "<!--[$tag]--><!--{content}-->" . $content . "<!--{/content}--><!--[/$tag]-->";
}

ShortcodesUtility::addEffectShortcode('fluid', 'themler_shortcode_fluid');
?>
<?php

// CustomHtml
function themler_shortcode_html($atts, $content = '') {
    $atts = ShortcodesUtility::atts(ShortcodesEffects::tagsStylesAtts(array(
        'css_additional' => ''
    )), $atts);

    $id = ShortcodesEffects::init('', $atts);
    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts);

    $content = ShortcodesUtility::doShortcode($content);
    return  '<!--[html]-->' .
                $style_tag .
                '<style>' . $atts['css_additional'] . '</style>' .
                '<div class="bd-tagstyles ' . $additional_class . '">' .
                    '<div class="bd-container-inner bd-content-element">' .
                        '<!--{content}-->' .
                            $content .
                        '<!--{/content}-->' .
                    '</div>' .
                '</div>' .
            '<!--[/html]-->';
}

ShortcodesUtility::addShortcode('html', 'themler_shortcode_html');
?>
<?php

// FlexAlign
function themler_shortcode_flex_align($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(), $atts);

    $id = ShortcodesEffects::init('bd-flexalign', $atts, ShortcodesEffects::HTML_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);

    list($style_tag, $additional_classes, $selector) = ShortcodesEffects::css(
        $id,
        ShortcodesEffects::filter($atts, 'margin'),
        '',
        '.bd-flexalign{selector} > *'
    );
    $style_tag = "<style>$selector{height: 100%;}</style>" . $style_tag;

    return "<!--[$tag]-->$style_tag<div class=\"bd-flexalign $additional_classes\"><!--{content}-->" . $content . "<!--{/content}--></div><!--[/$tag]-->";
}

ShortcodesUtility::addEffectShortcode('flex_align', 'themler_shortcode_flex_align');
?>
<?php

// FlexColumn
function themler_shortcode_flex_column($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'direction' => 'column',
        'responsive' => 'xs'
    ), $atts);

    $direction = ShortcodesUtility::escape($atts['direction']);
    $responsive = ShortcodesUtility::escape($atts['responsive']);

    $id = ShortcodesEffects::init('bd-flex-column', $atts, ShortcodesEffects::CSS_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);
    ShortcodesEffects::css($id, $atts);

    $displayFlex = implode("\n", array(
        'display: -webkit-box;',
        'display: -webkit-flex;',
        'display: -ms-flexbox;',
        'display: flex;'
    ));

    $flexBasis0 = implode("\n", array(
        '-webkit-flex-basis: 0;',
        '-ms-flex-preferred-size: 0;',
        'flex-basis: 0;'
    ));

    $flexGrow1 = implode("\n", array(
        '-webkit-box-flex: 1;',
        '-webkit-flex-grow: 1;',
        '-ms-flex-positive: 1;',
        'flex-grow: 1;'
    ));

    $flexDirectionColumn = implode("\n", array(
        '-webkit-box-orient: vertical;',
        '-webkit-box-direction: normal;',
        '-webkit-flex-direction: column;',
        '-ms-flex-direction: column;',
        'flex-direction: column;'
    ));

    $flexWrap = implode("\n", array(
        '-webkit-flex-wrap: wrap;',
        '-ms-flex-wrap: wrap;',
        'flex-wrap: wrap;'
    ));

    $targetControl = ShortcodesEffects::target_control($id);

    $mediaStart = '';
    $mediaEnd = '';

    if ($responsive && $responsive !== 'none' && $responsive !== 'lg') {
        list($mediaStart, $mediaEnd) = ShortcodesEffects::$responsive_rules_min['_' . $responsive];
    }

    ob_start();
?>
    <!--[<?php echo $tag ?>]-->
        <style>
            <?php echo $targetControl['selector'] ?> {
                <?php echo $displayFlex ?>
                <?php echo $flexDirectionColumn ?>
            }

            <?php echo $targetControl['selector'] ?> > .bd-vertical-align-wrapper
            {
                <?php echo $displayFlex ?>
                <?php echo $flexWrap ?>

                <?php if ($direction === 'column'): ?>
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                <?php endif ?>
                -webkit-flex-direction: <?php echo $direction ?>;
                -ms-flex-direction: <?php echo $direction ?>;
                flex-direction: <?php echo $direction ?>;

                width: 100%;
            }

            <?php echo $targetControl['selector'] ?> > .bd-vertical-align-wrapper > *
            {
                <?php echo $flexGrow1 ?>
            }

            <?php echo $mediaStart ?>
                <?php echo $targetControl['selector'] ?> > .bd-vertical-align-wrapper > *
                {
                    <?php echo $flexBasis0 ?>
                }
            <?php echo $mediaEnd ?>
        </style>
        <!--{content}-->
            <?php echo $content ?>
        <!--{/content}-->
    <!--[/<?php echo $tag ?>]-->
<?php
    return ob_get_clean();
}
ShortcodesUtility::addEffectShortcode('flex_column', 'themler_shortcode_flex_column');
?>
<?php

// GoogleMap
function themler_shortcode_googlemap($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'image_style' => '',
        'address' => '',
        'src' => '//maps.google.com/maps?output=embed',
        'zoom' => '',
        'map_type' => '',
        'language' => ''
    ), $atts);

    $id = ShortcodesEffects::init('', $atts);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css($id, $atts);

    $additional_image_class = ShortcodesStyles::getStyleClassname('Image', $atts['image_style']);

    $src_params = array();
    if ($atts['address'])
        $src_params[] = 'q=' . ShortcodesUtility::escape($atts['address']);
    if ($atts['zoom'])
        $src_params[] = 'z=' . ShortcodesUtility::escape($atts['zoom']);
    $src_params[] = 't=' . str_replace(array('road', 'satelite'), array('m', 'k'), ShortcodesUtility::escape($atts['map_type']));
    if ($atts['language'])
        $src_params[] = 'hl=' . ShortcodesUtility::escape($atts['language']);

    $src = ShortcodesUtility::escape($atts['src']) . '&' . implode('&', $src_params);

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <style>
            <?php echo $selector; ?> {
                height: 300px;
                width: 100%;
                display: block;
            }
        </style>
        <?php echo $style_tag; ?>
        <div class="<?php echo $additional_image_class . ' ' . $additional_class; ?> bd-own-margins">
            <div class="embed-responsive" style="height: 100%; width: 100%;">
                <iframe class="embed-responsive-item" src="<?php echo $src; ?>">
                </iframe>
            </div>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ob_get_clean();
}

ShortcodesUtility::addShortcode('googlemap', 'themler_shortcode_googlemap');
?>
<?php
// HoverBox
function themler_shortcode_hover_box($atts, $content, $tag) {
    $atts = ShortcodesUtility::atts(array(
        'active' => '0',
        'motion' => 'over',
        'direction' => 'left',
        'rotation' => '',

        'duration' => '500ms',
        'func' => 'ease',
        'perspective' => 300,

        'url' => '',
        'target' => '',
        'screen_tip' => ''
    ), $atts);

    $url = ShortcodesUtility::escape($atts['url']);
    $target = ShortcodesUtility::escape($atts['target']);
    $screen_tip = ShortcodesUtility::escape($atts['screen_tip']);
    $motion = ShortcodesUtility::escape($atts['motion']);
    $direction = ShortcodesUtility::escape($atts['direction']);
    $rotation = ShortcodesUtility::escape($atts['rotation']);

    $duration = ShortcodesUtility::escape($atts['duration']);
    $func = ShortcodesUtility::escape($atts['func']);
    $perspective = ShortcodesUtility::escape($atts['perspective']);

    global $hover_box_slides;
    if (empty($hover_box_slides)) {
        $hover_box_slides = array();
    }
    $stack_length = count($hover_box_slides);

    $id = ShortcodesEffects::init('', $atts);
    ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css($id, $atts);
    $class = substr($selector, 1);

    if (count($hover_box_slides) !== $stack_length + 2) {
        return '';
    }
    $back_slide = $hover_box_slides[$stack_length];
    $over_slide = $hover_box_slides[$stack_length + 1];
    array_pop($hover_box_slides);
    array_pop($hover_box_slides);

    $effect = themler_shortcode_get_hover_box_selector($motion, $direction, $rotation);

    list($border_radius_styles, ) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'border-radius'), '', '{selector} .bd-backSlide > *, {selector} .bd-overSlide > *', $class);

    $additional_class .= " bd-$effect";

    $back_slide_atts = 'class="bd-backSlide"';
    $over_slide_atts = 'class="bd-overSlide"';
    if ($url) $over_slide_atts .= ' data-url="' . $url . '"';
    if ($target) $over_slide_atts .= ' data-target="' . $target . '"';
    if ($screen_tip) $over_slide_atts .= ' title="' . $screen_tip . '"';

    $back_slide = str_replace('{slider_attributes}', $back_slide_atts, $back_slide);
    $over_slide = str_replace('{slider_attributes}', $over_slide_atts, $over_slide);

    $override_styles = '';
    if ($motion === 'flip' || $motion === 'wobble') {
        // TODO: add -webkit, -moz, ect
        $override_styles .= "
            $selector > .bd-slidesWrapper > .bd-overSlide {
                transition-duration: $duration, $duration, 0ms;

                transition-delay: 0s, 0s, $duration;
            }
        ";
    } else {
        $override_styles .= "
            $selector > .bd-slidesWrapper > .bd-overSlide {
                transition-duration: $duration;
                -webkit-transition-duration: $duration;
            }
        ";
    }

    $override_styles .= "
        $selector,
        $selector > .bd-slidesWrapper {
            -webkit-perspective: $perspective;
            -moz-perspective: $perspective;
            perspective: $perspective;
        }
    ";

    if ($motion === 'slide') {
        $override_styles .= "
            $selector > .bd-slidesWrapper > .bd-backSlide {
                transition-duration: $duration;
                -webkit-transition-duration: $duration;

                transition-timing-function: $func;
                -webkit-transition-timing-function: $func;
            }
        ";
    }

    if ($motion !== 'flip' && $motion !== 'wobble') {
        $override_styles .= "
            $selector > .bd-slidesWrapper > .bd-overSlide {
                transition-timing-function: $func;
                -webkit-transition-timing-function: $func;
            }
        ";
    }



    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <?php echo $style_tag . $border_radius_styles; ?>
        <?php if ($url) { echo "<style>$selector .bd-overSlide { cursor: pointer; }</style>"; } ?>
        <style><?php echo $override_styles; ?></style>
        <div class="<?php echo $additional_class; ?> bd-tagstyles">
            <div class="bd-slidesWrapper">
                <!--{content}-->
                    <?php echo $back_slide; ?>
                    <?php echo $over_slide; ?>
                <!--{/content}-->
            </div>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ob_get_clean();
}
ShortcodesUtility::addShortcode('hover_box', 'themler_shortcode_hover_box');

function themler_shortcode_hover_box_slide($atts, $content, $tag) {
    $atts = ShortcodesUtility::atts(array(
    ), $atts);

    $id = ShortcodesEffects::init('', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts);

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <?php echo $style_tag; ?>
        <div {slider_attributes}>
            <div class="<?php echo $additional_class; ?>">
                <!--{content}-->
                    <?php echo $content; ?>
                <!--{/content}-->
            </div>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php

    global $hover_box_slides;
    if (empty($hover_box_slides)) {
        $hover_box_slides = array();
    }
    $hover_box_slides[] = ob_get_clean();
    return '';
}
ShortcodesUtility::addShortcode('hover_box_slide', 'themler_shortcode_hover_box_slide');

function themler_shortcode_get_hover_box_selector($motion, $direction, $rotation) {
    $selector = 'effect';

    switch ($motion) {
        case 'fade':
            $selector .= '-fade';
            return $selector;
        case 'over':
            $selector .= '-over';
            break;
        case 'slide':
            $selector .= '-slide';
            break;
        case 'flip':
            $selector .= '-flip';
            break;
        case 'wobble':
            $selector .= '-wobble';
            break;
        case 'zoom':
            $selector .= '-zoom';
            switch ($rotation) {
                case 'rotate':
                    $selector .= '-rotate';
                    return $selector;
                case 'rotateX':
                    $selector .= '-rotateX';
                    break;
                case 'rotateY':
                    $selector .= '-rotateY';
                    break;
            }
            return $selector;
    }

    switch ($direction) {
        case 'left':
            $selector .= '-left';
            break;
        case 'right':
            $selector .= '-right';
            break;
        case 'top':
            $selector .= '-top';
            break;
        case 'bottom':
            $selector .= '-bottom';
            break;
    }

    if ($motion === 'over' || $motion === 'slide') {
        switch ($direction) {
            case 'topleft':
                $selector .= '-topleft';
                break;
            case 'topright':
                $selector .= '-topright';
                break;
            case 'bottomleft':
                $selector .= '-bottomleft';
                break;
            case 'bottomright':
                $selector .= '-bottomright';
                break;
        }
    }
    return $selector;
}

?>
<?php
ShortcodesEffects::putIconStyles(<<<EOT
{
    "icon-booth": "{selector}:before { content: '\\\\ff'; }",
    "icon-youtube": "{selector}:before { content: '\\\\100'; }",
    "icon-random": "{selector}:before { content: '\\\\101'; }",
    "icon-cloud-upload": "{selector}:before { content: '\\\\102'; }",
    "icon-road": "{selector}:before { content: '\\\\103'; }",
    "icon-arrow-small-up": "{selector}:before { content: '\\\\104'; }",
    "icon-dropbox": "{selector}:before { content: '\\\\105'; }",
    "icon-sort": "{selector}:before { content: '\\\\106'; }",
    "icon-angle-small": "{selector}:before { content: '\\\\107'; }",
    "icon-bitcoin": "{selector}:before { content: '\\\\108'; }",
    "icon-delicious": "{selector}:before { content: '\\\\109'; }",
    "icon-stethoscope": "{selector}:before { content: '\\\\10a'; }",
    "icon-weibo": "{selector}:before { content: '\\\\10b'; }",
    "icon-volume-off": "{selector}:before { content: '\\\\10c'; }",
    "icon-earth": "{selector}:before { content: '\\\\10d'; }",
    "icon-node-square": "{selector}:before { content: '\\\\10e'; }",
    "icon-plane": "{selector}:before { content: '\\\\10f'; }",
    "icon-undo": "{selector}:before { content: '\\\\110'; }",
    "icon-question-circle": "{selector}:before { content: '\\\\111'; }",
    "icon-tablet": "{selector}:before { content: '\\\\112'; }",
    "icon-filter-alt": "{selector}:before { content: '\\\\113'; }",
    "icon-happy": "{selector}:before { content: '\\\\114'; }",
    "icon-dialer": "{selector}:before { content: '\\\\115'; }",
    "icon-bag": "{selector}:before { content: '\\\\116'; }",
    "icon-credit-card": "{selector}:before { content: '\\\\117'; }",
    "icon-image-alt": "{selector}:before { content: '\\\\118'; }",
    "icon-shopping-cart-simple": "{selector}:before { content: '\\\\119'; }",
    "icon-arrow-basic-right": "{selector}:before { content: '\\\\11a'; }",
    "icon-male": "{selector}:before { content: '\\\\11b'; }",
    "icon-cut": "{selector}:before { content: '\\\\11c'; }",
    "icon-unhappy": "{selector}:before { content: '\\\\11d'; }",
    "icon-circle-alt": "{selector}:before { content: '\\\\11e'; }",
    "icon-double-chevron-right": "{selector}:before { content: '\\\\11f'; }",
    "icon-star-alt": "{selector}:before { content: '\\\\120'; }",
    "icon-rhomb": "{selector}:before { content: '\\\\121'; }",
    "icon-thumbs-down": "{selector}:before { content: '\\\\122'; }",
    "icon-github-alt": "{selector}:before { content: '\\\\123'; }",
    "icon-text-width": "{selector}:before { content: '\\\\124'; }",
    "icon-bookmark-alt": "{selector}:before { content: '\\\\125'; }",
    "icon-list-details": "{selector}:before { content: '\\\\126'; }",
    "icon-bullhorn": "{selector}:before { content: '\\\\127'; }",
    "icon-ellipsis": "{selector}:before { content: '\\\\128'; }",
    "icon-map-marker": "{selector}:before { content: '\\\\129'; }",
    "icon-typeface": "{selector}:before { content: '\\\\12a'; }",
    "icon-help": "{selector}:before { content: '\\\\12b'; }",
    "icon-triangle-circle": "{selector}:before { content: '\\\\12c'; }",
    "icon-gbp": "{selector}:before { content: '\\\\12d'; }",
    "icon-arrow-small-left": "{selector}:before { content: '\\\\12e'; }",
    "icon-anchor": "{selector}:before { content: '\\\\12f'; }",
    "icon-align-justify": "{selector}:before { content: '\\\\130'; }",
    "icon-arrow-circle-alt-up": "{selector}:before { content: '\\\\131'; }",
    "icon-growth": "{selector}:before { content: '\\\\132'; }",
    "icon-round-small": "{selector}:before { content: '\\\\133'; }",
    "icon-triangle-circle-alt": "{selector}:before { content: '\\\\134'; }",
    "icon-eye-close": "{selector}:before { content: '\\\\135'; }",
    "icon-code": "{selector}:before { content: '\\\\136'; }",
    "icon-step-forward": "{selector}:before { content: '\\\\137'; }",
    "icon-music": "{selector}:before { content: '\\\\138'; }",
    "icon-lightbulb": "{selector}:before { content: '\\\\139'; }",
    "icon-arrows-horizontal": "{selector}:before { content: '\\\\13a'; }",
    "icon-sign-out": "{selector}:before { content: '\\\\13b'; }",
    "icon-sort-asc": "{selector}:before { content: '\\\\13c'; }",
    "icon-play-circle": "{selector}:before { content: '\\\\13d'; }",
    "icon-bookmark": "{selector}:before { content: '\\\\13e'; }",
    "icon-pencil": "{selector}:before { content: '\\\\13f'; }",
    "icon-won": "{selector}:before { content: '\\\\140'; }",
    "icon-zoom-out": "{selector}:before { content: '\\\\141'; }",
    "icon-user-alt": "{selector}:before { content: '\\\\142'; }",
    "icon-repeat": "{selector}:before { content: '\\\\143'; }",
    "icon-text-height": "{selector}:before { content: '\\\\144'; }",
    "icon-shopping-cart-wire": "{selector}:before { content: '\\\\145'; }",
    "icon-rubl": "{selector}:before { content: '\\\\146'; }",
    "icon-find-contact": "{selector}:before { content: '\\\\147'; }",
    "icon-upload-circle-alt": "{selector}:before { content: '\\\\148'; }",
    "icon-arrow-small-down": "{selector}:before { content: '\\\\149'; }",
    "icon-file": "{selector}:before { content: '\\\\14a'; }",
    "icon-building": "{selector}:before { content: '\\\\14b'; }",
    "icon-certificate": "{selector}:before { content: '\\\\14c'; }",
    "icon-double-chevron-up": "{selector}:before { content: '\\\\14d'; }",
    "icon-hand-up": "{selector}:before { content: '\\\\14e'; }",
    "icon-italic": "{selector}:before { content: '\\\\14f'; }",
    "icon-volume-up": "{selector}:before { content: '\\\\150'; }",
    "icon-quote-right": "{selector}:before { content: '\\\\151'; }",
    "icon-sort-numeric-desc": "{selector}:before { content: '\\\\152'; }",
    "icon-four-rhombs": "{selector}:before { content: '\\\\153'; }",
    "icon-brain": "{selector}:before { content: '\\\\154'; }",
    "icon-mark": "{selector}:before { content: '\\\\155'; }",
    "icon-flickr": "{selector}:before { content: '\\\\156'; }",
    "icon-envelope": "{selector}:before { content: '\\\\157'; }",
    "icon-indent-right": "{selector}:before { content: '\\\\158'; }",
    "icon-basket-simple": "{selector}:before { content: '\\\\159'; }",
    "icon-cloud": "{selector}:before { content: '\\\\15a'; }",
    "icon-check": "{selector}:before { content: '\\\\15b'; }",
    "icon-youtube-square": "{selector}:before { content: '\\\\15c'; }",
    "icon-envelope-alt": "{selector}:before { content: '\\\\15d'; }",
    "icon-bitbucket-alt": "{selector}:before { content: '\\\\15e'; }",
    "icon-round-small-alt": "{selector}:before { content: '\\\\15f'; }",
    "icon-adn": "{selector}:before { content: '\\\\160'; }",
    "icon-linkedin-square": "{selector}:before { content: '\\\\161'; }",
    "icon-expand": "{selector}:before { content: '\\\\162'; }",
    "icon-tumblr-square": "{selector}:before { content: '\\\\163'; }",
    "icon-angle-double": "{selector}:before { content: '\\\\164'; }",
    "icon-compress": "{selector}:before { content: '\\\\165'; }",
    "icon-plus-square-alt": "{selector}:before { content: '\\\\166'; }",
    "icon-camera": "{selector}:before { content: '\\\\167'; }",
    "icon-four-boxes": "{selector}:before { content: '\\\\168'; }",
    "icon-shopping-cart-buggy": "{selector}:before { content: '\\\\169'; }",
    "icon-arrow-square-left": "{selector}:before { content: '\\\\16a'; }",
    "icon-delete-circle-alt": "{selector}:before { content: '\\\\16b'; }",
    "icon-suitcase": "{selector}:before { content: '\\\\16c'; }",
    "icon-curve-bottom": "{selector}:before { content: '\\\\16d'; }",
    "icon-caret-up": "{selector}:before { content: '\\\\16e'; }",
    "icon-renren": "{selector}:before { content: '\\\\16f'; }",
    "icon-linkedin": "{selector}:before { content: '\\\\170'; }",
    "icon-asterisk": "{selector}:before { content: '\\\\171'; }",
    "icon-arrow-pointer-left": "{selector}:before { content: '\\\\172'; }",
    "icon-sort-numeric-asc": "{selector}:before { content: '\\\\173'; }",
    "icon-calendar-simple": "{selector}:before { content: '\\\\174'; }",
    "icon-home-alt": "{selector}:before { content: '\\\\175'; }",
    "icon-step-backward": "{selector}:before { content: '\\\\176'; }",
    "icon-rss": "{selector}:before { content: '\\\\177'; }",
    "icon-globe": "{selector}:before { content: '\\\\178'; }",
    "icon-paste": "{selector}:before { content: '\\\\179'; }",
    "icon-fire": "{selector}:before { content: '\\\\17a'; }",
    "icon-star-half": "{selector}:before { content: '\\\\17b'; }",
    "icon-renminbi": "{selector}:before { content: '\\\\17c'; }",
    "icon-dribbble": "{selector}:before { content: '\\\\17d'; }",
    "icon-google-plus-square": "{selector}:before { content: '\\\\17e'; }",
    "icon-plus-square": "{selector}:before { content: '\\\\17f'; }",
    "icon-yen": "{selector}:before { content: '\\\\180'; }",
    "icon-briefcase": "{selector}:before { content: '\\\\181'; }",
    "icon-shopping-cart-trolley": "{selector}:before { content: '\\\\182'; }",
    "icon-warning": "{selector}:before { content: '\\\\183'; }",
    "icon-moon": "{selector}:before { content: '\\\\184'; }",
    "icon-sort-alpha": "{selector}:before { content: '\\\\185'; }",
    "icon-arrow-long-down": "{selector}:before { content: '\\\\186'; }",
    "icon-globe-alt": "{selector}:before { content: '\\\\187'; }",
    "icon-thumbs-up": "{selector}:before { content: '\\\\188'; }",
    "icon-sandwich": "{selector}:before { content: '\\\\189'; }",
    "icon-arrow-basic-down": "{selector}:before { content: '\\\\18a'; }",
    "icon-double-chevron-down": "{selector}:before { content: '\\\\18b'; }",
    "icon-legal": "{selector}:before { content: '\\\\18c'; }",
    "icon-apple": "{selector}:before { content: '\\\\18d'; }",
    "icon-power": "{selector}:before { content: '\\\\18e'; }",
    "icon-time-alt": "{selector}:before { content: '\\\\18f'; }",
    "icon-list": "{selector}:before { content: '\\\\190'; }",
    "icon-h-sign": "{selector}:before { content: '\\\\191'; }",
    "icon-css": "{selector}3:before { content: '\\\\192'; }",
    "icon-copy": "{selector}:before { content: '\\\\193'; }",
    "icon-arrow-tall-up": "{selector}:before { content: '\\\\194'; }",
    "icon-hdd": "{selector}:before { content: '\\\\195'; }",
    "icon-font": "{selector}:before { content: '\\\\196'; }",
    "icon-heart-circle": "{selector}:before { content: '\\\\197'; }",
    "icon-glass": "{selector}:before { content: '\\\\198'; }",
    "icon-picasa": "{selector}:before { content: '\\\\199'; }",
    "icon-arrow-long-left": "{selector}:before { content: '\\\\19a'; }",
    "icon-fullscreen": "{selector}:before { content: '\\\\19b'; }",
    "icon-lemon": "{selector}:before { content: '\\\\19c'; }",
    "icon-arrow-long-right": "{selector}:before { content: '\\\\19d'; }",
    "icon-hand-right": "{selector}:before { content: '\\\\19e'; }",
    "icon-list-details-small": "{selector}:before { content: '\\\\19f'; }",
    "icon-cog": "{selector}:before { content: '\\\\1a0'; }",
    "icon-four-boxes-alt": "{selector}:before { content: '\\\\1a1'; }",
    "icon-properties": "{selector}:before { content: '\\\\1a2'; }",
    "icon-arrow-pointer-down": "{selector}:before { content: '\\\\1a3'; }",
    "icon-inbox": "{selector}:before { content: '\\\\1a4'; }",
    "icon-arrow-double-up": "{selector}:before { content: '\\\\1a5'; }",
    "icon-plane-takeoff": "{selector}:before { content: '\\\\1a6'; }",
    "icon-arrow-square-down": "{selector}:before { content: '\\\\1a7'; }",
    "icon-tick-circle-alt": "{selector}:before { content: '\\\\1a8'; }",
    "icon-node-circle": "{selector}:before { content: '\\\\1a9'; }",
    "icon-arrow-pointer-right": "{selector}:before { content: '\\\\1aa'; }",
    "icon-starlet": "{selector}:before { content: '\\\\1ab'; }",
    "icon-cogs": "{selector}:before { content: '\\\\1ac'; }",
    "icon-arrow-long-up": "{selector}:before { content: '\\\\1ad'; }",
    "icon-bug": "{selector}:before { content: '\\\\1ae'; }",
    "icon-upload": "{selector}:before { content: '\\\\1af'; }",
    "icon-xing": "{selector}:before { content: '\\\\1b0'; }",
    "icon-minus-square-alt": "{selector}:before { content: '\\\\1b1'; }",
    "icon-arrows": "{selector}:before { content: '\\\\1b2'; }",
    "icon-trash-can": "{selector}:before { content: '\\\\1b3'; }",
    "icon-pushpin": "{selector}:before { content: '\\\\1b4'; }",
    "icon-eye-open": "{selector}:before { content: '\\\\1b5'; }",
    "icon-caret-left": "{selector}:before { content: '\\\\1b6'; }",
    "icon-bitbucket": "{selector}:before { content: '\\\\1b7'; }",
    "icon-lines": "{selector}:before { content: '\\\\1b8'; }",
    "icon-magic": "{selector}:before { content: '\\\\1b9'; }",
    "icon-arrow-double-right": "{selector}:before { content: '\\\\1ba'; }",
    "icon-remove-sign": "{selector}:before { content: '\\\\1bb'; }",
    "icon-exclamation-sign": "{selector}:before { content: '\\\\1bc'; }",
    "icon-chevron-down": "{selector}:before { content: '\\\\1bd'; }",
    "icon-sort-alpha-asc": "{selector}:before { content: '\\\\1be'; }",
    "icon-comments-alt": "{selector}:before { content: '\\\\1bf'; }",
    "icon-terminal": "{selector}:before { content: '\\\\1c0'; }",
    "icon-box": "{selector}:before { content: '\\\\1c1'; }",
    "icon-lock": "{selector}:before { content: '\\\\1c2'; }",
    "icon-bolt": "{selector}:before { content: '\\\\1c3'; }",
    "icon-filter": "{selector}:before { content: '\\\\1c4'; }",
    "icon-folder-alt": "{selector}:before { content: '\\\\1c5'; }",
    "icon-backward": "{selector}:before { content: '\\\\1c6'; }",
    "icon-sort-amount-asc": "{selector}:before { content: '\\\\1c7'; }",
    "icon-tag": "{selector}:before { content: '\\\\1c8'; }",
    "icon-house": "{selector}:before { content: '\\\\1c9'; }",
    "icon-drop": "{selector}:before { content: '\\\\1ca'; }",
    "icon-arrow-thick-right": "{selector}:before { content: '\\\\1cb'; }",
    "icon-ambulance": "{selector}:before { content: '\\\\1cc'; }",
    "icon-chevron-right": "{selector}:before { content: '\\\\1cd'; }",
    "icon-sign-in": "{selector}:before { content: '\\\\1ce'; }",
    "icon-sort-amount-desc": "{selector}:before { content: '\\\\1cf'; }",
    "icon-search-circle": "{selector}:before { content: '\\\\1d0'; }",
    "icon-skype": "{selector}:before { content: '\\\\1d1'; }",
    "icon-fast-forward": "{selector}:before { content: '\\\\1d2'; }",
    "icon-maxcdn": "{selector}:before { content: '\\\\1d3'; }",
    "icon-book-open": "{selector}:before { content: '\\\\1d4'; }",
    "icon-vimeo": "{selector}:before { content: '\\\\1d5'; }",
    "icon-beaker": "{selector}:before { content: '\\\\1d6'; }",
    "icon-facebook-alt": "{selector}:before { content: '\\\\1d7'; }",
    "icon-arrow-thin-down": "{selector}:before { content: '\\\\1d8'; }",
    "icon-heartlet": "{selector}:before { content: '\\\\1d9'; }",
    "icon-shopping-cart-solid": "{selector}:before { content: '\\\\1da'; }",
    "icon-linux": "{selector}:before { content: '\\\\1db'; }",
    "icon-leaf": "{selector}:before { content: '\\\\1dc'; }",
    "icon-hand-down": "{selector}:before { content: '\\\\1dd'; }",
    "icon-pinterest": "{selector}:before { content: '\\\\1de'; }",
    "icon-barcode": "{selector}:before { content: '\\\\1df'; }",
    "icon-curve-top": "{selector}:before { content: '\\\\1e0'; }",
    "icon-euro": "{selector}:before { content: '\\\\1e1'; }",
    "icon-arrow-basic-left": "{selector}:before { content: '\\\\1e2'; }",
    "icon-group": "{selector}:before { content: '\\\\1e3'; }",
    "icon-tumblr": "{selector}:before { content: '\\\\1e4'; }",
    "icon-fighter": "{selector}:before { content: '\\\\1e5'; }",
    "icon-hand-left": "{selector}:before { content: '\\\\1e6'; }",
    "icon-stripes-thick": "{selector}:before { content: '\\\\1e7'; }",
    "icon-superscript": "{selector}:before { content: '\\\\1e8'; }",
    "icon-minus-square": "{selector}:before { content: '\\\\1e9'; }",
    "icon-ticklet-circle": "{selector}:before { content: '\\\\1ea'; }",
    "icon-xing-square": "{selector}:before { content: '\\\\1eb'; }",
    "icon-arrow-double-left": "{selector}:before { content: '\\\\1ec'; }",
    "icon-forward": "{selector}:before { content: '\\\\1ed'; }",
    "icon-arrow-thick-down": "{selector}:before { content: '\\\\1ee'; }",
    "icon-eject": "{selector}:before { content: '\\\\1ef'; }",
    "icon-reply": "{selector}:before { content: '\\\\1f0'; }",
    "icon-search": "{selector}:before { content: '\\\\1f1'; }",
    "icon-comment-alt": "{selector}:before { content: '\\\\1f2'; }",
    "icon-share": "{selector}:before { content: '\\\\1f3'; }",
    "icon-arrows-vertical": "{selector}:before { content: '\\\\1f4'; }",
    "icon-food": "{selector}:before { content: '\\\\1f5'; }",
    "icon-flag": "{selector}:before { content: '\\\\1f6'; }",
    "icon-female": "{selector}:before { content: '\\\\1f7'; }",
    "icon-tasks": "{selector}:before { content: '\\\\1f8'; }",
    "icon-quote-left": "{selector}:before { content: '\\\\1f9'; }",
    "icon-arrow-tall-left": "{selector}:before { content: '\\\\1fa'; }",
    "icon-minus-circle": "{selector}:before { content: '\\\\1fb'; }",
    "icon-box-alt": "{selector}:before { content: '\\\\1fc'; }",
    "icon-arrow-tall-down": "{selector}:before { content: '\\\\1fd'; }",
    "icon-indent-left": "{selector}:before { content: '\\\\1fe'; }",
    "icon-arrow-thick-left": "{selector}:before { content: '\\\\1ff'; }",
    "icon-list-adv": "{selector}:before { content: '\\\\200'; }",
    "icon-chevron-up": "{selector}:before { content: '\\\\201'; }",
    "icon-medkit": "{selector}:before { content: '\\\\202'; }",
    "icon-tags": "{selector}:before { content: '\\\\203'; }",
    "icon-coffee": "{selector}:before { content: '\\\\204'; }",
    "icon-ticklet": "{selector}:before { content: '\\\\205'; }",
    "icon-box-small": "{selector}:before { content: '\\\\206'; }",
    "icon-sign-blank": "{selector}:before { content: '\\\\207'; }",
    "icon-basket": "{selector}:before { content: '\\\\208'; }",
    "icon-search-thick": "{selector}:before { content: '\\\\209'; }",
    "icon-edit-alt": "{selector}:before { content: '\\\\20a'; }",
    "icon-comment": "{selector}:before { content: '\\\\20b'; }",
    "icon-hospital": "{selector}:before { content: '\\\\20c'; }",
    "icon-arrow-small-right": "{selector}:before { content: '\\\\20d'; }",
    "icon-grid-small": "{selector}:before { content: '\\\\20e'; }",
    "icon-circle-arrow-left": "{selector}:before { content: '\\\\20f'; }",
    "icon-yahoo": "{selector}:before { content: '\\\\210'; }",
    "icon-print": "{selector}:before { content: '\\\\211'; }",
    "icon-instagram": "{selector}:before { content: '\\\\212'; }",
    "icon-arrow-angle-up": "{selector}:before { content: '\\\\213'; }",
    "icon-leaf-thin": "{selector}:before { content: '\\\\214'; }",
    "icon-magnet": "{selector}:before { content: '\\\\215'; }",
    "icon-arrow-thin-up": "{selector}:before { content: '\\\\216'; }",
    "icon-retweet": "{selector}:before { content: '\\\\217'; }",
    "icon-search-glare": "{selector}:before { content: '\\\\218'; }",
    "icon-search-thin": "{selector}:before { content: '\\\\219'; }",
    "icon-heart-empty": "{selector}:before { content: '\\\\21a'; }",
    "icon-scales": "{selector}:before { content: '\\\\21b'; }",
    "icon-sort-alpha-desc": "{selector}:before { content: '\\\\21c'; }",
    "icon-align-right": "{selector}:before { content: '\\\\21d'; }",
    "icon-stripes": "{selector}:before { content: '\\\\21e'; }",
    "icon-arrow-pointer-up": "{selector}:before { content: '\\\\21f'; }",
    "icon-round": "{selector}:before { content: '\\\\220'; }",
    "icon-user-medical": "{selector}:before { content: '\\\\221'; }",
    "icon-arrow-thin-left": "{selector}:before { content: '\\\\222'; }",
    "icon-arrow-circle-alt-right": "{selector}:before { content: '\\\\223'; }",
    "icon-starlet-alt": "{selector}:before { content: '\\\\224'; }",
    "icon-bold": "{selector}:before { content: '\\\\225'; }",
    "icon-aperture": "{selector}:before { content: '\\\\226'; }",
    "icon-pointer-basic": "{selector}:before { content: '\\\\227'; }",
    "icon-folder": "{selector}:before { content: '\\\\228'; }",
    "icon-heart": "{selector}:before { content: '\\\\229'; }",
    "icon-cloud-download": "{selector}:before { content: '\\\\22a'; }",
    "icon-bar-chart": "{selector}:before { content: '\\\\22b'; }",
    "icon-mobile": "{selector}:before { content: '\\\\22c'; }",
    "icon-volume-down": "{selector}:before { content: '\\\\22d'; }",
    "icon-exchange": "{selector}:before { content: '\\\\22e'; }",
    "icon-folder-open": "{selector}:before { content: '\\\\22f'; }",
    "icon-phone-square": "{selector}:before { content: '\\\\230'; }",
    "icon-zoom-in": "{selector}:before { content: '\\\\231'; }",
    "icon-beer": "{selector}:before { content: '\\\\232'; }",
    "icon-trello-square": "{selector}:before { content: '\\\\233'; }",
    "icon-delete": "{selector}:before { content: '\\\\234'; }",
    "icon-image": "{selector}:before { content: '\\\\235'; }",
    "icon-edit": "{selector}:before { content: '\\\\236'; }",
    "icon-twitter-square": "{selector}:before { content: '\\\\237'; }",
    "icon-external-link": "{selector}:before { content: '\\\\238'; }",
    "icon-money": "{selector}:before { content: '\\\\239'; }",
    "icon-html": "{selector}5:before { content: '\\\\23a'; }",
    "icon-youtube-play": "{selector}:before { content: '\\\\23b'; }",
    "icon-play": "{selector}:before { content: '\\\\23c'; }",
    "icon-calendar": "{selector}:before { content: '\\\\23d'; }",
    "icon-video": "{selector}:before { content: '\\\\23e'; }",
    "icon-adjust": "{selector}:before { content: '\\\\23f'; }",
    "icon-plus-circle": "{selector}:before { content: '\\\\240'; }",
    "icon-strikethrough": "{selector}:before { content: '\\\\241'; }",
    "icon-bell": "{selector}:before { content: '\\\\242'; }",
    "icon-crop": "{selector}:before { content: '\\\\243'; }",
    "icon-restore": "{selector}:before { content: '\\\\244'; }",
    "icon-circle-arrow-up": "{selector}:before { content: '\\\\245'; }",
    "icon-twitter": "{selector}:before { content: '\\\\246'; }",
    "icon-sitemap": "{selector}:before { content: '\\\\247'; }",
    "icon-facebook-square": "{selector}:before { content: '\\\\248'; }",
    "icon-downturn": "{selector}:before { content: '\\\\249'; }",
    "icon-fancy-circle-alt": "{selector}:before { content: '\\\\24a'; }",
    "icon-arrow-square-right": "{selector}:before { content: '\\\\24b'; }",
    "icon-save": "{selector}:before { content: '\\\\24c'; }",
    "icon-share-alt": "{selector}:before { content: '\\\\24d'; }",
    "icon-arrow-thick-up": "{selector}:before { content: '\\\\24e'; }",
    "icon-plus": "{selector}:before { content: '\\\\24f'; }",
    "icon-arrows-alt": "{selector}:before { content: '\\\\250'; }",
    "icon-chevron-left": "{selector}:before { content: '\\\\251'; }",
    "icon-circle-arrow-right": "{selector}:before { content: '\\\\252'; }",
    "icon-arrow-double-down": "{selector}:before { content: '\\\\253'; }",
    "icon-film": "{selector}:before { content: '\\\\254'; }",
    "icon-pie-chart": "{selector}:before { content: '\\\\255'; }",
    "icon-github": "{selector}:before { content: '\\\\256'; }",
    "icon-calendar-day-alt": "{selector}:before { content: '\\\\257'; }",
    "icon-sort-numeric": "{selector}:before { content: '\\\\258'; }",
    "icon-align-center": "{selector}:before { content: '\\\\259'; }",
    "icon-caret-down": "{selector}:before { content: '\\\\25a'; }",
    "icon-round-alt": "{selector}:before { content: '\\\\25b'; }",
    "icon-user-business": "{selector}:before { content: '\\\\25c'; }",
    "icon-signal": "{selector}:before { content: '\\\\25d'; }",
    "icon-reply-all": "{selector}:before { content: '\\\\25e'; }",
    "icon-star": "{selector}:before { content: '\\\\25f'; }",
    "icon-book": "{selector}:before { content: '\\\\260'; }",
    "icon-triangle": "{selector}:before { content: '\\\\261'; }",
    "icon-arrow-angle-right": "{selector}:before { content: '\\\\262'; }",
    "icon-arrow-basic-up": "{selector}:before { content: '\\\\263'; }",
    "icon-caret-right": "{selector}:before { content: '\\\\264'; }",
    "icon-align-left": "{selector}:before { content: '\\\\265'; }",
    "icon-comments": "{selector}:before { content: '\\\\266'; }",
    "icon-vk": "{selector}:before { content: '\\\\267'; }",
    "icon-qrcode": "{selector}:before { content: '\\\\268'; }",
    "icon-arrow-tall-right": "{selector}:before { content: '\\\\269'; }",
    "icon-shopping-cart": "{selector}:before { content: '\\\\26a'; }",
    "icon-pause": "{selector}:before { content: '\\\\26b'; }",
    "icon-umbrella": "{selector}:before { content: '\\\\26c'; }",
    "icon-ban": "{selector}:before { content: '\\\\26d'; }",
    "icon-plane-alt": "{selector}:before { content: '\\\\26e'; }",
    "icon-ticklet-circle-alt": "{selector}:before { content: '\\\\26f'; }",
    "icon-arrow-angle-left": "{selector}:before { content: '\\\\270'; }",
    "icon-android": "{selector}:before { content: '\\\\271'; }",
    "icon-arrow-square-up": "{selector}:before { content: '\\\\272'; }",
    "icon-inr": "{selector}:before { content: '\\\\273'; }",
    "icon-label": "{selector}:before { content: '\\\\274'; }",
    "icon-spinner": "{selector}:before { content: '\\\\275'; }",
    "icon-headphones": "{selector}:before { content: '\\\\276'; }",
    "icon-arrow-fancy": "{selector}:before { content: '\\\\277'; }",
    "icon-sort-desc": "{selector}:before { content: '\\\\278'; }",
    "icon-tick-circle": "{selector}:before { content: '\\\\279'; }",
    "icon-info-sign": "{selector}:before { content: '\\\\27a'; }",
    "icon-screenshot": "{selector}:before { content: '\\\\27b'; }",
    "icon-briefcase-simple": "{selector}:before { content: '\\\\27c'; }",
    "icon-search-alt": "{selector}:before { content: '\\\\27d'; }",
    "icon-time": "{selector}:before { content: '\\\\27e'; }",
    "icon-grid": "{selector}:before { content: '\\\\27f'; }",
    "icon-user": "{selector}:before { content: '\\\\280'; }",
    "icon-facebook": "{selector}:before { content: '\\\\281'; }",
    "icon-google-plus": "{selector}:before { content: '\\\\282'; }",
    "icon-github-square": "{selector}:before { content: '\\\\283'; }",
    "icon-check-empty": "{selector}:before { content: '\\\\284'; }",
    "icon-circle": "{selector}:before { content: '\\\\285'; }",
    "icon-fast-backward": "{selector}:before { content: '\\\\286'; }",
    "icon-calendar-day": "{selector}:before { content: '\\\\287'; }",
    "icon-phone": "{selector}:before { content: '\\\\288'; }",
    "icon-pinterest-square": "{selector}:before { content: '\\\\289'; }",
    "icon-cup": "{selector}:before { content: '\\\\28a'; }",
    "icon-star-thin": "{selector}:before { content: '\\\\28b'; }",
    "icon-wrench": "{selector}:before { content: '\\\\28c'; }",
    "icon-truck": "{selector}:before { content: '\\\\28d'; }",
    "icon-product-view-mode": "{selector}:before { content: '\\\\28e'; }",
    "icon-circle-arrow-down": "{selector}:before { content: '\\\\28f'; }",
    "icon-arrow-circle-alt-left": "{selector}:before { content: '\\\\290'; }",
    "icon-stackexchange": "{selector}:before { content: '\\\\291'; }",
    "icon-ticklet-thick": "{selector}:before { content: '\\\\292'; }",
    "icon-arrow-thin-right": "{selector}:before { content: '\\\\293'; }",
    "icon-tick": "{selector}:before { content: '\\\\294'; }",
    "icon-box-small-alt": "{selector}:before { content: '\\\\295'; }",
    "icon-file-alt": "{selector}:before { content: '\\\\296'; }",
    "icon-minus": "{selector}:before { content: '\\\\297'; }",
    "icon-upload-circle": "{selector}:before { content: '\\\\298'; }",
    "icon-gift": "{selector}:before { content: '\\\\299'; }",
    "icon-globe-outline": "{selector}:before { content: '\\\\29a'; }",
    "icon-windows": "{selector}:before { content: '\\\\29b'; }",
    "icon-arrow-line": "{selector}:before { content: '\\\\29c'; }",
    "icon-flag-alt": "{selector}:before { content: '\\\\29d'; }",
    "icon-home": "{selector}:before { content: '\\\\29e'; }",
    "icon-arrow-circle-alt-down": "{selector}:before { content: '\\\\29f'; }",
    "icon-dollar": "{selector}:before { content: '\\\\2a0'; }",
    "icon-double-chevron-left": "{selector}:before { content: '\\\\2a1'; }",
    "icon-arrow-angle-down": "{selector}:before { content: '\\\\2a2'; }"
}
EOT
);
?>
<?php

// IconLink
function themler_shortcode_icon($atts, $content = '') {
    $atts = ShortcodesUtility::atts(array(
        'link' => '',
        'title' => '',
        'target' => '',

        'icon' => '',
        'picture' => '',
        'icon_hovered' => '',
        'picture_hovered' => '',
    ), $atts, array('', 'icon_', 'icon_hovered_'));

    $link = ShortcodesUtility::escape($atts['link']);
    $title = ShortcodesUtility::escape($atts['title']);
    $target = ShortcodesUtility::escape($atts['target']);
    $icon_passive = ShortcodesUtility::escape($atts['icon']);
    $picture_passive = ShortcodesUtility::escape($atts['picture']);
    $icon_hovered = ShortcodesUtility::escape($atts['icon_hovered']);
    $picture_hovered = ShortcodesUtility::escape($atts['picture_hovered']);


    $sid = ShortcodesEffects::init('', $atts);
    list($style_tag, $additional_classes, $selector) = ShortcodesEffects::css($sid, $atts);

    $classes = array();
    $classes[] = $additional_classes;
    $icon_class = '';
    if ($icon_passive && $icon_passive !== 'none' || $picture_passive ||
        $icon_hovered && $icon_hovered !== 'none' || $picture_hovered) {

        $icon_class = 'bd-icon';
    }

    $icon_passive_style_tag = themler_shortcodes_icon_state_style($sid, array(
        'picture' => $picture_passive,
        'icon' => $icon_passive,
        'selector' => $selector . ($link ? ' span' : ''),
        'atts' => $atts,
        'icon_prefix' => 'icon_'
    ));
    $icon_hovered_style_tag = themler_shortcodes_icon_state_style($sid, array(
        'picture' => $picture_hovered,
        'icon' => $icon_hovered,
        'selector' => $selector . ($link ? ' span' : '') . ':hover',
        'atts' => $atts,
        'icon_prefix' => 'icon_hovered_'
    ));

    if ($link) {
        $html_atts = array();
        if ($title)
            $html_atts[] = 'title="' . $title . '"';
        if ($target)
            $html_atts[] = 'target="' . $target . '"';
        $html_atts[] = 'href="' . $link . '"';
        $html_atts[] = 'class="' . ($icon_class ? 'bd-iconlink ' : '') . implode(' ', $classes) . ' bd-own-margins"';

        $content = '<a ' . implode(' ', $html_atts) . '>' .
            '<span class="' . $icon_class . '"><!--{content}--><!--{/content}--></span></a>';
    } else {
        $content = '<span class="' . $icon_class . ' ' . implode(' ', $classes) .
            ' bd-own-margins"><!--{content}--><!--{/content}--></span>';
    }

    return
        '<!--[icon]-->' .
            $style_tag . $icon_passive_style_tag . $icon_hovered_style_tag .
            $content .
        '<!--[/icon]-->';
}
ShortcodesUtility::addShortcode('icon', 'themler_shortcode_icon');
?>
<?php

// ImageLink
function themler_shortcode_image($atts, $content = '', $tag = '', $parent = array()) {
    if ($parent) {
        $id = $parent['id'];
        $prefix = $parent['prefix'];
    } else {
        $atts = ShortcodesUtility::atts(array(
            'image' => '',
            'href' => '',
            'link' => '', // alias for href
            'target' => '',
            'screen_tip' => '',
            'alt' => '',
            'image_style' => '',
            'type' => '',
            'responsive' => '',
        ), $atts);
        $id = ShortcodesEffects::init('', $atts);
        $prefix = '';
    }

    $image = ShortcodesUtility::escape($atts[$prefix . 'image']);

    // w/o prefix
    $href = ShortcodesUtility::escape(empty($atts[$prefix . 'href']) ? '' : $atts[$prefix . 'href']);
    if (!$href) {
        $href = ShortcodesUtility::escape(empty($atts[$prefix . 'link']) ? '' : $atts[$prefix . 'link']);
    }
    $target     = ShortcodesUtility::escape(empty($atts[$prefix . 'target'])     ? '' : $atts[$prefix . 'target']);
    $screen_tip = ShortcodesUtility::escape(empty($atts[$prefix . 'screen_tip']) ? '' : $atts[$prefix . 'screen_tip']);
    $alt        = ShortcodesUtility::escape(empty($atts[$prefix . 'alt'])        ? '' : $atts[$prefix . 'alt']);

    $additionalClass     = empty($parent['additionalClass'])     ? '' : $parent['additionalClass'];
    $additionalImageAtts = empty($parent['additionalImageAtts']) ? '' : $parent['additionalImageAtts'];

    list($style_tag, $controlClass, $selector) = ShortcodesEffects::css($id, $atts, $prefix);

    $additional_image_style_class = ShortcodesStyles::getStyleClassname('Image', $atts[$prefix . 'image_style']);
    if (isset($atts[$prefix . 'responsive']) && ShortcodesUtility::getBool($atts[$prefix . 'responsive'])) {
        $additional_image_style_class .= ' img-responsive';
    }
    $class = substr($selector, 1);

    if ($image && $href) {
        list($style_image_tag1,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'positioning,transform,margin,float'), $prefix, '{selector}', $class);
        list($style_image_tag2,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'size'), $prefix, '{selector}', $class);
        list($style_image_tag3,) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, '!positioning,!transform,!size,!margin,!float'), $prefix, '{selector}', $class);

        $style_tag = "
            <style>
                $selector {
                    display: inline-block;
                }
            </style>
            $style_image_tag1
            $style_image_tag2

            <style>
                $selector img {
                    display: inline-block;
                    width: 100%;
                    height: 100%;
                }
            </style>
            $style_image_tag3
            ";
    }

    $a_atts = array();
    $img_atts = array();

    $img_atts[] = 'src="' . $image . '"';
    if ($alt)
        $img_atts[] = 'alt="' . $alt . '"';

    if ($href) {
        if ($target)
            $a_atts[] = 'target="' . $target . '"';
        if ($screen_tip)
            $a_atts[] = 'title="' . $screen_tip . '"';

        $a_atts[] = 'href="' . $href . '"';
        $a_atts[] = 'class="' . $controlClass . ' ' . $additionalClass . ' bd-own-margins"';
        $img_atts[] = 'class="' . $additional_image_style_class .'"';
        $img_atts[] = $additionalImageAtts;

        $content = '<a ' . implode(' ', $a_atts) . '>' .
                '<img ' . implode(' ', $img_atts) . '>' .
            '<!--{content}--><!--{/content}--></a>';
    } else {
        $img_atts[] = 'class="' . $controlClass . ' ' . $additionalClass . ' ' . $additional_image_style_class .' bd-own-margins"';
        $content = '<img ' . implode(' ', $img_atts) . '><!--{content}--><!--{/content}-->';
    }

    if (!empty($atts[$prefix . 'type'])) {
        $style_tag .= "<style>
            $selector > img, img$selector {
                max-width: 100%;
            }
        </style>";
    }

    return $parent ? array('html' => $content, 'css' => $style_tag) :
        "<!--[$tag]-->" .
            $style_tag .
            $content .
        "<!--[/$tag]-->";
}
ShortcodesUtility::addShortcode('image', 'themler_shortcode_image');
?>
<?php

// ImageScaling
function themler_shortcode_image_scaling($atts, $content = '', $tag) {
    $atts = ShortcodesUtility::atts(array(), $atts);

    $id = ShortcodesEffects::init('bd-imagescaling', $atts, ShortcodesEffects::CSS_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);

    list($style_css, $additional_classes, $selector) = ShortcodesEffects::css(
        $id, $atts, '',
        '{selector}.bd-imagescaling-img, {selector} .bd-imagescaling-img'
    );

    $target_control = ShortcodesEffects::target_control($id);

    $content = ShortcodesEffects::addClassesAndAttrs(
        $content,
        $target_control,
        array('bd-imagescaling', $additional_classes),
        array()
    );

    ob_start();
?>
    <!--[<?php echo $tag ?>]-->
        <?php echo $style_css; ?>
        <!--{content}-->
            <?php echo $content ?>
        <!--{/content}-->
    <!--[/<?php echo $tag ?>]-->
<?php
    return ob_get_clean();
}

ShortcodesUtility::addEffectShortcode('image_scaling', 'themler_shortcode_image_scaling');
?>
<?php

// LayoutColumn
function themler_shortcode_column($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'width_lg' => '',
        'width' => '',
        'width_sm' => '',
        'width_xs' => ''
    ), $atts);

    $col_classes = array();
    if (intval($atts['width_lg']) > 0) {
        $col_classes[] = 'col-lg-' . $atts['width_lg'];
    }
    if (intval($atts['width']) > 0) {
        $col_classes[] = 'col-md-' . $atts['width'];
    }
    if (intval($atts['width_sm']) > 0) {
        $col_classes[] = 'col-sm-' . $atts['width_sm'];
    }
    if (intval($atts['width_xs']) > 0) {
        $col_classes[] = 'col-xs-' . $atts['width_xs'];
    }
    $col_classes = array_merge($col_classes, ShortcodesEffects::hidden_classes($atts));

    $id = ShortcodesEffects::init('bd-layoutcolumn', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css(
        $id, ShortcodesEffects::filter($atts, '!order')
    );

    $order_class = preg_replace('/[^\d]+(\d+)/', 'bd-columnwrapper-$1', $selector);
    $order_tag = ShortcodesEffects::print_all_css(
        ShortcodesEffects::filter($atts, 'order'), 'css', '.' . $order_class
    );
    if (trim($order_tag)) {
        $order_tag = '<style>' . $order_tag . '</style>';
    }
    $col_classes[] = $order_class;

    $style_tag = $style_tag . $order_tag;

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <div class="<?php echo implode(' ', $col_classes); ?>">
            <div class="bd-column <?php echo $additional_class ?>">
                <div class="bd-vertical-align-wrapper">
                    <!--{content}-->
                        <?php echo $content; ?>
                    <!--{/content}-->
                </div>
            </div>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ShortcodesUtility::addResult(ob_get_clean(), $style_tag);
}

ShortcodesUtility::addShortcode('column', 'themler_shortcode_column');
?>
<?php

// LayoutContainer
function themler_shortcode_columns($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'collapse_spacing' => '0',
        'auto_height' => '',
        'vertical_align' => 'top',
        'add_margins' => 'auto', // auto = old version
        'spacing_css' => ''
    ), $atts);

    $id = ShortcodesEffects::init('bd-layoutcontainer', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css($id, $atts);

    $row_classes = array('row');
    $classes = array($additional_class);

    if (ShortcodesUtility::getBool($atts['collapse_spacing'])) {
        $row_classes[] = 'bd-collapsed-gutter';
    } else {
        $rowAtts = ShortcodesEffects::filter($atts, 'margin,height', 'spacing_');
        $colAtts = ShortcodesEffects::filter($atts, 'padding', 'spacing_');

        $rowStyle = ShortcodesEffects::print_all_css(
            $rowAtts,
            'spacing_css',
            $selector . ' > .bd-container-inner > .container-fluid > .row'
        );

        $colStyle = ShortcodesEffects::print_all_css(
            $colAtts,
            'spacing_css',
            $selector . ' > .bd-container-inner > .container-fluid > .row > div'
        );

        $containerStyle = ShortcodesEffects::print_all_css(
            array('spacing_css' => 'display:none;'),
            'spacing_css',
            $selector . '  > .bd-container-inner > .container-fluid:after'
        );

        $style_tag .= '<style>' . $rowStyle . $colStyle . $containerStyle . '</style>';
    }

    if (ShortcodesUtility::getBool($atts['auto_height'])) {
        $row_classes[] = 'bd-row-flex';

        if($atts['vertical_align']) {
            $row_classes[] = 'bd-row-align-' . $atts['vertical_align'];
        }

        $heightStyle = ShortcodesEffects::print_all_css(
            array('css' => 'height:100%;'),
            'css',
            $selector . ' > .bd-container-inner > .container-fluid, ' .
            $selector . ' > .bd-container-inner > .container-fluid > .row'
        );

        $style_tag .= '<style>' .
                $heightStyle .
            '</style>';
    }

    $_spacingPaddingLeft = ShortcodesEffects::css_prop($atts, "padding-left", '', 'spacing_');
    $_margins = '';
    if (($atts['add_margins'] !== 'auto' && ShortcodesUtility::getBool($atts['add_margins'])) ||
        ($atts['add_margins'] === 'auto' && ($_spacingPaddingLeft === '0' || $_spacingPaddingLeft === '0px'))
    ) {
        $_margins = 'margin-left: 0!important; margin-right: 0!important;';
    }
    $style_tag .= '<style>' .
            $selector . ' > .bd-container-inner > .container-fluid {padding-left: 0; padding-right: 0;' . $_margins . '}' .
        '</style>';

    list($content, $inner_styles) = ShortcodesUtility::processResult($content);
    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <?php echo $style_tag; ?>
        <?php echo $inner_styles; ?>
        <div class="<?php echo implode(' ', $classes); ?> bd-columns bd-no-margins">
            <div class="bd-container-inner">
                <div class="container-fluid">
                    <div class="<?php echo implode(' ', $row_classes) ?>">
                        <!--{content}-->
                            <?php echo $content; ?>
                        <!--{/content}-->
                    </div>
                </div>
            </div>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ob_get_clean();
}

ShortcodesUtility::addShortcode('row', 'themler_shortcode_columns');
ShortcodesUtility::addShortcode('columns', 'themler_shortcode_columns');
?>
<?php

// Parallax Background
function themler_shortcode_parallax_background($atts, $content='', $tag) {
    $atts = ShortcodesUtility::atts(array(), $atts);

    $id = ShortcodesEffects::init('bd-parallax-bg', $atts, ShortcodesEffects::HTML_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);

    list($style_css, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts);

    $target_control = ShortcodesEffects::target_control($id);
    $target_selector =  $target_control['selector'];

    $style_css .= "<style>.bd-parallax-bg-effect > $target_selector {
            background-color: transparent;
            z-index: 0;
        }</style>";

    ob_start();
?>
    <!--[<?php echo $tag ?>]-->
        <?php echo $style_css; ?>
        <div class="<?php echo $additional_classes ?> bd-parallax-bg-effect" data-control-selector="<?php echo $target_selector ?>">
            <!--{content}-->
                <?php echo $content ?>
            <!--{/content}-->
        </div>
    <!--[/<?php echo $tag ?>]-->
<?php
    return ob_get_clean();
}

ShortcodesUtility::addEffectShortcode('parallax_background', 'themler_shortcode_parallax_background');
?>
<?php

// Ribbon
function themler_shortcode_ribbon($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'end_size' => '',
        'ribbon_fold_color' => '',
        'ribbon_end_color' => '',
        'stitching' => true,
        'stitching_color' => '',
        'shadow' => false
    ), $atts, array('', 'container_'));

    $id = ShortcodesEffects::init('', $atts);

    $ribbon_fold_color = ShortcodesUtility::escape($atts['ribbon_fold_color']);
    $ribbon_end_color = ShortcodesUtility::escape($atts['ribbon_end_color']);
    $stitches_size = ShortcodesUtility::getBool($atts['stitching']) ? 1 : 0;
    $stitching_color = ShortcodesUtility::escape($atts['stitching_color']);
    $end_size = $atts['end_size'] ? ShortcodesUtility::escape($atts['end_size']) : '0px';

    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, '!background'));
    list($background_style_tag, ) = ShortcodesEffects::css($id, ShortcodesEffects::filter($atts, 'background'), '', "$selector .ribbon-inner");

    list($container_style_tag, $container_additional_class, $container_selector) = ShortcodesEffects::css($id, $atts, 'container_');

    $classes = array($additional_class, 'bd-ribbon', 'bd-ribbon-core');
    if (ShortcodesUtility::getBool($atts['shadow']))
        $classes[] = 'ribbon-shadow';

    $style_tag = "
        $style_tag
        <style>
            $selector {
                font-size: $end_size !important;
            }

            $selector .ribbon-inner:before,
            $selector .ribbon-inner:after {
                border: 1.5em solid $ribbon_end_color;
            }

            $selector .ribbon-inner .ribbon-content:before,
            $selector .ribbon-inner .ribbon-content:after {
                border-color: $ribbon_fold_color transparent transparent transparent;
            }

            $selector .ribbon-inner .ribbon-stitches-top {
                border-top: {$stitches_size}px dashed $stitching_color;
                top: 2px;
            }

            $selector .ribbon-inner .ribbon-stitches-bottom {
                border-top: {$stitches_size}px dashed $stitching_color;
                bottom: 2px;
            }

            $selector .ribbon-inner:before {
                border-left-color: transparent;
                border-right-width: 1.5em;
            }

            $selector .ribbon-inner:after {
                border-left-width: 1.5em;
                border-right-color: transparent;
            }
        </style>";

    return "<!--[$tag]-->" . $style_tag . $background_style_tag . $container_style_tag .
                '<div class="'.implode(' ', $classes).'">' .
                    '<div class="ribbon-inner">' .
                        '<div class="ribbon-stitches-top"></div>' .
                        '<strong class="ribbon-content">' .
                            '<div class="' . substr($container_selector, 1) . ' bd-content-element">' .
                                '<!--{content}-->' .
                                    $content .
                                '<!--{/content}-->' .
                             '</div>' .
                        '</strong>' .
                        '<div class="ribbon-stitches-bottom"></div>' .
                    '</div>' .
                '</div>' .
            "<!--[/$tag]-->";
}

ShortcodesUtility::addShortcode('ribbon', 'themler_shortcode_ribbon');
?>
<?php

// Section
function themler_shortcode_section($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(ShortcodesEffects::tagsStylesAtts(array(
        'title' => '',
        'id' => '',
	)), $atts);

    $sect_id = $atts['id'];
    $title = $atts['title'];

    $id = ShortcodesEffects::init('bd-section', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts);

	ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
		<?php echo $style_tag; ?>
		<section<?php if ($sect_id) echo " id=$sect_id"; ?> class="<?php echo $additional_class; ?> bd-tagstyles" data-section-title="<?php echo $title; ?>">
			<div class="bd-container-inner bd-margins clearfix">
				<!--{content}-->
					<?php echo $content; ?>
				<!--{/content}-->
			</div>
		</section>
	<!--[/<?php echo $tag; ?>]-->
<?php
	return ob_get_clean();
}

ShortcodesUtility::addShortcode('section', 'themler_shortcode_section');
?>
<?php

// Separator
function themler_shortcode_separator($atts, $imageHtml = '', $tag) {
    $atts = ShortcodesUtility::atts(
        array(
            'content_type' => 'none',
            'text' => '',
            'text_tag' => 'span',
            'align' => 'center',
            'content_align' => 'center',

            'href' => '',
            'target' => '',
            'screen_tip' => '',
            'alt' => '',

            'image' => '',
            'image_style' => '',
            'image_type' => '',
            'image_responsive' => '',

            'button_type' => 'default',
            'button_style' => '',
            'button_size' => '',
            'button_icon' => '',
            'button_picture' => '',
            'button_icon_hovered' => '',
            'button_picture_hovered' => ''
        ),
        $atts,
        array('', 'image_', 'content_', 'line_', 'button_', 'button_icon_', 'button_icon_hovered_')
    );

    // hack with prefix
    $atts['image_image'] = $atts['image'];
    $atts['image_image_style'] = $atts['image_style'];
    $atts['image_href'] = $atts['href'];
    $atts['image_alt'] = $atts['alt'];

    $id = ShortcodesEffects::init('bd-separator', $atts, true);
    list($controlStyles, $controlClasses, $controlSelector) = ShortcodesEffects::css($id, $atts);

    $controlClasses .= ' bd-separator-' . $atts['align'];
    $controlClasses .= ' bd-separator-content-' . $atts['content_align'];

    $contentHtml = '';
    $innerContentStyles = '';
    switch ($atts['content_type']) {
        case 'text':
            $contentHtml = "<${atts['text_tag']} class=\"bd-content-element\">" . $atts['text'] . "</${atts['text_tag']}>";
            break;
        case 'image':
            $contentResult = themler_shortcode_image($atts, '', '', array(
                'id' => $id,
                'prefix' => 'image_'
            ));
            $contentHtml = $contentResult['html'];
            $innerContentStyles = $contentResult['css'];
            break;
        case 'button':
            $contentResult = themler_shortcode_button($atts, $atts['text'], '', array(
                'id' => $id,
                'prefix' => 'button_'
            ));
            $contentHtml = $contentResult['html'];
            $innerContentStyles = $contentResult['css'];
            break;
    }

    $contentHtml = preg_replace('/<!--[\s\S]*?-->/', '', $contentHtml);

    list($contentStyles, $contentClass, ) = ShortcodesEffects::css($id, $atts, 'content_');
    list($lineStyles, ) = ShortcodesEffects::css(
        $id, ShortcodesEffects::filter($atts, '!width', 'line_'), 'line_',
        '{selector} .bd-separator-inner:before, {selector} .bd-separator-inner:after',
        str_replace('.', '', $controlSelector)
    );
    list($lineWidthStyles, ) = ShortcodesEffects::css(
        $id, ShortcodesEffects::filter($atts, 'width', 'line_'), 'line_',
        '{selector} .bd-separator-inner',
        str_replace('.', '', $controlSelector)
    );

    ob_start();
    ?>

    <!--[<?php echo $tag ?>]-->
    <?php echo $controlStyles ?>
    <?php if ($atts['content_type'] !== 'none'): ?>
        <?php echo $innerContentStyles ?>
        <?php echo $contentStyles ?>
    <?php endif ?>
    <?php echo $lineStyles ?>
    <?php echo $lineWidthStyles ?>

    <div class="<?php echo $controlClasses ?> clearfix">
        <div class="bd-container-inner">
            <div class="bd-separator-inner">
                <?php if ($atts['content_type'] !== 'none'): ?>
                    <div class="<?php echo $contentClass ?> bd-tagstyles bd-separator-content">
                        <?php echo $contentHtml ?>
                    </div>
                <?php endif ?>
                <!--{content}--><!--{/content}-->
            </div>
        </div>
    </div>
    <!--[/<?php echo $tag ?>]-->

    <?php
    return ob_get_clean();
}

ShortcodesUtility::addShortcode('separator', 'themler_shortcode_separator');
?>
<?php

// Slide
function themler_shortcode_slide($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'link' => '',
        'linktarget' => '',
        'title' => ''
    ), $atts);

    $link = ShortcodesUtility::escape($atts['link']);
    $linktarget = ShortcodesUtility::escape($atts['linktarget']);
    $title = ShortcodesUtility::escape($atts['title']);

    $id = ShortcodesEffects::init('', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts);

    $additional_class .= ' bd-slide item';

    global $theme_slides_count;
    if (!is_array($theme_slides_count) || !count($theme_slides_count)) {
        $theme_slides_count = array(0);
    }

    if ($theme_slides_count[count($theme_slides_count) - 1] === 0) {
        $additional_class .= ' active';
    }
    $theme_slides_count[count($theme_slides_count) - 1]++;

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <div class="<?php echo $additional_class; ?>"
             <?php if ($link) echo 'data-url="' . $link . '"'; ?>
             <?php if ($linktarget) echo 'data-target="' . $linktarget . '"'; ?>
             <?php if ($title) echo 'data-title="' . $title . '"'; ?>>
            <div class="bd-container-inner">
                <div class="bd-container-inner-wrapper">
                    <!--{content}-->
                        <?php echo $content; ?>
                    <!--{/content}-->
                </div>
            </div>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ShortcodesUtility::addResult(ob_get_clean(), $style_tag);
}
ShortcodesUtility::addShortcode('slide', 'themler_shortcode_slide');
?>
<?php

// Slider
function themler_shortcode_slider($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        // navigator (carousel):
        'interval' => 3000,
        'pause_on_hover' => 'yes',
        'navigator_wrap' => 'yes',
        'ride_on_start' => 'yes',
        'vertical_items' => 'no',
        'vertical_carousel' => 'no',
        'animation' => 'left',
        'navigator_style' => '',

        // indicators:
        'indicators_style' => '',

        // slider:
        'show_navigator' => 'yes',
        'show_indicators' => 'no',
        'indicators_wide' => 'no',
        'navigator_wide' => 'no',
        'slides_wide' => 'yes',
    ), $atts, array('', 'indicators_', 'navigator_'));

    $vertical_items = ShortcodesUtility::getBool($atts['vertical_items']);
    $vertical_carousel = ShortcodesUtility::getBool($atts['vertical_carousel']);
    $show_indicators = ShortcodesUtility::getBool($atts['show_indicators']);
    $show_navigator = ShortcodesUtility::getBool($atts['show_navigator']);
    $indicators_wide = ShortcodesUtility::getBool($atts['indicators_wide']);
    $navigator_wide = ShortcodesUtility::getBool($atts['navigator_wide']);
    $slides_wide = ShortcodesUtility::getBool($atts['slides_wide']);
    $interval = $atts['interval'];
    $pause_on_hover = ShortcodesUtility::getBool($atts['pause_on_hover']) ? 'hover' : '';
    $navigator_wrap = ShortcodesUtility::getBool($atts['navigator_wrap']) ? 'true' : 'false';
    $ride_on_start = ShortcodesUtility::getBool($atts['ride_on_start']) ? 'true' : 'false';
    $animation = ShortcodesUtility::escape($atts['animation']);

    $id = uniqid('carousel-');

    global $theme_slides_count;
    if (!is_array($theme_slides_count))
        $theme_slides_count = array();
    $theme_slides_count[] = 0;

    $sid = ShortcodesEffects::init('', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css($sid, $atts);
    $class = substr($selector, 1);

    list($navigator_style_tag, ) = ShortcodesEffects::css($sid, $atts, 'navigator_', "$selector .carousel-inner > .item");
    list($indicators_style_tag, $indicators_additional_class, $indicators_selector) = ShortcodesEffects::css($sid, $atts, 'indicators_');
    $navigator_class = ShortcodesStyles::getStyleClassname('Carousel', $atts['navigator_style']);
    $indicators_class = ShortcodesStyles::getStyleClassname('Indicators', $atts['indicators_style']);

    $slides_count = $theme_slides_count[count($theme_slides_count) - 1];
    $content = '<!--{content}-->' . $content . '<!--{/content}-->';
    $indicators = '<div class="bd-slider-indicators ' . $indicators_additional_class . '"><ol class="' . $indicators_class . '">';
    $indicators_arr = preg_split("/[\s]+/", $indicators_class);
    $indicators_class = join(".", $indicators_arr);
    for($i = 0; $i < $slides_count; $i++) {
        $indicators .= '<li>'
            .'<a class="' . (0 === $i ? ' active' : '') . '" href="#" data-target="#' . $id . '" data-slide-to="' . $i . '"></a>'
            .'</li> '; // note: indicators are space-separated
    }
    $indicators .= '</ol></div>';
    $navigator = <<<EOL
<div class="bd-left-button">
    <a class="$navigator_class">
        <span class="bd-icon"></span>
    </a>
</div>
<div class="bd-right-button">
    <a class="$navigator_class">
        <span class="bd-icon"></span>
    </a>
</div>
EOL;
    $result = '';
    if ($indicators_wide && $show_indicators) {
        $result .= $indicators;
    }

    if (!$slides_wide) {
        $result .= '<div class="bd-container-inner">';
    }

    if (!$indicators_wide && $show_indicators) {
        $result .= $indicators;
    }

    list($content, $inner_styles) = ShortcodesUtility::processResult($content);
    $result .= '<div class="bd-slides carousel-inner">' . $content . '</div>';

    if (!$navigator_wide && $show_navigator) {
        $result .= $navigator;
    }

    if (!$slides_wide) {
        $result .= '</div>';
    }

    if ($navigator_wide && $show_navigator){
        $result .= $navigator;
    }

    $_indicators_atts = ShortcodesEffects::filter($atts, 'vertical-align', 'indicators_');
    $indicators_style_tag .= "<style>$indicators_selector:before{" . $_indicators_atts['indicators_css'] . "}</style>";

    if ($animation) {
        $additional_class .= " bd-carousel-$animation";
    }

    list($border_radius_style, ) = ShortcodesEffects::css($sid, ShortcodesEffects::filter($atts, 'border-radius'), '',
        '{selector} [class*="bd-slide"], {selector} .carousel-inner', $class);

    $style_tag = $style_tag . "\n" . $border_radius_style;

    if ($vertical_items) {
        $additional_class .= ' bd-vertical-items';

        if ($vertical_carousel) {
            $additional_class .= ' bd-vertical-arrows';
        }
    }

    if (($duration = ShortcodesEffects::css_prop($atts, 'transition-duration', '', 'navigator_')) &&
            preg_match('/(\d+)(\w*)$/', $duration, $matches)) {

        $durationValue = (int) $matches[1];
        $durationMultiplier = $matches[2] === 's' ? 1000 : 1;

        $interval = $durationValue * $durationMultiplier + (int) $interval;
    }

    if (!$slides_wide) {
        $style_tag .= "<style>$selector .bd-slide > .bd-container-inner {
            max-width: none;
        }</style>";
    }

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <?php echo $style_tag . ((!$indicators_wide && $show_indicators) ? $indicators_style_tag : '') . $navigator_style_tag; ?>
        <?php echo $inner_styles; ?>
        <div id="<?php echo $id; ?>" class="bd-slider bd-no-margins <?php echo $additional_class; ?> carousel slide">
            <?php echo $result; ?>
            <script type="text/javascript">
                if ('undefined' !== typeof initSlider) {
                    initSlider(
                        '#<?php echo $id; ?>',
                        {
                            leftButtonSelector: 'bd-left-button',
                            rightButtonSelector: 'bd-right-button',
                            navigatorSelector: '<?php echo ($navigator_class ? '.' . $navigator_class : ''); ?>',
                            indicatorsSelector: '<?php echo ($indicators_class ? '.' . $indicators_class : ''); ?>',
                            carouselInterval: <?php echo $interval; ?>,
                            carouselPause: '<?php echo $pause_on_hover; ?>',
                            carouselWrap: <?php echo $navigator_wrap; ?>,
                            carouselRideOnStart: <?php echo $ride_on_start; ?>
                        }
                    );
                }
            </script>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ob_get_clean();
}
ShortcodesUtility::addShortcode('slider', 'themler_shortcode_slider');
?>
<?php

// SmoothScroll
function themler_shortcode_smooth_scroll($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(
        'animation_time' => 250,
    ), $atts);

    $id = ShortcodesEffects::init('bd-smoothscroll', $atts, ShortcodesEffects::HTML_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);

    list($style_css, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts);

    ob_start();
?>
    <!--[<?php echo $tag ?>]-->
        <?php echo $style_css; ?>
        <div class="<?php echo $additional_classes ?>" data-smooth-scroll data-animation-time="<?php echo $atts['animation_time']; ?>">
            <!--{content}-->
                <?php echo $content ?>
            <!--{/content}-->
        </div>
    <!--[/<?php echo $tag ?>]-->
<?php
    return ob_get_clean();
}

ShortcodesUtility::addEffectShortcode('smooth_scroll', 'themler_shortcode_smooth_scroll');
?>
<?php

// SocialIcon
function themler_shortcode_social_icon($atts, $content, $tag) {
    $atts = ShortcodesUtility::atts(array(
        'type' => '',
        'permalink_url' => '',
        'share_title' => '',
        'share_image_url' => '',
        'share_type' => 'static',

        'picture' => '',
        'icon' => '',
    ), $atts, array('', 'icon_'));

    $permalink_url = ShortcodesUtility::escape($atts['permalink_url']);
    $share_title = ShortcodesUtility::escape($atts['share_title']);
    $share_image_url = ShortcodesUtility::escape($atts['share_image_url']);
    $share_type = ShortcodesUtility::escape($atts['share_type']);
    $picture = ShortcodesUtility::escape($atts['picture']);
    $icon = ShortcodesUtility::escape($atts['icon']);
    $type = ShortcodesUtility::escape($atts['type']);

    if ($share_type === 'dynamic' && function_exists('themler_get_page_url')) {
        $permalink_url = themler_get_page_url();
        $share_title = themler_get_page_title();
    }

    $permalink_url = urlencode($permalink_url);
    $share_title = urlencode($share_title);
    $share_image_url = urlencode($share_image_url);

    $url_patterns = array(
        'fb' => '//www.facebook.com/sharer.php?u={permalinkURL}',
        'tw' => '//twitter.com/share?url={permalinkURL}&amp;text={shareTitle}',
        'gl' => '//plus.google.com/share?url={permalinkURL}',
        'pi' => '//pinterest.com/pin/create/button/?url={permalinkURL}&amp;media={shareImageUrl}&amp;description={shareTitle}',
        'li' => '//linkedin.com/shareArticle?title={shareTitle}&amp;mini=true&amp;url={permalinkURL}',
        'in' => '//instagram.com/{permalinkURL}',
        'dr' => '//dribbble.com/{permalinkURL}',
        'tm' => '//{permalinkURL}.tumblr.com',
        'fl' => '//flickr.com/photos/{permalinkURL}',
        'vk' => '//vk.com/share.php?url={permalinkURL}&title={shareTitle}&image={shareImageUrl}',
        'be' => '//behance.net/{permalinkURL}'
    );

    $url_pattern = isset($url_patterns[$type]) ? $url_patterns[$type] : '';
    $url = str_replace(array('{permalinkURL}', '{shareTitle}', '{shareImageUrl}'), array($permalink_url, $share_title, $share_image_url), $url_pattern);

    $id = ShortcodesEffects::init('', $atts);
    list($style_tag, $additional_classes, $selector) = ShortcodesEffects::css($id, $atts);
    $additional_classes .= ' bd-socialicon';

    $style_tag .= "
        <style>$selector {
            float: left;
        }</style>";

    $style_tag .= themler_shortcodes_icon_state_style($id, array(
        'picture' => $picture,
        'icon' => $icon,
        'selector' => "$selector span:first-child",
        'atts' => $atts,
        'icon_prefix' => 'icon_'
    ));

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <?php echo $style_tag; ?>
        <a target="_blank" class="<?php echo $additional_classes; ?>" href= "<?php echo $url; ?>">
            <span class="bd-icon"></span><span><!--{content}--><?php echo $content; ?><!--{/content}--></span>
        </a>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ob_get_clean();
}
ShortcodesUtility::addShortcode('social_icon', 'themler_shortcode_social_icon');

// SocialIcons
function themler_shortcode_social_icons($atts, $content, $tag) {
    $atts = ShortcodesUtility::atts(array(

    ), $atts, array('', 'passive_', 'hovered_'));

    $id = ShortcodesEffects::init('', $atts);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css($id, $atts);

    list($passive_style,) = ShortcodesEffects::css($id, $atts, 'passive_', "$selector .bd-socialicon");
    if (!ShortcodesEffects::css_prop($atts, 'background-image', '', 'passive_') && ShortcodesEffects::css_prop($atts, 'background-color', '', 'passive_')) {
        $passive_style .= "<style>$selector .bd-socialicon {background-image: none;}</style>";
    }

    list($hovered_style,) = ShortcodesEffects::css($id, $atts, 'hovered_', "$selector .bd-socialicon:hover");
    if (!ShortcodesEffects::css_prop($atts, 'background-image', '', 'hovered_') && ShortcodesEffects::css_prop($atts, 'background-color', '', 'hovered_')) {
        $hovered_style .= "<style>$selector .bd-socialicon:hover {background-image: none;}</style>";
    }

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <?php echo $style_tag . $passive_style . $hovered_style; ?>
        <div class="<?php echo $additional_class; ?>">
            <!--{content}-->
                <?php echo $content; ?>
            <!--{/content}-->
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ob_get_clean();
}
ShortcodesUtility::addShortcode('social_icons', 'themler_shortcode_social_icons');
?>
<?php

// Spacer
function themler_shortcode_spacer($atts, $content = '', $tag) {
    $atts = ShortcodesUtility::atts(array(), $atts);

    $id = ShortcodesEffects::init('bd-spacer', $atts, true);
    list($style_css, $additional_classes, ) = ShortcodesEffects::css($id, $atts);

    ob_start();
    ?>

    <!--[<?php echo $tag ?>]-->
    <?php echo $style_css ?>
    <div class="<?php echo $additional_classes ?> clearfix">
        <!--{content}--><!--{/content}-->
    </div>
    <!--[/<?php echo $tag ?>]-->

    <?php
    return ob_get_clean();
}

ShortcodesUtility::addShortcode('spacer', 'themler_shortcode_spacer');
?>
<?php
// TextBlock
function themler_shortcode_text_block($atts, $content='') {
    $atts = ShortcodesUtility::atts(array(
        'tag' => 'div'
    ), $atts);

    $tag = $atts['tag'];

    $id = ShortcodesEffects::init('', $atts);
    $content = ShortcodesUtility::doShortcode($content);

    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts, '', '{selector}', '', ($tag === 'p' || $tag === 'div' ? '' : $tag) . '{selector} {tag}');

    $additional_class .= ' bd-content-element';
    ob_start();
?>
    <!--[text]-->
        <?php echo $style_tag; ?>
        <<?php echo $tag; ?> class="<?php echo $additional_class; ?>">
            <!--{content}-->
                <?php echo $content; ?>
            <!--{/content}-->
        </<?php echo $tag; ?>>
    <!--[/text]-->
<?php
    return ob_get_clean();
}
ShortcodesUtility::addShortcode('text', 'themler_shortcode_text_block');
?>
<?php
// TextGroup
function themler_shortcode_text_group($atts, $content='') {
    $atts = ShortcodesUtility::atts(array(
        'image' => '',
        'image_link' => '',
        'image_target' => '',
        'image_screen_tip' => '',
        'image_alt' => '',
        'image_position' => 'left',
        'header' => '',
        'header_tag' => 'h4',
        'responsive' => 'xs',

        'style' => '',

        'image_style' => '',
        'image_type' => '',
        'image_responsive' => '',
        // deprecated attributes:
        'image_width' => '',
        'image_height' => '',

        'header_css' => ''
    ), $atts, array('', 'image_'));

    // hack with prefix
    $atts['image_image'] = $atts['image'];
    $atts['image_image_style'] = $atts['image_style'];

    $header_tag = $atts['header_tag'];
    $responsive = $atts['responsive'];
    $header = $atts['header'];
    $image = $atts['image_image'];
    $image_link = $atts['image_link'];
    $image_position = strtolower($atts['image_position']);

    $image_positions = array('left' => 'pull-left', 'right' => 'pull-right', 'top' => 'top', 'bottom' => 'bottom', 'middle' => 'middle');
    $headers = array('h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6');
    $header_tag = array_key_exists(strtolower($header_tag), $headers) ? $headers[strtolower($header_tag)] : 'h4';

    $id = ShortcodesEffects::init('', $atts);
    $content = ShortcodesUtility::doShortcode($content, true, true);
    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts);

    $imageHtml = '';
    $imageStyles = '';
    if ($image !== '') {
        $additionalClass = array();
        $additionalImageAtts = array();

        if ($image_position === 'left' || $image_position === 'right') {
            $additionalClass[] = $image_positions[$image_position];
        }
        if (!$image_link) {
            $additionalClass[] = 'media-object';
        }
        if ($responsive && $responsive !== 'none') {
            $additionalClass[] = 'bd-media-' . $responsive;
        }
        if ($atts['image_width']) {
            $additionalImageAtts[] = 'width="' . $atts['image_width'] . '"';
        }
        if ($atts['image_height']) {
            $additionalImageAtts[] = 'height="' . $atts['image_height'] . '"';
        }

        $imageResult = themler_shortcode_image($atts, '', '', array(
            'id' => $id,
            'prefix' => 'image_',
            'additionalClass' => join(' ', $additionalClass),
            'additionalImageAtts' => join(' ', $additionalImageAtts)
        ));
        $imageHtml = $imageResult['html'];
        $imageHtml = preg_replace('/<!--[\s\S]*?-->/', '', $imageHtml);
        $imageStyles = $imageResult['css'];
    }

    $additional_class .= ' ' . ShortcodesStyles::getStyleClassname('Block', $atts['style']);

    ob_start();
?>
    <!--[text_group]-->
        <?php echo $style_tag . ($image !== '' ? $imageStyles : '') ?>
        <div class="<?php echo $additional_class ?>">
            <div class="bd-container-inner">
                <div class="media">
                    <?php if ($image_position === 'top' || $image_position === 'left' || $image_position === 'right') echo $imageHtml ?>
                    <div class="media-body">
                        <?php if ($header_tag && $header): ?>
                            <<?php echo $header_tag; ?><?php if ($atts['header_css']) echo '  style="'.ShortcodesUtility::escape($atts['header_css']).'"'; ?> class="media-heading bd-blockheader bd-tagstyles bd-content-element"><?php echo $header; ?></<?php echo $header_tag; ?>>
                        <?php endif; ?>
                        <?php if ($image_position === 'middle') echo $imageHtml ?>
                        <div class="bd-blockcontent bd-tagstyles bd-content-element">
                            <!--{content}-->
                                <?php echo $content; ?>
                            <!--{/content}-->
                        </div>
                    </div>
                    <?php if ($image_position === 'bottom') echo $imageHtml ?>
                </div>
            </div>
        </div>
    <!--[/text_group]-->
<?php
    return ob_get_clean();
}
ShortcodesUtility::addShortcode('text_group', 'themler_shortcode_text_group');
?>
<?php

// TextureOverlay
function themler_shortcode_overlay($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(array(), $atts);

    $id = ShortcodesEffects::init('bd-textureoverlay', $atts, ShortcodesEffects::CSS_EFFECT);
    $content = ShortcodesUtility::doShortcode($content);
    list($style_tag, $additional_classes, $selector) = ShortcodesEffects::css(
        $id,
        ShortcodesEffects::filter($atts, 'background,opacity,border-radius'),
        '',
        '{selector}:before'
    );

    $targetControl = ShortcodesEffects::target_control($id);

    $content = ShortcodesEffects::addClassesAndAttrs(
        $content,
        $targetControl,
        array('bd-textureoverlay', $additional_classes),
        array()
    );

    return "<!--[$tag]-->$style_tag<!--{content}-->" . $content . "<!--{/content}--><!--[/$tag]-->";
}

ShortcodesUtility::addEffectShortcode('overlay', 'themler_shortcode_overlay');
?>
<?php

// UPageSection
function themler_shortcode_upage_section($atts, $content = '', $tag = '') {
    $atts = ShortcodesUtility::atts(ShortcodesEffects::tagsStylesAtts(array(
        'id' => '',
	)), $atts);
	$post_id = $atts['id'];

    $id = ShortcodesEffects::init('bd-upage-section', $atts);
    $content = '';
	if (is_numeric($post_id)) {
		$post = get_post($post_id);
		if ($post->post_type === 'upage_section') {
			$content = $post->post_content;
		}
	}
    list($style_tag, $additional_class) = ShortcodesEffects::css($id, $atts);

	ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
		<?php echo $style_tag; ?>

			<!--{content}-->
				<?php echo $content; ?>
			<!--{/content}-->

	<!--[/<?php echo $tag; ?>]-->
<?php
	return ob_get_clean();
}

if (ShortcodesUtility::isPreview()) {
	ShortcodesUtility::addShortcode('upage_section', 'themler_shortcode_upage_section');
}
?>
<?php

// Video
function themler_shortcode_video($atts, $content = '', $tag = '') {
    $default_types = function_exists('wp_get_video_extensions') ? wp_get_video_extensions() : array();
    $default_types[] = 'link'; // deprecated attribute
    $defaults_atts = array(
        'src' => '',
        'poster'   => '',
        'loop' => false,
        'autoplay' => false,
        'preload'  => 'metadata',
		'width'    => 640,
		'height'   => 360,

        'image_style' => '',
        'title' => true,
        'control_bar' => false,
        'show_control_bar' => '',
    );

	foreach ( $default_types as $type ) {
		$defaults_atts[$type] = '';
	}
    $atts = ShortcodesUtility::atts($defaults_atts, $atts);

    $src = '';
    if (!empty($atts['src'])) {
        $src = ShortcodesUtility::escape($atts['src']);

    } else {
        foreach ($default_types as $type) {
            if (!empty($atts[$type])) {
                $src = ShortcodesUtility::escape($atts[$type]);
            }
	    }
    }

    $width = intval($atts['width']);
    $height = intval($atts['height']);

    $autoplay = ShortcodesUtility::getBool($atts['autoplay']);
    $loop = ShortcodesUtility::getBool($atts['loop']);
    $title = ShortcodesUtility::getBool($atts['title']);
    $control_bar = ShortcodesUtility::getBool($atts['control_bar']);
    $show_control_bar = ShortcodesUtility::escape($atts['show_control_bar']);

    $id = ShortcodesEffects::init('', $atts);
    list($style_tag, $additional_class, $selector) = ShortcodesEffects::css($id, $atts);

    $additional_image_class = ShortcodesStyles::getStyleClassname('Image', $atts['image_style']);

    if (strpos($src, 'youtube.com/watch') !== false) {
        $tmp = explode('&', $src);
        list(, $video_id) = explode('=', isset($tmp[0]) ? $tmp[0] : '');
    } else {
        $video_id = end(explode('/', $src));
    }

    if (preg_match('/[&,?]t=((\d+h\d+m\d+s)|(\d+h\d+m)|(\d+h\d+s)|(\d+m\d+s)|(\d+[h,m,s]))$/', $src)){
        $h = $m = $s = $secCounter = 0;

        if (preg_match('/\d+h/', $src, $matches)){
            $h = intval($matches[0]);
        }

        if (preg_match('/\d+m/', $src, $matches)){
            $m = intval($matches[0]);
        }

        if (preg_match('/\d+s/', $src, $matches)){
            $s = intval($matches[0]);
        }

        $secCounter = 60 * 60 * $h + 60 * $m + $s;

        if (!is_nan($secCounter)){
            $timeStart = $secCounter;
        }
    }
    else if (preg_match('/[&,?]t=\d+$/', $src, $partWithMsc)){
        $secCounter = 0;

        if (preg_match('/\d+/', $partWithMsc[0], $results)){
            $secCounter = intval($results[0]);
        };

        if (!is_nan($secCounter)){
            $timeStart = $secCounter;
        }
    }
    else {
        $timeStart = '';
    }

    $iframe_atts = array();

    $iframe_atts[] = 'data-autoplay="' . ($autoplay ? 'true' : 'false') . '"';
    $iframe_atts[] = 'class="embed-responsive-item"';
    $iframe_atts[] = 'frameborder="0"';
    $iframe_atts[] = 'allowfullscreen';

    $src_params = array();
    $src_params[] = 'loop=' . ($loop ? 1 : 0);

    if (strpos($src, 'youtube') !== false || strpos($src, 'youtu.be') !== false) {

        $src_params[] = 'playlist=' . ($loop ? $video_id : 'null');
        $src_params[] = 'showinfo=' . ($title ? 1 : 0);
        $src_params[] = 'theme=' . ($control_bar ? 'light' : 'dark');
        $src_params[] = 'autohide=' . ($show_control_bar === 'autohide' ? 1 : 0);
        $src_params[] = 'controls=' . ($show_control_bar === 'hide' ? 0 : 1);
        $src_params[] = 'start='. $timeStart;

        $src = 'https://www.youtube.com/embed/' . $video_id . '?' . implode('&', $src_params);
    } else if (strpos($src, 'vimeo') !== false) {
        $iframe_atts[] = 'webkitallowfullscreen';
        $iframe_atts[] = 'mozallowfullscreen';

        $src_params[] = 'title=' . ($title ? 1 : 0);
        $src_params[] = 'color=' . ($control_bar ? 'ffffff' : '00adef');

        $src = 'https://player.vimeo.com/video/' . $video_id . '?' . implode('&', $src_params);
    }
    $iframe_atts[] = 'src="' . $src . '"';

    ob_start();
?>
    <!--[<?php echo $tag; ?>]-->
        <style><?php echo $selector; ?> {
            max-width: <?php echo $width; ?>px;
            max-height: <?php echo $height; ?>px;
            display: block;
        }</style>
        <?php echo $style_tag; ?>
        <?php if (!empty($atts['css']['display'])): ?>
        <style>
            <?php echo $selector ?> {
                display: <?php echo $atts['css']['display']; ?>
            }
        </style>
        <?php endif ?>
        <div class="<?php echo $additional_image_class . ' ' . $additional_class; ?> bd-own-margins">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe <?php echo implode(' ', $iframe_atts); ?>>
                    <!--{content}--><!--{/content}-->
                </iframe>
            </div>
        </div>
    <!--[/<?php echo $tag; ?>]-->
<?php
    return ob_get_clean();
}

ShortcodesUtility::addShortcode('video', 'themler_shortcode_video');
?>