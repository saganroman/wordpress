<?php
function theme_breadcrumbs_1(){
    $items = theme_breadcrumbs_items_1();
    $itemsCount = count($items);
    if ($itemsCount > 0) {
?>
    
    <div class="data-control-id-859 bd-breadcrumbs-1">
        <div class="bd-container-inner">
            <ol class="breadcrumb">
                <?php for($i = 0; $i < $itemsCount; $i++ ) {
                    if ($i < $itemsCount - 1) { ?>
                        <li><?php echo trim($items[$i]); ?></li>
                    <?php } else { ?>
                        <li class="active"><?php echo preg_replace("/<[\/]*a[^>]*>/", "", trim($items[$i])); ?></li>
                    <?php }
                } ?>
            </ol>
        </div>
    </div>
    
<?php
    }
}

function theme_breadcrumbs_items_1(){
    global $post, $wp_query;
    $items = array();

    $shop_item = '';
    if (theme_woocommerce_enabled() && get_option('woocommerce_prepend_shop_page_to_urls') == "yes" && wc_get_page_id( 'shop' ) && get_option( 'page_on_front' ) !== wc_get_page_id( 'shop' ) ){
        $shop_item = theme_breadcrumbs_link_1(get_permalink( wc_get_page_id('shop') ), get_the_title( wc_get_page_id('shop') ), get_the_title( wc_get_page_id('shop') ));
    }

    if ( !is_front_page() ) {
        $items[] = theme_breadcrumbs_link_1(get_home_url(), '', __('Home', 'default'));
    }

    if (is_category()) {
        $thisCat = get_category(get_query_var('cat'), false);
        $cats = explode('|', get_category_parents($thisCat->cat_ID, TRUE, '|'));
        foreach($cats as $cat) {
            if (theme_strlen($cat) > 0) {
                $items[] = theme_breadcrumbs_text_1($cat);
            }
        }
    } elseif ( is_tax('product_cat') ) {

        if (strlen($shop_item) > 0)
            $items[] = $shop_item;

        $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

        $parents = array();
        $parent = $term->parent;
        while ( $parent ) {
            $parents[] = $parent;
            $new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ) );
            $parent = $new_parent->parent;
        }

        if ( ! empty( $parents ) ) {
            $parents = array_reverse( $parents );
            foreach ( $parents as $parent ) {
                $item = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
                $items[] = theme_breadcrumbs_link_1( get_term_link( $item->slug, 'product_cat' ), $item->name, $item->name);
            }
        }

        $queried_object = $wp_query->get_queried_object();
        $items[] = theme_breadcrumbs_text_1($queried_object->name);

    } elseif ( is_tax('product_tag') ) {
        if (strlen($shop_item) > 0)
            $items[] = $shop_item;
        $queried_object = $wp_query->get_queried_object();
        $items[] = theme_breadcrumbs_text_1(__('Products tagged &ldquo;', 'woocommerce') . $queried_object->name . '&rdquo;');
    }

    if(is_home()) {
        if (is_front_page())
            $items[] = theme_breadcrumbs_text_1(get_bloginfo('name'));
        else
            $items[] = theme_breadcrumbs_text_1(single_post_title('', false));
    }

    if(is_page() && !is_front_page()) {
        $parents = array();
        $parent_id = $post->post_parent;
        while ( $parent_id ) {
            $page = get_post( $parent_id );
            if ($parent_id != get_option('page_on_front')) {
                $parents[] = theme_breadcrumbs_link_1(get_permalink( $page->ID ), get_the_title( $page->ID ), get_the_title( $page->ID ));
            }
            $parent_id  = $page->post_parent;
        }
        $parents = array_reverse( $parents );
        foreach($parents as $p) {
            if (theme_strlen($p) > 0) {
                $items[] = $p;
            }
        }
        $items[] = theme_breadcrumbs_text_1(get_the_title());
    }

    if (theme_woocommerce_enabled() && is_post_type_archive('product') && get_option('page_on_front') !== wc_get_page_id('shop') ) {
        $_name = wc_get_page_id( 'shop' ) ? get_the_title( wc_get_page_id( 'shop' ) ) : ucwords( get_option( 'woocommerce_shop_slug' ) );
        if ( is_search() ) {
            $items[] = theme_breadcrumbs_link_1(get_post_type_archive_link('product'), $_name, $_name);
            $items[] = theme_breadcrumbs_text_1(__('Search results for &ldquo;', 'woocommerce') . get_search_query() . '&rdquo;');
        } elseif ( is_paged() ) {
            $items[] = theme_breadcrumbs_link_1(get_post_type_archive_link('product'), $_name, $_name);
        } else {
            $items[] = theme_breadcrumbs_text_1($_name);
        }
    }

    if(is_single()) {

        if ( get_post_type() == 'product' ) {

            if(strlen($shop_item) > 0)
                $items[] = $shop_item;

            if ( $terms = wp_get_object_terms( $post->ID, 'product_cat' ) ) {
                $term = current( $terms );
                $parents = array();
                $parent = $term->parent;

                while ( $parent ) {
                    $parents[] = $parent;
                    $new_parent = get_term_by( 'id', $parent, 'product_cat' );
                    $parent = $new_parent->parent;
                }

                if ( ! empty( $parents ) ) {
                    $parents = array_reverse($parents);
                    foreach ( $parents as $parent ) {
                        $item = get_term_by( 'id', $parent, 'product_cat');
                        $items[] = theme_breadcrumbs_link_1(get_term_link( $item->slug, 'product_cat' ), $item->name, $item->name);
                    }
                }

                $items[] = theme_breadcrumbs_link_1(get_term_link( $term->slug, 'product_cat' ), $term->name, $term->name);

            }

            $items[] = theme_breadcrumbs_text_1(get_the_title());

        } elseif ( get_post_type() != 'post' ) {

            $post_type = get_post_type_object( get_post_type() );
            $slug = $post_type->rewrite;
            $items[] = theme_breadcrumbs_link_1(get_post_type_archive_link( get_post_type() ), $post_type->labels->singular_name, $post_type->labels->singular_name);
            $items[] = theme_breadcrumbs_text_1(get_the_title());

        } else {
            $categories_1 = get_the_category();
            if($categories_1):
                foreach($categories_1 as $cat_1):
                    $cat_1_ids[] = $cat_1->term_id;
                endforeach;
                $cat_1_line = implode(',', $cat_1_ids);
            endif;
            $categories = get_categories(array(
                'include' => $cat_1_line,
                'orderby' => 'id'
            ));
            if ( $categories ) :
                foreach ( $categories as $cat ) :
                    $cats[] = theme_breadcrumbs_link_1(get_category_link( $cat->term_id ), $cat->name, $cat->name);
                endforeach;
                foreach($cats as $cat) {
                    if (theme_strlen($cat) > 0) {
                        $items[] = $cat;
                    }
                }
            endif;
            $items[] = theme_breadcrumbs_text_1(get_the_title());
        }
    }

    if(is_tag()){
        $items[] = theme_breadcrumbs_text_1(__("Tag: ", 'default') . single_tag_title('', FALSE));
    }
    if(is_404()){
        $items[] = theme_breadcrumbs_text_1(__("404 - Page not Found", 'default'));
    }
    if(is_search()){
        $items[] = theme_breadcrumbs_text_1(__("Search", 'default'));
    }
    if(is_year()){
        $items[] = theme_breadcrumbs_text_1(get_the_time('Y'));
    }
    if(is_author()){
        $items[] = theme_breadcrumbs_text_1(sprintf( esc_attr(__( 'View all posts by %s', 'default' )), get_the_author() ));
    }

    if (count($items) == 0){
        $items[] = theme_breadcrumbs_text_1(get_bloginfo('name'));
    }
    return $items;
}

function theme_breadcrumbs_link_1($href = '#', $title = '', $text = ''){
    ob_start();
?>
    
    <div class="data-control-id-848 bd-breadcrumbslink-1">
        <a href="<?php echo $href; ?>" title="<?php echo $title; ?>"><?php echo $text; ?></a>
    </div>
    
<?php
    return ob_get_clean();
}

function theme_breadcrumbs_text_1($text = ''){
    ob_start();
?>
    
    <span class="data-control-id-847 bd-breadcrumbstext-1"><span><?php echo $text; ?></span></span>
    
<?php
    return ob_get_clean();
}
?>