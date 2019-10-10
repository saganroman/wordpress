<?php theme_add_template_option('Blog', 'blogTemplate', __('Blog', 'default')); ?>
<?php theme_add_template_query_option('Blog', 'blogTemplate', __('Blog', 'default')); ?>
<?php theme_add_template_option('Home', 'home', __('Home', 'default')); ?>
<?php if (theme_woocommerce_enabled()) theme_add_template_option('Product Overview', 'productOverview', __('Product%20Overview', 'default')); ?>
<?php if (theme_woocommerce_enabled()) theme_add_template_option('Products', 'products', __('Products', 'default')); ?>
<?php theme_add_template_option('404', 'template404', __('404', 'default'), 0); ?>