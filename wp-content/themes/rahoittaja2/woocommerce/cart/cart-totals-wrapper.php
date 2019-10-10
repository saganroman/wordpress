<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

remove_filter('woocommerce_locate_template', 'theme_cart_totals_set_path');
ob_start();

$params = array( 'template', isset($template_name) ? $template_name : '', '', '');
if (version_compare(WC_VERSION, '3.6.2', '>')) {
    array_push($params, WC_VERSION);
}
wp_cache_set(sanitize_key(implode('-', $params)), '', 'woocommerce');

woocommerce_cart_totals();
add_filter('woocommerce_locate_template', 'theme_cart_totals_set_path', 10, 2);
$table = str_replace(
    array(
        'class="cart_totals',
        '<table cellspacing="0"',
        '<h2>' . (version_compare(WC_VERSION, '3.0.0', '<') ? __('Cart Totals', 'woocommerce') : __('Cart totals', 'woocommerce')) . '</h2>',
        '<tbody>',
        '</tbody>'
    ),
    array(
        'class=" bd-shoppingcartgrandtotal-1 cart_totals cart-totals grand-totals',
        '<table cellspacing="0" class=" bd-table-4"',
        '',
        '',
        ''
    ),
    ob_get_clean()
);
$table = preg_replace('#(<tr class="cart-subtotal">.*<\/tr>)#Us', '<thead>$1</thead>', $table); // add thead
$table = preg_replace('#<tr class="(order-total|total)">(.*)<\/tr>#Us', '<tfoot><tr class=" bd-container-34 bd-tagstyles">$2</tr></tfoot>', $table); // add tfoot
$table = preg_replace('#(<thead>.*<\/thead>)(.*)(<tfoot>.*<\/tfoot>)#Us', '$1 $3<tbody>$2</tbody>', $table); // add tbody
echo $table;