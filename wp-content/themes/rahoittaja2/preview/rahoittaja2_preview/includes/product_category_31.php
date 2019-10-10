<?php
function wc_category_template_filter_31($template, $template_name, $template_path) {
    if ('content-product_cat.php' === $template_name) {
        $template = theme_get_wc_template_path() . 'content-product_cat_31.php';
    }
    return $template;
}
?>