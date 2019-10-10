<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!defined('WC_VERSION')) {
    global $woocommerce;
    define('WC_VERSION', $woocommerce->version);
}

function theme_manage_woocommerce_styles() {
    remove_action('wp_head', array($GLOBALS['woocommerce'], 'generator'));
    wp_dequeue_script('wc-single-product');
}
add_action('wp_enqueue_scripts', 'theme_manage_woocommerce_styles', 99);

function theme_woocommerce_get_script_data($params) {
    if (isset($params['ajax_url']))
        $params['ajax_url'] = theme_add_preview_args($params['ajax_url']);
    if (isset($params['checkout_url']))
        $params['checkout_url'] = theme_add_preview_args($params['checkout_url']);
    if (isset($params['cart_url']))
        $params['cart_url'] = theme_add_preview_args($params['cart_url']);
    if (isset($params['redirect']))
        $params['redirect'] = theme_add_preview_args($params['redirect']);
    return $params;
}

function theme_review_form_params($params) {
    $params['i18n_required_rating_text'] = esc_attr__('Please select a rating', 'woocommerce');
    $params['i18n_required_comment_text'] = esc_attr__('Please type a comment', 'woocommerce');
    $params['review_rating_required'] = get_option('woocommerce_review_rating_required');
    return $params;
}
add_filter(version_compare(WC_VERSION, '3.3.3', '<') ? 'woocommerce_params' : 'woocommerce_get_script_data', 'theme_review_form_params');

if (theme_can_view_preview()) {
    add_filter(version_compare(WC_VERSION, '3.3.3', '<') ? 'woocommerce_params' : 'woocommerce_get_script_data', 'theme_woocommerce_get_script_data');
    add_filter('wc_checkout_params', 'theme_woocommerce_get_script_data');
    add_filter('wc_cart_params', 'theme_woocommerce_get_script_data');
    add_filter('wc_cart_fragments_params', 'theme_woocommerce_get_script_data');
    add_filter('wc_add_to_cart_params', 'theme_woocommerce_get_script_data');
    add_filter('woocommerce_payment_successful_result', 'theme_woocommerce_get_script_data');
}

function theme_get_wc_template_path() {
    global $woocommerce;
    $template_path = method_exists($woocommerce, 'template_path') ? $woocommerce->template_path() : $woocommerce->template_url;
    return get_stylesheet_directory() . '/' . $template_path;
}

function theme_cart_totals_set_path($located, $template_name) {
    // support old WC versions
    if ($template_name === 'cart/totals.php' || $template_name === 'cart/cart-totals.php') {
        $located = theme_get_wc_template_path() . 'cart/cart-totals-wrapper.php';
    }
    return $located;
}
add_filter('woocommerce_locate_template', 'theme_cart_totals_set_path', 10, 2);

function theme_shipping_calculator_set_path($located, $template_name) {
    if ($template_name === 'cart/shipping-calculator.php') {
        $located = theme_get_wc_template_path() . 'cart/shipping-calculator-wrapper.php';
    }
    return $located;
}
add_filter('woocommerce_locate_template', 'theme_shipping_calculator_set_path', 10, 2);

function theme_add_to_cart_message($message) {
    return str_replace('button wc-forward', '', $message);
}
add_filter('woocommerce_add_error', 'theme_add_to_cart_message');
add_filter('woocommerce_add_success', 'theme_add_to_cart_message');

function theme_clear_cart_after_payment() {
    // woocommerce adds action to get_header
    // until we don't use get_header(), call wc_clear_cart_after_payment manually
    if (function_exists('wc_clear_cart_after_payment') && has_action('get_header', 'wc_clear_cart_after_payment')) {
        wc_clear_cart_after_payment();
    }
}
add_action('theme_after_head', 'theme_clear_cart_after_payment');

function theme_shop_products_per_page() {
    $items = theme_get_option( 'theme_shop_products_per_page' );
    return $items == 0 ? -1 : $items;
}
add_filter('loop_shop_per_page', 'theme_shop_products_per_page');

