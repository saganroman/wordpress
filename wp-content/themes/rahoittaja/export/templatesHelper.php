<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $theme_templates, $theme_template_types, $theme_templates_short_link;
$theme_templates = array();
$theme_templates_short_link = array();
$theme_template_types = array();


function _theme_add_preview_args($url) {
    $template = get_stylesheet() . '_preview';

    if (!has_action('setup_theme', 'preview_theme')) {
        $nonce = wp_create_nonce("preview-customize_$template");
        return add_query_arg(array('preview' => '1', 'theme' => $template, 'wp_customize' => 'on', 'nonce' => $nonce, 'original' => get_stylesheet()), $url);
    }
    return add_query_arg(array('preview' => '1', 'template' => $template, 'stylesheet' => $template, 'preview_iframe' => 1, 'TB_iframe' => 'true'), $url);
}

function register_template($name, $url, $add_preview_params = true, $is_absolute = false) {
    global $theme_templates, $theme_templates_short_link;
    if (isset($theme_templates[$name]))
        throw new Exception('Template ' . $name . ' is already exists');

    $path = $is_absolute ? '' : home_url();
    if ($add_preview_params) {
        $url = _theme_add_preview_args($path . $url);
    }
    $theme_templates_short_link[$name] = remove_query_arg(array('custom_template', 'custom_page', 'default'), $url);
    $theme_templates[$name] = $url;
}

function allow_template_duplicate($name) {
    $GLOBALS['theme_template_types'][$name] = true;
}

class PageTemplatesHelper {

    public $default_pattern = '([_a-z0-9-]+)';
    public $patterns;
    public $excludes;

    function __construct() {
        $this->patterns = array(
            '{id}'        => '(\d+)',
            '{slug}'      => $this->default_pattern,
            '{taxonomy}'  => $this->default_pattern,
            '{term}'      => $this->default_pattern,
            '{nicename}'  => $this->default_pattern,
            '{post_type}' => $this->default_pattern
        );

        $this->excludes = array();
        if ('page' == get_option('show_on_front'))
            $this->excludes[] = (int)get_option('page_on_front');  // front page
        $this->excludes[] = (int)get_option('page_for_posts'); // page for posts

        if (theme_woocommerce_enabled()) {
            $this->excludes[] = (int)wc_get_page_id('shop');
            $this->excludes[] = (int)wc_get_page_id('cart');
            $this->excludes[] = (int)wc_get_page_id('checkout');
            $this->excludes[] = (int)wc_get_page_id('myaccount');
            $this->excludes[] = (int)wc_get_page_id('edit_address');
            $this->excludes[] = (int)wc_get_page_id('view_order');
            $this->excludes[] = (int)wc_get_page_id('change_password');
            $this->excludes[] = (int)wc_get_page_id('logout');
        }
    }

    public function sample($arr, $default = null) {
        if (!is_array($arr) || empty($arr))
            return $default;
        $values = array_values($arr);
        return $values[0];
    }

    public function match($filename, $pattern) {
        $pattern = '#^' . str_replace(array_keys($this->patterns), array_values($this->patterns), $pattern) . '.php$#i';
        preg_match($pattern, $filename, $matches);
        if (empty($matches)) {
            return false;
        }
        return $matches;
    }

    public function validate_url($url) {
        if (!is_string($url))
            return false;
        if (preg_match('#^http#', $url))
            return $url;
        return home_url($url);
    }

    public function get_post($args = array()) {
        $args['numberposts'] = 1;
        $args['hierarchical'] = false;
        $posts = get_posts($args);
        return $this->sample($posts, false);
    }

    public function get_page($args = array()) {
        $args['numberposts'] = 1;
        $args['number'] = false;
        $pages = get_pages($args);
        return $this->sample($pages, false);
    }

    public function get_404() {
        return '/?page_id=1234567';
    }

