<?php

global $slider_wrapper;
$slider_wrapper = array();

function theme_write_items($elements, $items) {
    $loop = 0;
    $items = max(1, $items);
    foreach($elements as $element) {
        if ($loop % $items == 0)
            printf('<div class="item%s">', $loop == 0 ? ' active' : '');
        echo $element;
        if ($loop == count($elements) - 1 || ($loop + 1) % $items == 0)
            echo '</div>';
        $loop++;
    }
}

function theme_set_slider_wrapper($widget_id, $one_row) {
    global $slider_wrapper;
    $slider_wrapper[$widget_id] = array(
        'before' => '<div class=" bd-grid-41"><div class="container-fluid"><div class="separated-grid row"><div
class="carousel slide adjust-slides" data-slider-id="'.$widget_id.'" '.($one_row ? 'style="padding-left: 0; padding-right: 0; width: 100%"' : '').'>',
        'after' => '</div></div></div></div>'
    );
}

function theme_get_slider_wrapper($widget_id) {
    global $slider_wrapper;
    if (!isset($slider_wrapper[$widget_id]))
        return array('before' => '', 'after' => '');
    return $slider_wrapper[$widget_id];
}

function theme_products_slider_block($title = '', $content = '', $class = '', $id = '') {
    $wrapper = theme_get_slider_wrapper($id);
    $content = $wrapper['before'] . $content . $wrapper['after'];
    ob_start();
?>
    <div class=" bd-productsslider-1">
        <div class="bd-container-inner">
            <?php theme_products_slider_block_1($title, $content, $class, $id); ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

class Theme_WooCommerce_Widget_Product_Slider extends WP_Widget {

    var $woo_widget_cssclass;
    var $woo_widget_description;
    var $woo_widget_idbase;
    var $woo_widget_name;
    var $woo_widget_class;
    var $woo_widget_default_title;
    var $woo_widget_default_width_lg;
    var $woo_widget_default_width;
    var $woo_widget_default_width_sm;
    var $woo_widget_default_width_xs;
    var $woo_widget_default_items_in_row;

    function __construct() {
        
        $this->woo_widget_default_width_lg = '';
        $this->woo_widget_default_width = '';
        $this->woo_widget_default_width_sm = '';
        $this->woo_widget_default_width_xs = '';
        $this->woo_widget_default_items_in_row = '2';
        $widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );
        parent::__construct($this->widget_custom_init(), $this->woo_widget_name, $widget_ops);
        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }

    function  widget_custom_init(){
        return 'base';
    }

    function  widget_custom_form($instance){

    }

    function  widget_custom_update(&$instance, $new_instance){

    }

    function widget_get_wp_query($instance) {
        return null;
    }

    function widget($args, $instance) {
        global $woocommerce, $post;

        $cache = wp_cache_get($this->woo_widget_class, 'widget');
        if (!is_array($cache))
            $cache = array();
        if (isset($cache[$args['widget_id']])) {
            echo $cache[$args['widget_id']];
            return;
        }

        $title = apply_filters('widget_title', empty($instance['title']) ? $this->woo_widget_default_title : $instance['title'], $instance, $this->id_base);
        $query = $this->widget_get_wp_query($instance);
        if ($query === null)
            return;

        ob_start();
        $items_in_row = empty($instance['items_in_row']) ? $this->woo_widget_default_items_in_row : $instance['items_in_row'];

        if ($query->have_posts()) :
            echo $args['before_widget'];
            if ($title)
                echo $args['before_title'] . $title . $args['after_title'];
            $elements = array();

            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                $product_view = array();
                $product_view['link']  = apply_filters('the_permalink', get_permalink());
                $product_view['title'] = the_title('', '', false);
                $product_view['price'] = theme_get_price_data($product);
                $product_view['desc']  = $post->post_excerpt;
                $product_view['image'] = woocommerce_get_product_thumbnail('shop_catalog', '', '');

                ob_start();

                $item_class = 'separated-item-2 grid';

                // columns width stored in 1-24 format in database
                $width_lg = intval((empty($instance['width_lg']) ? $this->woo_widget_default_width_lg : $instance['width_lg']) / 2);
                if ($width_lg) {
                    $item_class .= ' col-lg-' . $width_lg;
                }
                $width = intval((empty($instance['width']) ? $this->woo_widget_default_width : $instance['width']) / 2);
                if ($width){
                    $item_class .= ' col-md-' . $width;
                }
                $width_sm = intval((empty($instance['width_sm']) ? $this->woo_widget_default_width_sm : $instance['width_sm']) / 2);
                if ($width_sm) {
                    $item_class .= ' col-sm-' . $width_sm;
                }
                $width_xs = intval((empty($instance['width_xs']) ? $this->woo_widget_default_width_xs : $instance['width_xs']) / 2);
                if ($width_xs) {
                    $item_class .= ' col-xs-' . $width_xs;
                }
?>
                <div class="<?php echo $item_class; ?>">
                    <div class=" bd-griditem-2">
                        <?php theme_do_action('woocommerce_before_shop_loop_item', array(
                            array('woocommerce_template_loop_product_link_open', 10) // 2.1.0
                        )); ?>
                        <a class=" bd-productimage-2" href="<?php echo $product_view['link']; ?>"><?php theme_product_image($product_view, ' bd-imagestyles-15', ''); ?></a>
	
		<?php if (time() - get_option('theme_products_newness_period') * 60 * 60 * 24 < strtotime(get_the_time('Y-m-d'))) { ?>
<div class=" bd-productnewicon-4 bd-productnew-1">
    <span><?php _e('New!', 'woocommerce'); ?></span>
</div>
<?php } ?>
	
		<?php if ($product->is_on_sale()): ?>
<div class=" bd-productsaleicon bd-productsale-1">
    <span><?php _e('Sale!', 'woocommerce'); ?></span>
</div>
<?php endif; ?>
	
		<?php
    if (!$product->is_in_stock()) :
?>
        <div class=" bd-productoutofstockicon bd-productoutofstock-1">
            <span>
                <?php _e('Out of stock', 'woocommerce'); ?>
            </span>
        </div>
<?php
    endif;
?>
	
		<?php if ( isset($product_view['link']) && isset($product_view['title']) ){ ?><div class=" bd-producttitle-4"><a href="<?php echo $product_view['link']; ?>"><?php echo $product_view['title']; ?></a></div><?php } ?>
	
		<div class=" bd-productprice-2">
<?php
    if (isset($product_view['price'])) {
?>
        <span class="price"><?php
            echo theme_price_html(array(
                'price_data'       => $product_view['price'],
                'swap_old_regular' => true,
                'show_old_price'   => false,
                'old_price' => array(
                    'wrap_start'        => '<span class=" bd-pricetext-6">',
                    'wrap_end'          => '</span>',
                    'label_class'       => false,
                    'label_attributes'  => '',
                    'amount_class'      => ' bd-container-8 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table',
                    'amount_attributes' => '',
                ),
                'price' => array(
                    'wrap_start'        => '<span class=" bd-pricetext-5">',
                    'wrap_end'          => '</span>',
                    'label_class'       => false,
                    'label_attributes'  =>'',
                    'amount_class'      => ' bd-container-13 bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table',
                    'amount_attributes' => '',
                ),
            )); ?>
        </span>
<?php
    }
?>
</div>
	
		<?php theme_product_buy(' bd-productbuy-1 bd-no-margins bd-button', ' '); ?>
                        <?php theme_do_action('woocommerce_after_shop_loop_item', array(
                            array('woocommerce_template_loop_product_link_close', 5), // 2.1.0
                            array('woocommerce_template_loop_add_to_cart', 10)
                        )); ?>
                    </div>
                </div>

<?php
                $elements[] = ob_get_clean();
            }

            theme_set_slider_wrapper($args['widget_id'], count($elements) <= $items_in_row);
?>
            <div class="carousel-inner">
                <?php theme_write_items($elements, $items_in_row); ?>
            </div>
            <?php if (count($elements) > $items_in_row): ?>
                
                    <div class="bd-left-button">
    <a class=" bd-carousel-2" href="#">
        <span class="bd-icon"></span>
    </a>
</div>

<div class="bd-right-button">
    <a class=" bd-carousel-2" href="#">
        <span class="bd-icon"></span>
    </a>
</div>
            <?php endif; ?>
<?php
            echo $args['after_widget'];

            // Reset the global $the_post as this query will have stomped on it
            wp_reset_query();
        endif;

        $content = ob_get_clean();

        if (isset($args['widget_id']))
            $cache[$args['widget_id']] = $content;

        echo $content;

        wp_cache_set($this->woo_widget_class, $cache, 'widget');
        wp_reset_postdata();
    }

    function flush_widget_cache() {
        wp_cache_delete($this->woo_widget_class, 'widget');
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];

        $instance['width_lg'] = (int) $new_instance['width_lg'];
        $instance['width'] = (int) $new_instance['width'];
        $instance['width_sm'] = (int) $new_instance['width_sm'];
        $instance['width_xs'] = (int) $new_instance['width_xs'];

        $instance['items_in_row'] = (int) $new_instance['items_in_row'];

        $this->widget_custom_update( $instance, $new_instance );

        $this->flush_widget_cache();

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if (isset($alloptions[$this->woo_widget_cssclass]))
            delete_option($this->woo_widget_cssclass);

        return $instance;
    }

    function form( $instance ) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
            $number = 2;

        if ( !isset($instance['width_lg']) || !$width_lg = (int) $instance['width_lg'] )
            $width_lg = $this->woo_widget_default_width_lg;
        if ( !isset($instance['width']) || !$width = (int) $instance['width'] )
            $width = $this->woo_widget_default_width;
        if ( !isset($instance['width_sm']) || !$width_sm = (int) $instance['width_sm'] )
            $width_sm = $this->woo_widget_default_width_sm;
        if ( !isset($instance['width_xs']) || !$width_xs = (int) $instance['width_xs'] )
            $width_xs = $this->woo_widget_default_width_xs;
        if ( !isset($instance['items_in_row']) || !$items_in_row = (int) $instance['items_in_row'] )
            $items_in_row = $this->woo_widget_default_items_in_row;

        // columns width stored in 1-24 format in database
        $options = array('' => '', '24' => '1', '12' => '2', '8' => '3', '6' => '4', '4' => '6', '3' => '12');

        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woocommerce'); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of products to show:', 'woocommerce'); ?></label>
            <input id="<?php echo esc_attr( $this->get_field_id('number') ); ?>" name="<?php echo esc_attr( $this->get_field_name('number') ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" /></p>

        <p><b>Columns</b></p>
        <table>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id('width_lg'); ?>"><?php _e('Desktops:', 'woocommerce'); ?></label>
                </td>
                <td>
                    <select id="<?php echo esc_attr( $this->get_field_id('width_lg') ); ?>" name="<?php echo esc_attr( $this->get_field_name('width_lg') ); ?>">
                    <?php foreach ($options as $key => $option) {
                            $selected = ($width_lg == $key ? ' selected="selected"' : '');
                            echo '<option' . $selected . ' value="' . $key . '">' . esc_html($option) . '</option>' . "\n";
                    } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Laptops:', 'woocommerce'); ?></label>
                </td>
                <td>
                    <select id="<?php echo esc_attr( $this->get_field_id('width') ); ?>" name="<?php echo esc_attr( $this->get_field_name('width') ); ?>">
                    <?php foreach ($options as $key => $option) {
                        $selected = ($width == $key ? ' selected="selected"' : '');
                        echo '<option' . $selected . ' value="' . $key . '">' . esc_html($option) . '</option>' . "\n";
                    } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id('width_sm'); ?>"><?php _e('Tablets:', 'woocommerce'); ?></label>
                </td>
                <td>
                    <select id="<?php echo esc_attr( $this->get_field_id('width_sm') ); ?>" name="<?php echo esc_attr( $this->get_field_name('width_sm') ); ?>">
                    <?php foreach ($options as $key => $option) {
                        $selected = ($width_sm == $key ? ' selected="selected"' : '');
                        echo '<option' . $selected . ' value="' . $key . '">' . esc_html($option) . '</option>' . "\n";
                    } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $this->get_field_id('width_xs'); ?>"><?php _e('Phones:', 'woocommerce'); ?></label>
                </td>
                <td>
                    <select id="<?php echo esc_attr( $this->get_field_id('width_xs') ); ?>" name="<?php echo esc_attr( $this->get_field_name('width_xs') ); ?>">
                    <?php foreach ($options as $key => $option) {
                        $selected = ($width_xs == $key ? ' selected="selected"' : '');
                        echo '<option' . $selected . ' value="' . $key . '">' . esc_html($option) . '</option>' . "\n";
                    } ?>
                    </select>
                </td>
            </tr>
        </table>

        <p><label for="<?php echo $this->get_field_id('items_in_row'); ?>"><?php _e('Number of products in slide:', 'woocommerce'); ?></label>
            <input id="<?php echo esc_attr( $this->get_field_id('items_in_row') ); ?>" name="<?php echo esc_attr( $this->get_field_name('items_in_row') ); ?>" type="text" value="<?php echo esc_attr( $items_in_row ); ?>" size="3" /></p>

        <?php
        $this->widget_custom_form($instance);

    }
}