if (!function_exists('get_woocommerce_currencies')) {
    function get_woocommerce_currencies() {
        return array_unique(
            apply_filters( 'woocommerce_currencies',
                array(
                    'AUD' => __( 'Australian Dollars', 'woocommerce' ),
                    'BRL' => __( 'Brazilian Real', 'woocommerce' ),
                    'CAD' => __( 'Canadian Dollars', 'woocommerce' ),
                    'RMB' => __( 'Chinese Yuan', 'woocommerce' ),
                    'CZK' => __( 'Czech Koruna', 'woocommerce' ),
                    'DKK' => __( 'Danish Krone', 'woocommerce' ),
                    'EUR' => __( 'Euros', 'woocommerce' ),
                    'HKD' => __( 'Hong Kong Dollar', 'woocommerce' ),
                    'HUF' => __( 'Hungarian Forint', 'woocommerce' ),
                    'IDR' => __( 'Indonesia Rupiah', 'woocommerce' ),
                    'INR' => __( 'Indian Rupee', 'woocommerce' ),
                    'ILS' => __( 'Israeli Shekel', 'woocommerce' ),
                    'JPY' => __( 'Japanese Yen', 'woocommerce' ),
                    'KRW' => __( 'South Korean Won', 'woocommerce' ),
                    'MYR' => __( 'Malaysian Ringgits', 'woocommerce' ),
                    'MXN' => __( 'Mexican Peso', 'woocommerce' ),
                    'NOK' => __( 'Norwegian Krone', 'woocommerce' ),
                    'NZD' => __( 'New Zealand Dollar', 'woocommerce' ),
                    'PHP' => __( 'Philippine Pesos', 'woocommerce' ),
                    'PLN' => __( 'Polish Zloty', 'woocommerce' ),
                    'GBP' => __( 'Pounds Sterling', 'woocommerce' ),
                    'RON' => __( 'Romanian Leu', 'woocommerce' ),
                    'RUB' => __( 'Russian Ruble', 'woocommerce' ),
                    'SGD' => __( 'Singapore Dollar', 'woocommerce' ),
                    'ZAR' => __( 'South African rand', 'woocommerce' ),
                    'SEK' => __( 'Swedish Krona', 'woocommerce' ),
                    'CHF' => __( 'Swiss Franc', 'woocommerce' ),
                    'TWD' => __( 'Taiwan New Dollars', 'woocommerce' ),
                    'THB' => __( 'Thai Baht', 'woocommerce' ),
                    'TRY' => __( 'Turkish Lira', 'woocommerce' ),
                    'USD' => __( 'US Dollars', 'woocommerce' ),
                )
            )
        );
    }
}

function theme_get_currency_title($textType, $showLabel, $showArrow) {
    $label = $showLabel ? __('Currency', 'default') : '';
    $currency = get_woocommerce_currency();
    $symbol = get_woocommerce_currency_symbol($currency);

    if ($textType === 'noText')
        $value = $symbol;
    elseif ($textType === 'short')
        $value = $currency;
    elseif ($textType === 'full')
        $value = $symbol . ' ' . theme_get_woocommerce_currency_full_name($currency);

    if ($value && $label)
        $label .= ': ';
    $title = $label . $value;

    $title = '<span>' . $title . '</span>';
    if ($showArrow)
        $title .= ' <span class="caret"></span>';
    return $title;
}

function theme_get_woocommerce_currency_full_name($currency) {
    $currencies = get_woocommerce_currencies();
    return $currencies[$currency];
}

function set_new_currency( $currency ) {
    if (isset($_COOKIE['currency'])) {
        $new_currency = $_COOKIE['currency'];
        $currencies = get_woocommerce_currencies();
        if (isset($currencies[$new_currency]))
            $currency = $new_currency;
    }
    return $currency;
}
add_filter('woocommerce_currency', 'set_new_currency');

/**
 * Determine if need to show add-to-cart button
 * And returns product variations content without that button
 *
 * @return array
 *  string content
 *  bool show_button
 */
