<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('admin_init', 'theme_admin_init', 1000);
add_action('save_post',  'theme_save_post_template', 1000);
add_action('body_class', 'theme_body_class', 1000);
add_filter('single_template', 'theme_post_template' );

function theme_admin_init(){
    add_meta_box('theme_select_post_template', __('Post Template', 'default'), 'theme_select_post_template', 'post', 'side', 'default');
}

function theme_select_post_template( $post )
{
    $post_ID = $post->ID;

    $template = get_post_meta( $post_ID, 'theme_post_template', true );
    if (empty($template))
        $template = 'single.php';
    // Render the template
?>
    <label class="screen-reader-text" for="theme_post_template"><?php _e('Post Template', 'default') ?></label>
    <select name="theme_post_template" id="theme_post_template">
        <option value='default'><?php _e('Default Template', 'default'); ?></option>
        <?php page_template_dropdown($template); ?>
    </select>

<?php
}

function is_post_template($template = '') {
    if (!is_single()) {
        return false;
    }

    global $wp_query;

    $post = $wp_query->get_queried_object();
    $post_template = get_post_meta( $post->ID, 'theme_post_template', true );

    // We have no argument passed so just see if a page_template has been specified
    if ( empty( $template ) ) {
        if (!empty( $post_template ) ) {
            return true;
        }
    } elseif ( $template == $post_template) {
        return true;
    }

    return false;
}

function theme_body_class($classes) {
    if ( ! is_post_template() )
        return $classes;
    global $wp_query;
    // We distrust the global $post object, as it can be substituted in any
    // number of different ways.
    $post = $wp_query->get_queried_object();
    $post_template = get_post_meta( $post->ID, 'theme_post_template', true );
    $classes[] = 'post-template';
    $classes[] = 'post-template-' . str_replace( '.php', '-php', $post_template );
    return $classes;
}

function theme_save_post_template($post_ID){
    $template = (string) @ $_POST[ 'theme_post_template' ];

    if (empty($template)) return;

    delete_post_meta( $post_ID, 'theme_post_template' );
    if ('single.php' == $template) return;

    add_post_meta( $post_ID, 'theme_post_template', $template );
}

function theme_post_template($template){
    global $wp_query;
    $post_ID = $wp_query->post->ID;

    $template_file = get_post_meta( $post_ID, 'theme_post_template', true );
    if (!$template_file)
        return $template;

    if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $template_file ) )
        return get_stylesheet_directory() . DIRECTORY_SEPARATOR . $template_file;
    // If there's a tpl in the parent of the current child theme
    else if ( file_exists( get_template_directory() . DIRECTORY_SEPARATOR . $template_file ) )
        return get_template_directory() . DIRECTORY_SEPARATOR . $template_file;

    return $template;

}