class Theme_WooCommerce_Widget_Featured_Products extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_featured_products';
        $this->woo_widget_description = __( 'Display a list of featured products on your site.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_featured_products';
        $this->woo_widget_name = __('WooCommerce Featured Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_featured_products';
        $this->woo_widget_default_title = __('Featured Products', 'woocommerce');
        return 'featured-products';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;

        if ( !$number = (int) $instance['number'] )
            $number = 10;
        else if ( $number < 1 )
            $number = 1;
        else if ( $number > 15 )
            $number = 15;

        $query_args = array(
            'posts_per_page' => $number,
            'no_found_rows' => 1,
            'post_status' => 'publish',
            'post_type' => 'product',
            'meta_query' => array(),
            'tax_query' => array(),
        );
        $query_args['meta_query'] = array();
        if (function_exists('wc_get_product_visibility_term_ids')) {
            $product_visibility_term_ids = wc_get_product_visibility_term_ids();
            $query_args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'term_taxonomy_id',
                'terms'    => $product_visibility_term_ids['featured'],
            );
        } else {
            $query_args['meta_query'][] = array(
                'key' => '_featured',
                'value' => 'yes'
            );
        }
        $query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
        $query_args['meta_query'][] = $woocommerce->query->visibility_meta_query();

        return new WP_Query($query_args);
    }
}