function theme_wc_get_variations() {
    global $_theme_wc_variations, $product;
    if (!isset($_theme_wc_variations)) {

        add_action('woocommerce_before_add_to_cart_button', '_theme_print_add_to_cart_seperator', 100);
        add_action('woocommerce_after_add_to_cart_button', '_theme_print_add_to_cart_seperator', 1);
        ob_start();
        woocommerce_template_single_add_to_cart();
        $content = ob_get_clean();
        remove_action('woocommerce_before_add_to_cart_button', '_theme_print_add_to_cart_seperator', 100);
        remove_action('woocommerce_after_add_to_cart_button', '_theme_print_add_to_cart_seperator', 1);

        $show_button = false;

        $parts = explode('%ADD_TO_CART%', $content);
        $button_re = '/<button type="submit".+button>/U';
        if (count($parts) === 3 && preg_match($button_re, $parts[1], $m)) {
            $show_button = true;
            if (strpos($m[0], 'name="add-to-cart"') !== false) {
                // in default template since 3.0.0 name="add-to-cart" is assigned to button
                // so we add hidden input instead
                $parts[1] = preg_replace($button_re, '<input type="hidden" name="add-to-cart" value="' . $product->get_id() . '" />', $parts[1]);
            } else {
                $parts[1] = preg_replace($button_re, '', $parts[1]);
            }
        }

        $_theme_wc_variations = array(
            'content' => implode('', $parts),
            'show_button' => $show_button
        );
    }
    return $_theme_wc_variations;
}

function _theme_print_add_to_cart_seperator() {
    echo "%ADD_TO_CART%";
}

function theme_wc_disabled_button_supported() {
    // The cart button will display (disabled) when no selections are made.
    global $woocommerce;
    return version_compare($woocommerce->version, '2.5.0', '>=');
}

function theme_wc_quantity_buttons_supported() {
    // Removed quantity increment/decrement buttons
    global $woocommerce;
    return version_compare($woocommerce->version, '2.3.0', '<');
}

function theme_single_product_buy($class, $attributes, $context) {
    global $product;
    $product_type = method_exists($product, 'get_type') ? $product->get_type() : $product->product_type;

    $additional_style = '';
    $additional_script = '';
    if ($product_type === 'variable') {

        if (!theme_wc_disabled_button_supported()) {
            $additional_style = ' style="display:none;"';
            $class .= ' wc-add-to-cart';

            $additional_script = <<<EOL
                <script>
                    jQuery(document).on('show_variation', 'form.cart', function() {
                        jQuery('.wc-add-to-cart').slideDown(200);
                    });
                </script>
EOL;
        } else {
            $class .= ' wc-add-to-cart';

            $additional_script = <<<EOL
            <script>
                var btn = jQuery('.wc-add-to-cart.single_add_to_cart_button');
                jQuery('form.cart').on('hide_variation', function() {
                    btn.attr('disabled', 'disabled').attr('title', wc_add_to_cart_variation_params.i18n_make_a_selection_text);
                }).on('show_variation', function(event, variation, purchasable) {
                    if (purchasable) {
                        btn.removeAttr('disabled').removeAttr('title');
                    } else {
                        btn.attr('disabled', 'disabled').attr('title', wc_add_to_cart_variation_params.i18n_unavailable_text);
                    }
                });
            </script>
EOL;
        }
    }
    $variations = theme_wc_get_variations();
    if ($variations['show_button']) {
        ?>
        <button onclick="jQuery('<?php echo $context; ?> [class*=bd-productvariations] form.cart').submit()" <?php echo $attributes; ?> class="<?php echo $class; ?> single_add_to_cart_button"<?php echo $additional_style; ?>>
            <?php
            echo method_exists($product, 'single_add_to_cart_text')
                ? $product->single_add_to_cart_text()
                :  _e('Add to cart', 'woocommerce');
            ?>
        </button>
        <?php
    }
    echo $additional_script;
}

function theme_product_buy($class, $attributes) {
    if (!apply_filters('theme_show_single_add_to_cart', true))
        return; // if plugin disabled the button

    global $product_overview_context;
    if (!empty($product_overview_context)) { // this button located in product-overview
        theme_single_product_buy($class, $attributes, $product_overview_context);
        return;
    }

    if (function_exists('woocommerce_template_loop_add_to_cart')) {
        ob_start();
        woocommerce_template_loop_add_to_cart();
        $result = ob_get_clean();
        if (preg_match('#class="([^"]*?)"#', $result, $matches)) {
            $classes = array_diff(explode(' ', $matches[1]), array('button'));
            $classes[] = $class;
            $result = str_replace($matches[0], $attributes . ' class="' . implode(' ', $classes) . '"', $result);
        }
        echo $result;
    }
}