    public function get_any_page_id() {
        $page = $this->get_page(array(
            'sort_column' => 'ID',
            'exclude' => implode(', ', $this->excludes)
        ));
        if (!empty($page))
            return $page->ID;
        return false;
    }

    public function get_any_page_url() {
        $id = $this->get_any_page_id();
        if (false === $id)
            return $this->get_404();
        return get_permalink($id);
    }

    public function get_any_post_id() {
        $post = $this->get_post();
        if (!empty($post))
            return $post->ID;
        return false;
    }

    public function get_any_post_url() {
        $id = $this->get_any_post_id();
        if (false === $id)
            return $this->get_404();
        return get_permalink($id);
    }

    public function get_sample_page_by_slug($slug) {
        $page = $this->get_post(array(
            'name' => $slug,
            'post_type'   => 'page',
            'orderby'     => 'name'
        ));
        if (empty($page))
            return false;
        return get_permalink($page->ID);
    }

    public function get_sample_page_by_id($id) {
        $post = get_post($id);
        if (empty($post))
            return false;
        return get_permalink($id);
    }

    public function get_sample_category_by_slug($slug) {
        $cat = get_category_by_slug($slug);
        if (empty($cat))
            return false;
        return get_category_link($cat->cat_ID);
    }

    public function get_sample_category_by_id($id) {
        $cat = get_category($id);
        if (empty($cat))
            return false;
        return get_category_link($cat->cat_ID);
    }

    public function get_sample_category() {
        $categories = get_categories(array('number' => 1));
        if (empty($categories))
            return false;
        return get_category_link($this->sample($categories)->cat_ID);
    }

    public function get_sample_tag_by_slug($slug) {
        $tags = get_tags(array(
            'slug' => $slug,
            'number' => 1
        ));
        if (empty($tags))
            return false;
        return get_tag_link($this->sample($tags)->term_id);
    }

    public function get_sample_tag_by_id($id) {
        $tag = get_tag($id);
        if (empty($tag))
            return false;
        return get_tag_link($tag->term_id);
    }

    public function get_sample_tag() {
        $tags = get_tags(array('number' => 1));
        if (empty($tags))
            return false;
        return get_tag_link($this->sample($tags)->term_id);
    }

    public function get_sample_author_by_id($id) {
        $post = $this->get_post(array(
            'author' => $id,
            'orderby' => 'name'
        ));
        if (empty($post))
            return false;
        return get_permalink($post->ID);
    }

    public function get_sample_author_by_name($name) {
        $user = get_user_by('slug', $name);
        if (empty($user))
            return false;
        return $this->get_sample_author_by_id($user->ID);
    }

    public function get_sample_single() {
        return $this->get_sample_post_by_type('');
    }

    public function get_sample_archive() {
        return $this->get_sample_page_date();
    }

    public function get_sample_archive_by_type($type) {
        if (false === $this->get_sample_post_by_type($type))
            return false;
        return get_post_type_archive_link($type);
    }

    public function get_sample_post_by_type($type) {
        $post = $this->get_post(array(
            'post_type'   => $type
        ));
        if (empty($post))
            return false;
        return get_permalink($post->ID);
    }

    public function get_sample_page_date() {
        $post = $this->get_post(array(
            'post_type'   => 'post',
            'orderby'     => 'date'
        ));
        if (empty($post))
            return false;
        return get_month_link(get_the_date('Y', $post->ID), get_the_date('m', $post->ID));
    }

    public function get_sample_taxonomy($taxonomy = '', $term = '') {
        $use_default = !empty($taxonomy) || !empty($term);

        if (empty($term)) {
            if (empty($taxonomy)) {
                $custom_taxonomies = get_taxonomies(array('public' => true, '_builtin' => false));
                if (empty($custom_taxonomies)) {
                    $taxonomy = 'category';
                } else {
                    $custom_taxonomies = array_keys($custom_taxonomies);
                    $taxonomy = $custom_taxonomies[0];
                }
            }
            $terms = get_terms($taxonomy, array('number' => 1));
            if (is_wp_error($terms)) {
                $term = 'term';
            } else if (!empty($terms)) {
                $term = $this->sample($terms)->slug;
            }
        }
        $link = get_term_link($term, $taxonomy);
        if (!is_wp_error($link))
            return $link;
        if ($use_default)
            return $this->get_sample_taxonomy();
        return false;
    }