class Theme_WooCommerce_Widget_Recently_Viewed extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_recently_viewed_products';
        $this->woo_widget_description = __( 'Display a list of recently viewed products.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_recently_viewed_products';
        $this->woo_widget_name = __('WooCommerce Recently Viewed Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_recently_viewed_products';
        $this->woo_widget_default_title = __('Recently viewed', 'woocommerce');
        return 'recently_viewed_products';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;

        if ( !$number = (int) $instance['number'] )
            $number = 10;
        else if ( $number < 1 )
            $number = 1;
        else if ( $number > 15 )
            $number = 15;

        $query_args = array('posts_per_page' => $number, 'no_found_rows' => 1, 'post_status' => 'publish', 'post_type' => 'product', 'post__in' => $_SESSION['viewed_products'], 'orderby' => 'rand');

        $query_args['meta_query'] = array();

        $query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();

        return new WP_Query($query_args);
    }
}

class Theme_WooCommerce_Widget_Best_Sellers extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_best_sellers';
        $this->woo_widget_description = __( 'Display a list of your best selling products on your site.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_best_sellers';
        $this->woo_widget_name = __('WooCommerce Best Sellers', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_best_sellers';
        $this->woo_widget_default_title = __('Best Sellers', 'woocommerce');
        return 'best_sellers';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;

        if ( !$number = (int) $instance['number'] )
            $number = 10;
        else if ( $number < 1 )
            $number = 1;
        else if ( $number > 15 )
            $number = 15;

        $query_args = array(
            'posts_per_page' => $number,
            'post_status' 	 => 'publish',
            'post_type' 	 => 'product',
            'meta_key' 		 => 'total_sales',
            'orderby' 		 => 'meta_value',
            'no_found_rows'  => 1,
        );

        $query_args['meta_query'] = array();

        if ( isset( $instance['hide_free'] ) && 1 == $instance['hide_free'] ) {
            $query_args['meta_query'][] = array(
                'key'     => '_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'DECIMAL',
            );
        }

        $query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
        $query_args['meta_query'][] = $woocommerce->query->visibility_meta_query();

        return new WP_Query($query_args);
    }

    function widget_custom_update (&$instance, $new_instance ) {
        $instance['hide_free'] = 0;
        if ( isset( $new_instance['hide_free'] ) ) {
            $instance['hide_free'] = 1;
        }
    }

    function widget_custom_form( $instance ) {
        $hide_free_checked = ( isset( $instance['hide_free'] ) && 1 == $instance['hide_free'] ) ? ' checked="checked"' : '';
        ?>
        <p><input id="<?php echo esc_attr( $this->get_field_id('hide_free') ); ?>" name="<?php echo esc_attr( $this->get_field_name('hide_free') ); ?>" type="checkbox"<?php echo $hide_free_checked; ?> />
            <label for="<?php echo $this->get_field_id('hide_free'); ?>"><?php _e('Hide free products', 'woocommerce'); ?></label></p>
    <?php
    }
}