function theme_product_image($product_view, $class, $attributes) {
    if (isset($product_view['image'])) {
        $image = $product_view['image'];
        if (strpos($image, 'class') === false) {
            $image = str_replace('<img', '<img ' . $attributes . ' class=" ' . $class . ' "', $image);
        } else {
            $image = preg_replace('/class([ \t]*)=([ \t]*)([\"\'])/', $attributes . ' class=$3 ' . $class . ' ', $image);
        }
        echo $image;
    }
}

function theme_get_product_thumbnails_data(){
    global $product;

    if (method_exists($product, 'get_gallery_image_ids')) {
        $attachments_ids = $product->get_gallery_image_ids();
    } else if (method_exists($product, 'get_gallery_attachment_ids')) {
        $attachments_ids = $product->get_gallery_attachment_ids();
    } else if (function_exists('get_post_thumbnail_id')) {
        $attachments_ids = array(get_post_thumbnail_id());
    } else {
        $attachments_ids = array();
    }

    $images = array();
    if ($attachments_ids) {
        foreach ( $attachments_ids as $key => $attachment_id ) {
            if ( get_post_meta( $attachment_id, '_woocommerce_exclude_image', true ) == 1 )
                continue;
            $images[] = array(
                'url' => wp_get_attachment_url($attachment_id),
                'title' => esc_attr(get_the_title($attachment_id)),
                'src' => theme_get_array_value(wp_get_attachment_image_src($attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ), false), 0),
                'preview' => theme_get_array_value(wp_get_attachment_image_src($attachment_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), false), 0)
            );
        }
    }
    return $images;
}

function theme_woocommerce_placeholder_img($html, $side, $dimensions) {
    if (isset($dimensions['width'])) {
        $html = str_replace('<img', '<img style="max-width:' . $dimensions['width'] . 'px;"', $html);
    }
    return $html;
}
add_filter('woocommerce_placeholder_img', 'theme_woocommerce_placeholder_img', 10, 3);

if (!has_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart')) {
    add_filter('theme_show_single_add_to_cart', '__return_false');
}

function theme_get_wc_nonce_field($action) {
    global $woocommerce;
    return method_exists($woocommerce, 'nonce_field') ? $woocommerce->nonce_field($action, true, false) : wp_nonce_field("woocommerce-$action");
}

function theme_change_cart_page_template() {
    if (is_page()) {
        global $post;
        if (function_exists('wc_get_page_id') && wc_get_page_id('cart') == $post->ID && !is_page_template('cart.php')) {
            update_post_meta($post->ID, '_wp_page_template', 'cart.php');
        }
    }
}
add_action('wp', 'theme_change_cart_page_template');

/**
 * Split original woocommerce html into old and current prices
 * Suffix applies to current price
 *
 * @param $price_data
 * @return array
 *  string price
 *  string old_price
 */
function theme_parse_price_html($price_data) {
    $old_price_html = '';

    if (preg_match('/<del>(.*)<\/del>/', $price_data, $del_matches)) {
        $old_price_html = $del_matches[1];
        $price_data = str_replace($del_matches[0], '', $price_data);
    }
    $price_html = preg_replace('#<\/?ins>#', '', $price_data);

    return array(
        'price' => $price_html,
        'old_price' => $old_price_html,
    );
}


/**
 * Split $price_html into label and amount
 *
 * <span class="amount">Free!</span>
 * label=
 * amount=<span class="amount">Free!</span>
 *
 * Free!
 * label=
 * amount=<span class="amount">Free!</span>
 *
 * <span class="amount">$3.30</span>
 * label=Price:
 * amount=<span class="amount">$3.30</span>
 *
 *
 * NOTE:
 * - do not show label if product is free
 * - do not show label if it has "From:"
 *
 * @param $price_html
 * @return array
 *  string label
 *  string amount
 */
