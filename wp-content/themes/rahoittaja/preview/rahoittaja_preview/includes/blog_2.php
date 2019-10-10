<?php
function theme_blog_2() {
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
    <div class="data-control-id-1519 bd-blog-2">
        <div class="bd-container-inner">
        
            <?php
    if ( is_home() && 'page' == get_option('show_on_front') && get_option('page_for_posts') ){
        $blog_page_id = (int)get_option('page_for_posts');
        $title = '<a href="' . get_permalink($blog_page_id) . '" rel="bookmark" title="' . strip_tags(get_the_title($blog_page_id)) . '">' . get_the_title($blog_page_id) . '</a>';
    echo '<h2 class="data-control-id-1397 bd-container-17 bd-tagstyles bd-custom-blockquotes">' . $title . '</h2>';
}
?>
        
<?php
    if (have_posts()) { ?>
        <div class="data-control-id-1365 bd-grid-5 bd-margins">
            <div class="container-fluid">
                <div class="separated-grid row">
<?php
                    while (have_posts()) {
                        the_post();
                        do_action('theme_before_blog_post');

                        $id = theme_get_post_id();
                        $class = theme_get_post_class();
?>
                        
                        <div class="separated-item-34 col-md-12 ">
                        
                            <div class="bd-griditem-34">
                                <article id="<?php echo $id; ?>" class="data-control-id-1441 bd-article-3 clearfix hentry <?php echo $class; if (theme_is_preview()) echo ' bd-post-id-' . theme_get_the_ID(); ?>">
    <div class="data-control-id-1486616 bd-pagetitle-8">
    <div class="bd-container-inner">
        <h1><?php echo theme_get_page_title(); ?></h1>
    </div>
</div>
	
		<?php
if (theme_is_preview() && is_singular()) {
    $editor_attrs = 'data-editable-id="post-' . theme_get_the_ID() . '"';
} else {
    $editor_attrs = '';
}
?>
<div class="data-control-id-1315 bd-postcontent-2 bd-tagstyles bd-custom-blockquotes entry-content bd-contentlayout-offset" <?php echo $editor_attrs; ?>>
    <?php echo(is_singular() ? theme_get_content() : theme_get_excerpt()); ?>
</div>
</article>
                                <?php
                                global $withcomments;
                                if (is_singular() || $withcomments) {  ?>
                                    <?php
    if (theme_get_option('theme_allow_comments')) {
        comments_template('/comments_2.php');
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
        <div class="data-control-id-1403 bd-blogpagination-2">
    <?php
if (is_single()){
    $prev_link = theme_get_next_post_link('%link', '%title &raquo;');
    $next_link = theme_get_previous_post_link('%link', '&laquo; %title');
    if ($prev_link || $next_link) { ?>
<ul class="bd-pagination-15 pagination">
    <?php if ($next_link): ?>
    <li class="bd-paginationitem-15">
        <?php echo $next_link; ?>
    </li>
    <?php endif ?>

    <?php if ($prev_link): ?>
    <li class="bd-paginationitem-15">
        <?php echo $prev_link; ?>
    </li>
    <?php endif ?>
</ul>
<?php
    }
} else {
    global $wp_query;
    if ( $wp_query->max_num_pages > 1 ) {
        echo preg_replace(
            array(
                '/<li(.*current)/',
                '/<ul class=\'page-numbers\'/',
                '/<li>/'
            ),
            array(
                '<li  class="data-control-id-1401 bd-paginationitem-15 active"$1',
                '<ul  class="data-control-id-1402 bd-pagination-15 pagination"',
                '<li  class="data-control-id-1401 bd-paginationitem-15">'
            ),
            paginate_links( array(
                'base' 			=> str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ),
                'format' 		=> '',
                'current' 		=> max( 1, get_query_var('paged') ),
                'total' 		=> $wp_query->max_num_pages,
                'prev_text' 	=> '&larr;',
                'next_text' 	=> '&rarr;',
                'type'			=> 'list',
                'end_size'		=> 3,
                'mid_size'		=> 3
            ) )
        );
    }
}
?>
</div>
        </div>
    </div>
<?php
    if($need_reset_query){
        wp_reset_query();
    }
}