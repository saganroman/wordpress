<?php
function theme_tab_reviews_2() {
    global $woocommerce;
    remove_filter('comments_template', array($woocommerce, 'comments_template_loader'));
    remove_filter('comments_template', array('WC_Template_Loader', 'comments_template_loader'));
    if (comments_open()) {
        comments_template('/product_reviews_1.php');
    }
}
?>