class Theme_WooCommerce_Widget_Recent_Products extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_recent_products';
        $this->woo_widget_description = __( 'Display a list of your most recent products on your site.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_recent_products';
        $this->woo_widget_name = __('WooCommerce Recent Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_recent_products';
        $this->woo_widget_default_title = __('New Products', 'woocommerce');
        return 'recent_products';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;

        if ( !$number = (int) $instance['number'] )
            $number = 10;
        else if ( $number < 1 )
            $number = 1;
        else if ( $number > 15 )
            $number = 15;

        $show_variations = $instance['show_variations'] ? '1' : '0';

        $query_args = array('posts_per_page' => $number, 'no_found_rows' => 1, 'post_status' => 'publish', 'post_type' => 'product');

        $query_args['meta_query'] = array();

        if ( $show_variations == '0' ) {
            $query_args['meta_query'][] = $woocommerce->query->visibility_meta_query();
            $query_args['parent'] = '0';
        }

        $query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();

        return new WP_Query($query_args);
    }

    function widget_custom_update( &$instance, $new_instance ) {
        $instance['show_variations'] = !empty($new_instance['show_variations']) ? 1 : 0;
    }

    function widget_custom_form( $instance ) {
        $show_variations = isset( $instance['show_variations'] ) ? (bool) $instance['show_variations'] : false;
        ?>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('show_variations') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_variations') ); ?>"<?php checked( $show_variations ); ?> />
            <label for="<?php echo $this->get_field_id('show_variations'); ?>"><?php _e( 'Show hidden product variations', 'woocommerce' ); ?></label><br />
        </p>
    <?php
    }
}


