<?php
function theme_blog_1() {
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
    <div class=" bd-blog-1">
        <div class="bd-container-inner">
        
        
<?php
    if (have_posts()) { ?>
        <div class=" bd-grid-4 bd-margins">
            <div class="container-fluid">
                <div class="separated-grid row">
<?php
                    while (have_posts()) {
                        the_post();
                        do_action('theme_before_blog_post');

                        $id = theme_get_post_id();
                        $class = theme_get_post_class();
?>
                        
                        <div class="separated-item-18 col-md-12 ">
                        
                            <div class="bd-griditem-18">
                                <article id="<?php echo $id; ?>" class=" bd-article-2 clearfix hentry <?php echo $class; if (theme_is_preview()) echo ' bd-post-id-' . theme_get_the_ID(); ?>">
    <?php
if (!is_page() || theme_get_meta_option($post->ID, 'theme_show_page_title')) {
    $title = get_the_title();
    if (!is_singular()) {
        $title = sprintf('<a href="%s" rel="bookmark" title="%s">%s</a>', get_permalink($post->ID), strip_tags($title), $title);
    }
    if (!theme_is_empty_html($title)) {
?>
    <h2 class="entry-title  bd-postheader-2">
        <?php echo $title; ?>
    </h2>
<?php
    }
}
?>
	
		<div class=" bd-layoutbox-2 bd-no-margins clearfix">
    <div class="bd-container-inner">
        <?php
	$time_string = get_the_time('U') === get_the_modified_time('U')
        ? '<time class="entry-date published updated" datetime="' . esc_attr(get_the_date('c')) . '">' . get_the_date() . '</time>'
        : '<time class="entry-date published" datetime="' . esc_attr(get_the_date('c')) . '">' . get_the_date() . '</time><time class="updated" style="display:none;" datetime="' . esc_attr(get_the_modified_date('c')) . '">' . get_the_modified_date() . '</time>';
?>
<div class=" bd-posticondate-2 bd-no-margins">
    <span class=" bd-icon bd-icon-34"><?php echo $time_string; ?></span>
</div>
	
		<div class="author vcard  bd-posticonauthor-3 bd-no-margins">
    <a class="url" href="<?php echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="<?php echo esc_attr(__('View all posts by ' . get_the_author(), 'default')) ?>">
        <span class=" bd-icon bd-icon-36"><span class="fn n"><?php echo get_the_author(); ?></span></span>
    </a>
</div>
    </div>
</div>
	
		<div class=" bd-layoutbox-4 bd-no-margins clearfix">
    <div class="bd-container-inner">
        <?php echo theme_get_post_thumbnail(array(
    
    'class' => ' bd-postimage-2 bd-no-margins',
    'img_class' => ' bd-imagestyles',
    'attributes' => '',
    'img_attributes' => '',
)); ?>
	
		<?php
if (theme_is_preview() && is_singular()) {
    $editor_attrs = 'data-editable-id="post-' . theme_get_the_ID() . '"';
} else {
    $editor_attrs = '';
}
?>
<div class=" bd-postcontent-8 bd-tagstyles entry-content bd-contentlayout-offset" <?php echo $editor_attrs; ?>>
    <?php echo(is_singular() ? theme_get_content() : theme_get_excerpt()); ?>
</div>
	
		<?php if ($post && !is_singular() && !theme_is_empty_html('Continue reading...')): ?>
    <a class="bd-postreadmore-4 bd-button "   title="<?php echo strip_tags('Continue reading...') ?>" href="<?php echo get_permalink($post->ID) ?>"><?php _e('Continue reading...', 'default'); ?></a>
<?php endif; ?>
    </div>
</div>
	
		<div class=" bd-layoutbox-6 bd-no-margins clearfix">
    <div class="bd-container-inner">
        <div class=" bd-posticontags-18 bd-no-margins">
    <?php $tags_list = get_the_tag_list('', ', '); ?>
    <?php if ($tags_list): ?>
    <span class=" bd-icon bd-icon-10"><span><?php echo $tags_list; ?></span></span>
    <?php endif; ?>
</div>
    </div>
</div>
</article>
                                <?php
                                global $withcomments;
                                if (is_singular() || $withcomments) {  ?>
                                    <?php
    if (theme_get_option('theme_allow_comments')) {
        comments_template('/comments_1.php');
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
        <div class=" bd-blogpagination-1">
    <?php
if (is_single()){
    $prev_link = theme_get_next_post_link('%link', '%title &raquo;');
    $next_link = theme_get_previous_post_link('%link', '&laquo; %title');
    if ($prev_link || $next_link) { ?>
<ul class="bd-pagination-12 pagination">
    <?php if ($next_link): ?>
    <li class="bd-paginationitem-12">
        <?php echo $next_link; ?>
    </li>
    <?php endif ?>

    <?php if ($prev_link): ?>
    <li class="bd-paginationitem-12">
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
                '<li  class=" bd-paginationitem-12 active"$1',
                '<ul  class=" bd-pagination-12 pagination"',
                '<li  class=" bd-paginationitem-12">'
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