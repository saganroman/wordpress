<?php
function theme_blog_3() {
    global $post;
    $need_reset_query = false;
    if (is_page()) {
        $page_id = get_queried_object_id();
        if (theme_get_meta_option($page_id, 'theme_show_categories')) {
            $need_reset_query = true;
            if (get_query_var('paged')) {
                $paged = get_query_var('paged');
            } else {
                $paged = get_query_var('page', 1);
            }
            query_posts(
                wp_parse_args(
                    'category_name=' . theme_get_meta_option($page_id, 'theme_categories'),
                    array(
                        'paged' => $paged
                    )
                )
            );
        }
    }

    if (!$need_reset_query && theme_is_preview()) {
        global $theme_current_template_info;
        if (isset($theme_current_template_info)) {
            $template_name = $theme_current_template_info['name'];
            $ids = theme_get_option('theme_template_' . $template_name . '_query_ids');
            if ($ids && !empty($theme_current_template_info['allow_sample_posts'])) {
                $need_reset_query = true;
                $ids = explode(',', $ids);

                query_posts(array(
                    'post_type' => 'any',
                    'post__in' => $ids,
                    'paged' => get_query_var('paged', get_query_var('page', 1)),
                ));
            }
        }
    }
?>
    <div class=" bd-blog-3">
        <div class="bd-container-inner">
        
            <?php
    if ( is_home() && 'page' == get_option('show_on_front') && get_option('page_for_posts') ){
        $blog_page_id = (int)get_option('page_for_posts');
        $title = '<a href="' . get_permalink($blog_page_id) . '" rel="bookmark" title="' . strip_tags(get_the_title($blog_page_id)) . '">' . get_the_title($blog_page_id) . '</a>';
    echo '<h2 class=" bd-container-20 bd-tagstyles">' . $title . '</h2>';
}
?>
        
<?php
    if (have_posts()) { ?>
        <div class=" bd-grid-6 bd-margins">
            <div class="container-fluid">
                <div class="separated-grid row">
<?php
                    while (have_posts()) {
                        the_post();
                        do_action('theme_before_blog_post');

                        $id = theme_get_post_id();
                        $class = theme_get_post_class();
?>
                        
                        <div class="separated-item-21 col-md-12 ">
                        
                            <div class="bd-griditem-21">
                                <article id="<?php echo $id; ?>" class=" bd-article-4 clearfix hentry <?php echo $class; if (theme_is_preview()) echo ' bd-post-id-' . theme_get_the_ID(); ?>">
    <?php echo theme_get_post_thumbnail(array(
    
    'class' => ' bd-postimage-3',
    'img_class' => ' bd-imagestyles',
    'attributes' => '',
    'img_attributes' => '',
)); ?>
	
		<?php
if (!is_page() || theme_get_meta_option($post->ID, 'theme_show_page_title')) {
    $title = get_the_title();
    if (!is_singular()) {
        $title = sprintf('<a href="%s" rel="bookmark" title="%s">%s</a>', get_permalink($post->ID), strip_tags($title), $title);
    }
    if (!theme_is_empty_html($title)) {
?>
    <h2 class="entry-title  bd-postheader-4">
        <?php echo $title; ?>
    </h2>
<?php
    }
}
?>
	
		<?php
if (theme_is_preview() && is_singular()) {
    $editor_attrs = 'data-editable-id="post-' . theme_get_the_ID() . '"';
} else {
    $editor_attrs = '';
}
?>
<div class=" bd-postcontent-3 bd-tagstyles  entry-content bd-contentlayout-offset" <?php echo $editor_attrs; ?>>
    <?php echo(is_singular() ? theme_get_content() : theme_get_excerpt()); ?>
</div>
</article>
                                <?php
                                global $withcomments;
                                if (is_singular() || $withcomments) {  ?>
                                    <?php
    if (theme_get_option('theme_allow_comments')) {
        comments_template('/comments_3.php');
    }
?>
                                <?php } ?>
                            </div>
                        </div>
<?php
                        do_action('theme_after_blog_post');
                    }
?>
                </div>
            </div>
        </div>
<?php
    } else {
        theme_404_content();
    }
?>
        
        </div>
    </div>
<?php
    if($need_reset_query){
        wp_reset_query();
    }
}