class Theme_WooCommerce_Widget_On_Sale extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_onsale';
        $this->woo_widget_description = __( 'Display a list of your on-sale products on your site.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_onsale';
        $this->woo_widget_name = __('WooCommerce On-sale', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_onsale';
        $this->woo_widget_default_title = __('On Sale', 'woocommerce');
        return 'onsale';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;

        if ( !$number = (int) $instance['number'] )
            $number = 10;
        else if ( $number < 1 )
            $number = 1;
        else if ( $number > 15 )
            $number = 15;

        // Get products on sale
        if ( false === ( $product_ids_on_sale = get_transient( 'wc_products_onsale' ) ) ) {

            $meta_query = array();

            $meta_query[] = array(
                'key' => '_sale_price',
                'value' 	=> 0,
                'compare' 	=> '>',
                'type'		=> 'NUMERIC'
            );

            $on_sale = get_posts(array(
                'post_type' 		=> array('product', 'product_variation'),
                'posts_per_page' 	=> -1,
                'post_status' 		=> 'publish',
                'meta_query' 		=> $meta_query,
                'fields' 			=> 'id=>parent'
            ));

            $product_ids 	= array_keys( $on_sale );
            $parent_ids		= array_values( $on_sale );

            // Check for scheduled sales which have not started
            foreach ( $product_ids as $key => $id )
                if ( get_post_meta( $id, '_sale_price_dates_from', true ) > current_time('timestamp') )
                    unset( $product_ids[ $key ] );

            $product_ids_on_sale = array_unique( array_merge( $product_ids, $parent_ids ) );

            set_transient( 'wc_products_onsale', $product_ids_on_sale );
        }

        $product_ids_on_sale[] = 0;

        $meta_query = array();
        $meta_query[] = $woocommerce->query->visibility_meta_query();
        $meta_query[] = $woocommerce->query->stock_status_meta_query();

        $query_args = array(
            'posts_per_page' 	=> $number,
            'no_found_rows' => 1,
            'post_status' 	=> 'publish',
            'post_type' 	=> 'product',
            'orderby' 		=> 'date',
            'order' 		=> 'ASC',
            'meta_query' 	=> $meta_query,
            'post__in'		=> $product_ids_on_sale
        );

        return new WP_Query($query_args);
    }

}

