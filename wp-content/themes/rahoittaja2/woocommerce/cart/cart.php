<?php
/**
 * Cart Page
 *
 * @version     3.3.0
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $woocommerce;
?>

<?php function_exists('wc_print_notices') ? wc_print_notices() : $woocommerce->show_messages(); ?>

<?php global $woocommerce; ?>
<?php $is_wc3 = version_compare($woocommerce->version, '3.0.0', '>='); ?>
<form action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post" class=" bd-shoppingcarttable-1">
    <?php do_action('woocommerce_before_cart_table'); ?>
    <div class="table-responsive">
        <table class=" bd-table shop_table shop_table_responsive cart">
            <thead>
                <tr>
                    <th class="product-thumbnail">&nbsp;</th>
                    <th class="product-name"><?php _e('Product', 'woocommerce'); ?></th>
                    <th class="product-price"><?php _e('Price', 'woocommerce'); ?></th>
                    <th class="product-quantity"><?php _e('Quantity', 'woocommerce'); ?></th>
                    <th class="product-subtotal"><?php _e('Total', 'woocommerce'); ?></th>
                    <th class="product-remove">&nbsp;</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="6" class="actions">
                        <div class=" bd-container-53 bd-tagstyles bd-custom-button">
                            <input class=" bd-button-15" type="submit" name="update_cart" value="<?php $is_wc3 ? _e('Update cart', 'woocommerce') : _e('Update Cart', 'woocommerce'); ?>" />
                            <a href="<?php echo wc_get_checkout_url(); ?>" class=" bd-button-15"><?php $is_wc3 ? _e('Proceed to checkout', 'woocommerce') : _e('Proceed to Checkout', 'woocommerce'); ?></a>
                            <?php
                                remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout'); // remove default button
                                remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
                                do_action('woocommerce_proceed_to_checkout');
                                echo theme_get_wc_nonce_field('cart');

                                if (wc_coupons_enabled()) { ?>
                                    <div class="form-inline form-responsive-dependent-float">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="coupon_code" id="coupon_code" value="" placeholder="<?php $is_wc3 ? _e('Coupon code', 'woocommerce') : _e('Coupon', 'woocommerce'); ?>" />
                                        </div>
                                        <input class=" bd-button-15" type="submit" name="apply_coupon" value="<?php $is_wc3 ? _e('Apply coupon', 'woocommerce') : _e('Apply Coupon', 'woocommerce'); ?>"/>
                                    </div>
                                    <?php do_action('woocommerce_cart_coupon');
                                }
                            ?>
                        </div>
                    </td>
                </tr>
            </tfoot>

        <tbody>
            <?php do_action('woocommerce_before_cart_contents'); ?>

            <?php
            if (sizeof($woocommerce->cart->get_cart()) > 0) {
                $i = 1;
                foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);//fail

                    if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                        ?>
                        <tr class="<?php if ($i % 2 === 0): ?>alt<?php endif ?> <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

                            <!-- The thumbnail -->
                            <td class="product-thumbnail">
                                <?php
                                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('shop_thumbnail', array('class' => ' bd-imagestyles')), $cart_item, $cart_item_key ); /**/ ?>
                                    <a href="<?php echo esc_url(get_permalink($product_id)); ?>"><?php echo $thumbnail; ?></a>
                            </td>


                            <!-- Product Name -->
                            <td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
                                <div class=" bd-producttext-15">
    <?php
        if (!$_product->is_visible()) {
            echo apply_filters('woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key);
        } else {
            printf('<a href="%s">%s</a>', esc_url( get_permalink( $product_id ) ), apply_filters('woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key));
        }

        // Meta data
        echo wc_get_formatted_cart_item_data($cart_item);

        // Backorder notification
        if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
            echo '<p class="backorder_notification">' . __('Available on backorder', 'woocommerce') . '</p>';
        }
    ?>
</div>
                            </td>

                            <!-- Product price -->
                            <td class="product-price" data-title="<?php esc_attr_e('Price', 'woocommerce'); ?>">
                                <?php
                                    $product_price = get_option('woocommerce_display_cart_prices_excluding_tax') == 'yes' || $woocommerce->customer->is_vat_exempt() ? $_product->get_price_excluding_tax() : $_product->get_price();
                                    echo apply_filters('woocommerce_cart_item_price_html', wc_price($product_price), $cart_item, $cart_item_key);
                                ?>
                            </td>

                            <!-- Quantity inputs -->
                            <td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
                                <?php
                                    if ($_product->is_sold_individually()) {
                                        $product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
                                    } else {
                                        $data_min = apply_filters('woocommerce_cart_item_data_min', '', $_product);
                                        $data_max = $_product->backorders_allowed() ? '' : $_product->get_stock_quantity();
                                        $data_max = apply_filters('woocommerce_cart_item_data_max', $data_max, $_product);

                                        $product_quantity = sprintf('<div class="quantity"><input type="%s" name="cart[%s][qty]" data-min="%s" data-max="%s" value="%s" size="4" title="Qty" class="qty  bd-bootstrapinput form-control" maxlength="12" /></div>', theme_wc_quantity_buttons_supported() ? 'text' : 'number', $cart_item_key, $data_min, $data_max, esc_attr($cart_item['quantity']));
                                    }
                                    echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key);
                                ?>
                            </td>

                            <!-- Product subtotal -->
                            <td class="product-subtotal" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
                                <?php
                                    echo apply_filters('woocommerce_cart_item_subtotal', $woocommerce->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key);
                                ?>
                            </td>

                            <!-- Remove from cart link -->
                            <td class="product-remove">
                                <?php
                                    $href = esc_url(wc_get_cart_remove_url($cart_item_key) . '&_wp_http_referer=' . urlencode(wc_get_cart_url()));
                                    echo apply_filters('woocommerce_cart_item_remove_link', sprintf(
                                        '<a href="%s" class="removelink 
 bd-icon-69 bd-icon " title="%s" data-product_id="%s" data-product_sku="%s"></a>',
                                        $href,
                                        __('Remove this item', 'woocommerce'),
                                        esc_attr($product_id),
                                        esc_attr($_product->get_sku())
                                    ), $cart_item_key);
                                ?>
                            </td>
                        </tr>
                    <?php
                        $i++;
                    }
                }
            }

            do_action('woocommerce_cart_contents');
            do_action('woocommerce_after_cart_contents');
            ?>
        </tbody>
    </table>
    </div>
    <?php do_action('woocommerce_after_cart_table'); ?>
</form>


<div class="cart-collaterals">

    <?php theme_do_action('woocommerce_cart_collaterals', array(
        array('woocommerce_cross_sell_display', 10), // 2.1.0
        array('woocommerce_cart_totals', 10) // 2.1.0
    )); ?>

</div>

<?php do_action('woocommerce_after_cart'); ?>