    public function get_sample_page_search() {
        $id = $this->get_any_page_id();
        if (false === $id)
            $title = 'No pages found';
        else
            $title = get_the_title($id);
        return home_url() . '?s=' . urlencode($title);
    }

    private function _get_sample_post($filename, $post_meta_value) {
        $post = $this->get_post(array(
            'post_type'   => 'post',
            'meta_key'    => 'theme_post_template',
            'meta_value'  => $post_meta_value,
            'orderby'     => 'name'
        ));
        if (!empty($post)) {
            return add_query_arg(array('custom_page' => $filename), get_permalink($post->ID));
        }
        return add_query_arg(array('default' => 'true', 'custom_page' => $filename), $this->get_any_post_url());
    }

    public function get_sample_post($filename, $post_meta_value) {
        $url = $this->_get_sample_post($filename, $post_meta_value);
        return $this->validate_url($url);
    }

    private function _get_sample_page($filename) {

        $page = $this->get_post(array(
            'post_type'   => 'page',
            'meta_key'    => '_wp_page_template',
            'meta_value'  => $filename,
            'exclude' => implode(', ', $this->excludes),
            'orderby'     => 'name'
        ));
        $url = '';
        if (!empty($page)) {
            $url = get_permalink($page->ID);
        }
        elseif (false !== ($r = $this->match($filename, 'search')) && ($url = $this->get_sample_page_search())) {

        }
        elseif (false !== ($r = $this->match($filename, 'date')) && ($url = $this->get_sample_page_date())) {

        }
        elseif (false !== ($r = $this->match($filename, 'page-{slug}')) && ($url = $this->get_sample_page_by_slug($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'page-{id}')) && ($url = $this->get_sample_page_by_id($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'category-{slug}')) && ($url = $this->get_sample_category_by_slug($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'category-{id}')) && ($url = $this->get_sample_category_by_id($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'category')) && ($url = $this->get_sample_category())) {

        }
        elseif (false !== ($r = $this->match($filename, 'tag-{slug}')) && ($url = $this->get_sample_tag_by_slug($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'tag-{id}')) && ($url = $this->get_sample_tag_by_id($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'tag')) && ($url = $this->get_sample_tag())) {

        }
        elseif (false !== ($r = $this->match($filename, 'author-{nicename}')) && ($url = $this->get_sample_author_by_name($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'author-{id}')) && ($url = $this->get_sample_author_by_id($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'single-{post_type}')) && ($url = $this->get_sample_post_by_type($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'single')) && ($url = $this->get_sample_single())) {

        }
        elseif (false !== ($r = $this->match($filename, 'archive')) && ($url = $this->get_sample_archive())) {

        }
        elseif (false !== ($r = $this->match($filename, 'archive-{post_type}')) && ($url = $this->get_sample_archive_by_type($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'taxonomy-{taxonomy}-{term}')) && ($url = $this->get_sample_taxonomy($r[1], $r[2]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'taxonomy-{taxonomy}')) && ($url = $this->get_sample_taxonomy($r[1]))) {

        }
        elseif (false !== ($r = $this->match($filename, 'taxonomy')) && ($url = $this->get_sample_taxonomy())) {

        }

        if ($url) {
            return add_query_arg(array('custom_page' => $filename), $url);
        }

        return add_query_arg(array('default' => 'true', 'custom_page' => $filename), $this->get_any_page_url());
    }

    public function get_sample_page($filename) {
        $url = $this->_get_sample_page($filename);
        return $this->validate_url($url);
    }
}

global $pageTemplatesHelper;
$pageTemplatesHelper = new PageTemplatesHelper();

?>