class Theme_WooCommerce_Widget_Random_Products extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_random_products';
        $this->woo_widget_description = __( 'Display a list of random products on your site.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_random_products';
        $this->woo_widget_name    = __('WooCommerce Random Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_random_products';
        $this->woo_widget_default_title = __('Random Products', 'woocommerce');
        return 'random_products';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;

        // Setup product query
        $query_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $instance['number'],
            'orderby'        => 'rand',
            'no_found_rows'  => 1
        );

        $query_args['meta_query'] = array();

        if ( ! $instance['show_variations'] ) {
            $query_args['meta_query'][] = $woocommerce->query->visibility_meta_query();
            $query_args['post_parent'] = 0;
        }

        $query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();

        return new WP_Query( $query_args );
    }

    function widget_custom_update( &$instance, $new_instance ) {
        $instance['show_variations'] = !empty($new_instance['show_variations']) ? 1 : 0;
    }

    function widget_custom_form( $instance ) {
        $show_variations = isset( $instance['show_variations'] ) ? (bool) $instance['show_variations'] : false;
        ?>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('show_variations') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_variations') ); ?>"<?php checked( $show_variations ); ?> />
            <label for="<?php echo $this->get_field_id('show_variations'); ?>"><?php _e( 'Show hidden product variations', 'woocommerce' ); ?></label><br />
        </p>
    <?php
    }
}

class Theme_WooCommerce_Widget_Top_Rated_Products extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_top_rated_products';
        $this->woo_widget_description = __( 'Display a list of top rated products on your site.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_top_rated_products';
        $this->woo_widget_name = __('WooCommerce Top Rated Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_top_rated_products';
        $this->woo_widget_default_title = __('Top Rated Products', 'woocommerce');
        return 'top-rated-products';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;

        if ( !$number = (int) $instance['number'] ) $number = 10;
        else if ( $number < 1 ) $number = 1;
        else if ( $number > 15 ) $number = 15;

        add_filter( 'posts_clauses',  array(&$this, 'order_by_rating_post_clauses') );
        $query_args = array('posts_per_page' => $number, 'no_found_rows' => 1, 'post_status' => 'publish', 'post_type' => 'product' );
        $query_args['meta_query'] = array();
        $query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
        $query_args['meta_query'][] = $woocommerce->query->visibility_meta_query();

        return new WP_Query( $query_args );
    }

    function order_by_rating_post_clauses( $args ) {

        global $wpdb;

        $args['where'] .= " AND $wpdb->commentmeta.meta_key = 'rating' ";

        $args['join'] .= "
            LEFT JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
            LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
        ";

        $args['orderby'] = "$wpdb->commentmeta.meta_value DESC";

        $args['groupby'] = "$wpdb->posts.ID";

        return $args;
    }

}

class Theme_WooCommerce_Widget_Related_Products extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_related_products';
        $this->woo_widget_description = __( 'Display a list of related products.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_related_products';
        $this->woo_widget_name = __('WooCommerce Related Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_related_products';
        $this->woo_widget_default_title = __('Related Products', 'woocommerce');
        return 'related_products';
    }

    function widget_get_wp_query($instance) {
        global $wp_query;
        $product = get_product($wp_query->post->ID);
        if (!$product)
            return null;
        $related = $product->get_related($instance['number']);
        if ( sizeof($related) == 0 )
            return null;

        $products = new WP_Query(apply_filters('woocommerce_related_products_args', array(
            'post_type'				=> 'product',
            'ignore_sticky_posts'	=> 1,
            'no_found_rows' 		=> 1,
            'posts_per_page'        => $instance['number'],
            'post__in' 				=> $related,
            'post__not_in'			=> array($product->id)
        )));
        return $products;
    }
}

