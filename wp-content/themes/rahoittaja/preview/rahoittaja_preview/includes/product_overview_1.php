<?php
function theme_product_overview() {
    global $product_overview_context;
    $product_overview_context = '.bd-productoverview';

    // single post expected
    while (have_posts()) {
        the_post();
        do_action('woocommerce_before_single_product');
?>
        <div  id="product-<?php the_ID(); ?>" <?php post_class("data-control-id-3244 bd-productoverview"); ?>>

            <?php theme_do_action('woocommerce_before_single_product_summary', array(
                array('woocommerce_show_product_sale_flash', 10), // 2.1.0
                array('woocommerce_show_product_images', 20) // 2.1.0
            )); ?>

<?php
            global $post, $product;
            $product_view = array();
            $product_view['link']  = apply_filters('the_permalink', get_permalink());
            $product_view['title'] = the_title('', '', false);
            $product_view['price'] = theme_get_price_data($product);
            $product_view['desc']  = $post->post_excerpt;
            $product_view['image'] = woocommerce_get_product_thumbnail('shop_catalog', '', '');
?>
            <div class="data-control-id-3074 bd-layoutcontainer-29 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class="data-control-id-3070 bd-columnwrapper-66 
 col-md-6
 col-sm-12
 col-xs-12">
    <div class="bd-layoutcolumn-66 bd-column" ><div class="bd-vertical-align-wrapper"><?php if ( isset($product_view['link']) && isset($product_view['title']) ) : ?>
<h2 class="data-control-id-2981 bd-productoverviewtitle-1 bd-no-margins"><?php echo $product_view['title']; ?></h2>
<?php endif; ?>
	
		<?php if (version_compare(WC_VERSION, '3.0.0', '<')) { ?>

    <div class="data-control-id-2966 bd-productimage-6 ">
        <div class="zoom-container images">
        <?php if ( has_post_thumbnail() ) : ?>
            <a itemprop="image" href="<?php echo wp_get_attachment_url( get_post_thumbnail_id() ); ?>"  class="zoom" data-rel="prettyPhoto[product-gallery]" rel="thumbnails" title="<?php echo get_the_title( get_post_thumbnail_id() ); ?>">
            <?php
                global $post;
                remove_action('begin_fetch_post_thumbnail_html', '_wp_post_thumbnail_class_filter_add'); // disable 'wp-post-image' class
                // 
                echo get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ),  array('class' => 'data-control-id-2965 bd-imagestyles') );
                add_action('begin_fetch_post_thumbnail_html', '_wp_post_thumbnail_class_filter_add');
            ?>
            </a>
        <?php else :  ?>
            <img class='data-control-id-2965 bd-imagestyles' src="<?php echo woocommerce_placeholder_img_src(); ?>" alt="Placeholder" />
        <?php endif; ?>
        </div>
    </div>

<?php } else {

    global $post;
	$post_thumbnail_id = get_post_thumbnail_id($post->ID);
	$full_size_image   = wp_get_attachment_image_src($post_thumbnail_id, 'full');
	$thumbnail_post    = get_post($post_thumbnail_id);
	$image_title       = $thumbnail_post->post_content;
?>
	<div class="data-control-id-2966 bd-productimage-6  images woocommerce-product-gallery__wrapper">
		<?php
		$attributes = array(
			'title'                   => $image_title,
			'data-src'                => $full_size_image[0],
			'data-large_image'        => $full_size_image[0],
			'data-large_image_width'  => $full_size_image[1],
			'data-large_image_height' => $full_size_image[2],
            'class'                   => 'data-control-id-2965 bd-imagestyles',
		);

		if (has_post_thumbnail()) {
			$html  = '<div data-thumb="' . get_the_post_thumbnail_url($post->ID, 'shop_thumbnail') . '" class="woocommerce-product-gallery__image zoom-container"><a href="' . esc_url($full_size_image[0]) . '">';
			$html .= get_the_post_thumbnail($post->ID, 'shop_single', $attributes);
			$html .= '</a></div>';
		} else {
			$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
			$html .= sprintf('<img src="%s" alt="%s" class="data-control-id-2965 bd-imagestyles wp-post-image" />', esc_url(wc_placeholder_img_src()), esc_html__('Awaiting product image', 'woocommerce'));
			$html .= '</div>';
		}

		echo apply_filters('woocommerce_single_product_image_thumbnail_html', $html, get_post_thumbnail_id($post->ID));
		?>
	</div>

<?php } ?>
	
		<?php
    $images = theme_get_product_thumbnails_data();
    $images_count = count($images);
    if ($images_count > 0) {
        $items = intval('5');
        $loop = 0;
        $inner_style = '';
        $item_style = '';
        
        if ($images_count < $items && $items !== 0) {
            $inner_style = 'width: ' . floor(100 / $items) * $images_count . '%;margin: 0 auto;';
        }
        if ($items !== 0) {
            $item_style = 'width:' . floor(100 / min($images_count, $items)) . '%';
        }
?>
<div class="data-control-id-2980 bd-imagethumbnails-1 carousel slide adjust-slides   flex-control-nav">
    
    <div class="carousel-inner" style="<?php echo $inner_style; ?>">
        <?php
        foreach ($images as $image) {
            $classes = array('zoom');
            if (get_option('woocommerce_enable_lightbox') === 'yes' && version_compare($GLOBALS['woocommerce']->version, '3.0.0', '<')) {
                $classes[] = 'with-lightbox';
            }

            if ($loop % $items === 0) {
                echo '<ol class="item' . ($loop === 0 ? ' active' : '') . '">';
            }
            ?>

            <li style="<?php echo $item_style; ?>" class="<?php echo implode(' ', $classes); ?>">
                <a class="data-control-id-2971 bd-productimage-7"
                   href="<?php echo $image['url']; ?>"
                   title="<?php echo $image['title']; ?>"
                   data-rel="prettyPhoto[product-gallery]"
                   rel="smallImage:'<?php echo $image['preview']; ?>'"
                   style="width:100%"
                >
                    <img class="data-control-id-2970 bd-imagestyles" src="<?php echo $image['src']; ?>" />
                </a>
            </li>

            <?php
            if ($loop === $images_count - 1 || ($loop + 1) % $items === 0) {
                echo '</ol>';
            }
            $loop++;
        }
        ?>
    </div>
    <?php if ($images_count > $items): ?>
        
            <div class="bd-left-button">
    <a class="data-control-id-2979 bd-carousel" href="#">
        <span class="bd-icon"></span>
    </a>
</div>

<div class="bd-right-button">
    <a class="data-control-id-2979 bd-carousel" href="#">
        <span class="bd-icon"></span>
    </a>
</div>
    <?php endif ?>
</div>
<?php
    }