function theme_parse_label_and_amount($price_html) {
    if (!trim($price_html)) {
        return array(
            'label' => '',
            'amount' => '',
        );
    }

    $is_free = strpos($price_html, __('Free!', 'woocommerce')) !== false;
    $show_label = !$is_free;

    if (!theme_html_has_class($price_html, 'amount')) {
        $price_html = '<span class="amount">' . $price_html . '</span>';
        $show_label = false;
    }

    if (theme_html_has_class($price_html, 'from')) {
        $price_html = theme_html_replace_class($price_html, 'from', 'amount');
        $show_label = false;
    }

    return array(
        'label' => $show_label ? __('Price:', 'woocommerce') : '',
        'amount' => $price_html,
    );
}

/**
 * @param array $args
 *  string        wrap_start
 *  string        wrap_end
 *  string|false  label_class
 *  string        label_attributes
 *  string        amount_class
 *  string        amount_attributes
 * @param $price_html
 * @return string
 */
function theme_generate_price($args, $price_html) {
    $data = theme_parse_label_and_amount($price_html);
    $label_html = $data['label'];
    $amount_html = $data['amount'];

    if (!$label_html && !$amount_html) {
        return '';
    }

    $return = '';
    if ($label_html && $args['label_class']) {
        $return .= '<span ' . $args['label_attributes'] . ' class="' . $args['label_class'] . '">' . $label_html . '</span>';
    }
    $amount_html = theme_html_replace_class($amount_html, 'amount', $args['amount_class'], $args['amount_attributes']);
    $return .= $amount_html;

    return $args['wrap_start'] . $return . $args['wrap_end'];
}

/**
 * Return final final control result by provided options
 *
 * @param array $args
 *  array   price
 *  array   old_price
 *  string  price_data - original woocommerce html
 *  bool    swap_old_regular - show current price first or not
 *  bool    show_old_price - show old price or not
 * @return string
 */
function theme_price_html($args) {
    $price_data = $args['price_data'];

    $swap_old_regular = $args['swap_old_regular'];
    $show_old_price = $args['show_old_price'];

    $price_parts = theme_parse_price_html($price_data);
    $price_result = theme_generate_price($args['price'], $price_parts['price']);
    $old_price_result = theme_generate_price($args['old_price'], $price_parts['old_price']);

    if (!$show_old_price) {
        $old_price_result = '';
    }

    $result = $swap_old_regular ? "$old_price_result$price_result" : "$price_result$old_price_result";
    // do not apply 'woocommerce_get_price_html' filter since it applied in $product->get_price_html
    return $result;
}

/**
 * Returns original product price html
 *
 * @param $product
 * @return string
 */
function theme_get_price_data($product) {
    return $product->get_price_html();
}

if (!function_exists('wc_coupons_enabled')) {
    // < 2.5.0 compatibility
    function wc_coupons_enabled() {
        return get_option('woocommerce_enable_coupons') == 'yes' && get_option('woocommerce_enable_coupon_form_on_cart') == 'yes';
    }
}

if (!function_exists('wc_get_page_id')) {
    // too old versions compatibility
    function wc_get_page_id($page) {
        return woocommerce_get_page_id($page);
    }
}

if (!function_exists('wc_price')) {
    // too old versions compatibility
    function wc_price($price, $args = array()) {
        return woocommerce_price($price, $args);
    }
}

if (!function_exists('wc_get_cart_url')) {
    // too old versions compatibility
    function wc_get_cart_url() {
        global $woocommerce;
        return $woocommerce->cart->get_cart_url();
    }
}

if (!function_exists('wc_get_checkout_url')) {
    // too old versions compatibility
    function wc_get_checkout_url() {
        global $woocommerce;
        return $woocommerce->cart->get_checkout_url();
    }
}

function theme_init_polyfills() {
    if (!function_exists('wc_get_formatted_cart_item_data')) {
        // < 3.3.0 compatibility
        function wc_get_formatted_cart_item_data($cart_item, $flat=false) {
            global $woocommerce;
            return $woocommerce->cart->get_item_data($cart_item, $flat);
        }
    }

    if (!function_exists('wc_get_cart_remove_url')) {
        // < 3.3.0 compatibility
        function wc_get_cart_remove_url($cart_item_key) {
            global $woocommerce;
            return $woocommerce->cart->get_remove_url($cart_item_key);
        }
    }
}
add_action('init', 'theme_init_polyfills');