class Theme_WooCommerce_Widget_UpSells_Products extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_upsells_products';
        $this->woo_widget_description = __( 'Display a list of Up-Sells products on product page.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_upsells_products';
        $this->woo_widget_name = __('WooCommerce Up-Sells Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_upsells_products';
        $this->woo_widget_default_title = __('Up-Sells Products', 'woocommerce');
        return 'upsells_products';
    }

    function widget_get_wp_query($instance) {
        global $wp_query, $woocommerce;
        $product = get_product($wp_query->post->ID);
        if (!$product)
            return null;

        $upsells = $product->get_upsells();
        if (sizeof($upsells) == 0)
            return null;

        return new WP_Query(array(
            'post_type'           => 'product',
            'ignore_sticky_posts' => 1,
            'no_found_rows'       => 1,
            'posts_per_page'      => $instance['number'],
            'post__in'            => $upsells,
            'post__not_in'        => array($product->id),
            'meta_query'          => $woocommerce->query->get_meta_query()
        ));
    }
}

class Theme_WooCommerce_Widget_CrossSells_Products extends Theme_WooCommerce_Widget_Product_Slider {

    function widget_custom_init() {
        $this->woo_widget_cssclass = 'widget_crosssells_products';
        $this->woo_widget_description = __( 'Display a list of Cross-Sells products on cart page.', 'woocommerce' );
        $this->woo_widget_idbase = 'woocommerce_crosssells_products';
        $this->woo_widget_name = __('WooCommerce Cross-Sells Products', 'woocommerce' );
        $this->woo_widget_class = 'theme_widget_crosssells_products';
        $this->woo_widget_default_title = __('Cross-Sells Products', 'woocommerce');
        return 'crosssells_products';
    }

    function widget_get_wp_query($instance) {
        global $woocommerce;
        if (!is_cart())
            return null;

        $crosssells = $woocommerce->cart->get_cross_sells();
        if (sizeof($crosssells) == 0)
            return null;

        return new WP_Query(array(
            'post_type'           => 'product',
            'ignore_sticky_posts' => 1,
            'no_found_rows'       => 1,
            'posts_per_page'      => $instance['number'],
            'post__in'            => $crosssells,
            'meta_query'          => $woocommerce->query->get_meta_query()
        ));
    }
}

// init widgets
function theme_woo_widgets_init() {

    if (theme_woocommerce_enabled()) {
        unregister_widget('WC_Widget_Featured_Products');
        unregister_widget('WooCommerce_Widget_Featured_Products');
        register_widget('Theme_WooCommerce_Widget_Featured_Products');

        unregister_widget('WC_Widget_Recently_Viewed');
        unregister_widget('WooCommerce_Widget_Recently_Viewed');
        register_widget('Theme_WooCommerce_Widget_Recently_Viewed');

        unregister_widget('WC_Widget_Best_Sellers');
        unregister_widget('WooCommerce_Widget_Best_Sellers');
        register_widget('Theme_WooCommerce_Widget_Best_Sellers');

        unregister_widget('WC_Widget_Recent_Products');
        unregister_widget('WooCommerce_Widget_Recent_Products');
        register_widget('Theme_WooCommerce_Widget_Recent_Products');

        unregister_widget('WC_Widget_On_Sale');
        unregister_widget('WooCommerce_Widget_On_Sale');
        register_widget('Theme_WooCommerce_Widget_On_Sale');

        unregister_widget('WC_Widget_Random_Products');
        unregister_widget('WooCommerce_Widget_Random_Products');
        register_widget('Theme_WooCommerce_Widget_Random_Products');

        unregister_widget('WC_Widget_Top_Rated_Products');
        unregister_widget('WooCommerce_Widget_Top_Rated_Products');
        register_widget('Theme_WooCommerce_Widget_Top_Rated_Products');

        register_widget('Theme_WooCommerce_Widget_Related_Products');

        register_widget('Theme_WooCommerce_Widget_UpSells_Products');

        register_widget('Theme_WooCommerce_Widget_CrossSells_Products');
    }
}
add_action('widgets_init', 'theme_woo_widgets_init');


?>