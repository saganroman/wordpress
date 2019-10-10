<?php
function theme_shopping_cart() {
?>
    <div class=" bd-shoppingcart">
        <div class="bd-container-inner">
<?php
            global $post;
            if (have_posts()) {
                while (have_posts()) {
                    the_post();
?>
                    <div class=" bd-carttitle-1">
    <h2><a href="<?php echo get_permalink($post->ID); ?>" rel="bookmark" title="<?php echo strip_tags(get_the_title()); ?>"><?php the_title(); ?></a></h2>
</div>
<?php
                    echo theme_get_content();
                }
            } else {
                theme_404_content();
            }
?>

        <?php if (theme_woocommerce_enabled()): ?>
            <div class="row">
                <div class="col-md-6">
                    <?php
                        // older versions doesn't print shipping calculator in woocommerce_cart_totals()
                        if (version_compare(WC_VERSION, '2.6', '<')) {
                            ob_start();
                            woocommerce_shipping_calculator();
                            $content = ob_get_clean();

                            if (!theme_is_empty_html($content)) {
                                theme_shopping_cart_block_4_1(
                                    ' bd-block-4 bd-no-margins',
                                    'id="shipping-calculator" ',
                                    __('Calculate Shipping', 'woocommerce'),
                                    $content
                                );
                            }
                        }
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                        ob_start();
?>
                        <?php woocommerce_cart_totals(); ?>
<?php
                        $content = ob_get_clean();

                        if (!theme_is_empty_html($content)) {
                            theme_shopping_cart_block_4_1(
                                ' bd-block-4 bd-no-margins',
                                'id="cart-totals" ',
                                version_compare(WC_VERSION, '3.0.0', '<') ? __('Cart Totals', 'woocommerce') : __('Cart totals', 'woocommerce'),
                                $content
                            );
                        }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
<?php
}

function theme_shopping_cart_block_4_1($class, $attributes, $title, $content){
?>
    <?php theme_shopping_cart_block_4($class, $attributes, $title, $content); ?>
<?php
}