?></div></div>
</div>
	
		<div class="data-control-id-3072 bd-columnwrapper-67 
 col-md-6
 col-sm-12
 col-xs-12">
    <div class="bd-layoutcolumn-67 bd-column" ><div class="bd-vertical-align-wrapper"><div class="data-control-id-3050 bd-productprice-5">
<?php
    if (isset($product_view['price'])) {
?>
        <span class="price"><?php
            echo theme_price_html(array(
                'price_data'       => $product_view['price'],
                'swap_old_regular' => true,
                'show_old_price'   => true,
                'old_price' => array(
                    'wrap_start'        => '<span class="data-control-id-3049 bd-pricetext-15">',
                    'wrap_end'          => '</span>',
                    'label_class'       => 'data-control-id-3016 bd-label-17',
                    'label_attributes'  => '',
                    'amount_class'      => 'data-control-id-3048 bd-container-36 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table',
                    'amount_attributes' => '',
                ),
                'price' => array(
                    'wrap_start'        => '<span class="data-control-id-3015 bd-pricetext-14">',
                    'wrap_end'          => '</span>',
                    'label_class'       => false,
                    'label_attributes'  =>'',
                    'amount_class'      => 'data-control-id-3014 bd-container-35 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table',
                    'amount_attributes' => '',
                ),
            )); ?>
        </span>
<?php
    }
?>
</div>
	
		<?php

if (get_option('woocommerce_enable_review_rating' ) !== 'no') {

    global $product;

    $rating_count = method_exists($product, 'get_rating_count')   ? $product->get_rating_count()   : 1;
    $review_count = method_exists($product, 'get_review_count')   ? $product->get_review_count()   : 1;
    $average      = method_exists($product, 'get_average_rating') ? $product->get_average_rating() : 0;

    if ($rating_count > 0) {
?>

<div class="data-control-id-3059 bd-productrating-1" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">

    <div class="data-control-id-3058 bd-rating-2" title="<?php printf( __( 'Rated %s out of 5', 'woocommerce' ), $average ); ?>">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="data-control-id-3057  bd-icon bd-icon-2 <?php if ($i <= round($average)) echo ' active'; ?>"></span>
        <?php endfor; ?>

        <span itemprop="ratingValue" style="display:none"><?php echo esc_html($average); ?></span>
        <span itemprop="ratingCount" style="display:none"><?php echo $rating_count; ?></span>
        <span itemprop="reviewCount" style="display:none"><?php echo $review_count; ?></span>
    </div>
</div>

<?php
    }
}
?>
	
		<?php $desc_length = intval('65'); ?>
<div class="data-control-id-3060 bd-productdesc-13">
    <?php
        if (isset($product_view['desc']) && $product_view['desc']) {
            $desc = apply_filters('woocommerce_short_description', $product_view['desc']);

            if ($desc_length > 0) {
                $excerpt = theme_create_excerpt($desc, $desc_length, 1, true);
                if ($excerpt) {
                    $desc = force_balance_tags($excerpt . '...');
                }
            }
            echo $desc;
        }
    ?>
</div>
	
		<div class="data-control-id-3063 bd-productvariations-1">
    <?php
        $variations = theme_wc_get_variations();
        $content = $variations['content'];
        if (theme_wc_quantity_buttons_supported()) {
            $content = str_replace('type="number', 'type="text', $content);
        }
        $content = str_replace('class="input-text qty text"', 'class="data-control-id-3062 bd-bootstrapinput-5 form-control input-sm qty" maxlength="12"', $content);
        echo $content;
    ?>
    <script>
        jQuery('.bd-productvariations-1 table.variations label').css('display', 'inline');
        jQuery('.bd-productvariations-1 table.variations a.reset_variations').each(function() {
            var reset_link = jQuery('<div>').append(jQuery(this).clone()).remove().html();
            this.remove();
            jQuery('.bd-productvariations-1 table.variations tbody').append('<tr><td></td><td>' + reset_link + '</td></tr>')
        });
    </script>
    <?php theme_do_action('woocommerce_single_product_summary', array(
        array('woocommerce_template_single_title', 5), // 2.1.0
        array('woocommerce_template_single_rating', 10), // 2.1.0
        array('woocommerce_template_single_price', 10), // 2.1.0
        array('woocommerce_template_single_excerpt', 20), // 2.1.0
        array('woocommerce_template_single_add_to_cart', 30), // 2.1.0
    )); ?>
</div>
	
		<?php theme_product_buy('data-control-id-3068 bd-productbuy-4 bd-button-9', ' '); ?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<?php
    $tabs = apply_filters('woocommerce_product_tabs', array());

    if (is_null($tabs)) {
        $tabs = array('reviews' => array());
    }
    if (isset($tabs['reviews'])) {
        $tabs['reviews']['callback'] = 'theme_tab_reviews_2';
    }

    if ( ! empty( $tabs ) ) :
        ob_start();
        $count = 0;
        foreach ( $tabs as $key => $tab ) : ?>
            <li class="<?php if ($count == 0) echo 'active '; echo $key; ?>_tab data-control-id-3110 bd-menuitem-12">
                <a data-toggle="tab" href="#tab-<?php echo $key ?>2">
                    <span>
                        <?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', $tab['title'], $key ) ?>
                    </span>
                </a>
            </li>
        <?php $count++; endforeach;
        $tabs_header = ob_get_clean();

        ob_start();
        $count = 0;
        foreach ( $tabs as $key => $tab ) : ?>
            <div class="tab-<?php echo $key; ?> tab-pane entry-content<?php if ($count == 0) echo ' active'; ?>" id="tab-<?php echo $key; ?>2">
                <?php call_user_func( $tab['callback'], $key, $tab ) ?>
            </div>
        <?php $count++; endforeach;
        $tabs_content = ob_get_clean();
    ?>

        <div class="data-control-id-3151 bd-tabinformationcontrol-2 tabbable" data-responsive="true">
            <div class="bd-container-inner">
            <div><ul class="data-control-id-3118 bd-menu-12 clearfix nav nav-tabs navbar-left">
    <?php echo $tabs_header; ?>
</ul></div>
            <div class="data-control-id-3150 bd-container-37 bd-tagstyles tab-content">
    <?php echo $tabs_content; ?>
</div>
            <div class="data-control-id-1053745 bd-accordion accordion">
    <div class="data-control-id-1056626 bd-menuitem-8 accordion-item"></div>
    <div class="data-control-id-1056636 bd-container-41 bd-tagstyles accordion-content"></div>
</div>
            </div>
        </div>
<?php
    endif;
?>

			<?php theme_do_action('woocommerce_after_single_product_summary', array(
                array('woocommerce_output_product_data_tabs', 10), // 2.1.0
                array('woocommerce_upsell_display', 15), // 2.1.0
                array('woocommerce_output_related_products', 20) // 2.1.0
            )); ?>
        </div>
    <?php
        do_action('woocommerce_after_single_product');
    }

    $product_overview_context = null;
}