<?php
    require_once( '../../../../wp-load.php' );
    add_filter('show_admin_bar', '__return_false');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset') ?>" />
    
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    
    <script>
    var themeHasJQuery = !!window.jQuery;
</script>
<script src="<?php echo get_bloginfo('template_url', 'display') . '/jquery.js?ver=' . wp_get_theme()->get('Version'); ?>"></script>
<script>
    window._$ = jQuery.noConflict(themeHasJQuery);
</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--[if lte IE 9]>
<link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('template_url', 'display') . '/layout.ie.css' ?>" />
<script src="<?php echo get_bloginfo('template_url', 'display') . '/layout.ie.js' ?>"></script>
<![endif]-->
<link class="data-control-id-9" href='//fonts.googleapis.com/css?family=Fjalla+One:regular&subset=latin' rel='stylesheet' type='text/css'>
<script src="<?php echo get_bloginfo('template_url', 'display') . '/layout.core.js' ?>"></script>
<script src="<?php echo get_bloginfo('template_url', 'display'); ?>/CloudZoom.js?ver=<?php echo wp_get_theme()->get('Version'); ?>" type="text/javascript"></script>
    
    <?php wp_head(); ?>
    
    <style>
        html {
            margin-top: 30px !important;
        }
        body {
            font-family: inherit;
            color:#000000;
            background-color: #FFFFFF;
        }
        .jumbotron {
            background-color: #EEEEEE;
            border-radius: 6px 6px 6px 6px;
            color: inherit;
            font-size: 16px;
            font-weight: 200;
            line-height: 27px;
            margin-bottom: 30px;
            padding: 60px;
        }
        .jumbotron li {
            line-height: 27px;
            white-space: normal;
        }
        .jumbotron img {
            max-width: 100%;
            height: auto;
        }

    </style>
</head>
<body <?php body_class(); ?>>

<div class="container">
    <div class="jumbotron">
        <h3>WooCommerce Page Templates are disabled.</h3>
        <p>There can be two reasons:</p>
        <ol>
            <li><p>You are working in <b>Themler Cloud</b> and have chosen a theme without WooCommerce.</p>
                <p>To enable WooCommerce Page Template please access <b>WordPress Admin Panel</b> and enable <b>WooCommerce plugin</b> under <b>WP Plugins &gt;&gt; Installed Plugins</b>.</p></li>
            <li>You are working in <b>your CMS</b> and you do not have WooCommerce plugin installed.</li>
        </ol>
        <p>To install and activate WooCommerce please use one of available installation options:</p>
        <ul>
            <li><p><b>Automatic Installation:</b></p>
                <ol>
                    <li>To do an automatic installation of WooCommerce log in to your <b>WordPress Admin Panel</b> and go to: <b>Plugins &gt;&gt; Add New</b>.</li>
                    <li>In the search field type <i>"WooCommerce"</i> and click "Search Plugins".

                        <p><img src="../images/static/woo_install_1.png" alt=""></p>
                    </li>
                    <li>Once you have found the plugin click "Install Now" to install the plugin.

                        <p><img src="../images/static/woo_install_2.png" alt=""></p>
                    </li>
                    <li>After clicking that link you will be asked if you are sure you want to install the plugin. </li>
                    <li>Click <b>"yes"</b> and WordPress will automatically complete the installation. </li>
                    <li>Once the installation is complete, activate the plugin by clicking on the "Activate Plugin" link.

                        <p><img src="../images/static/woo_install_3.png" alt=""></p>
                    </li>
                </ol>
            </li>
            <li><b>Manual Installation:</b>
                <ol>
                    <li> <a href="http://wordpress.org/plugins/woocommerce/">Download the plugin</a> to your computer.</li>
                    <li><p>Go to the <b>WordPress Admin Panel &gt;&gt; Plugins &gt;&gt; Add New &gt;&gt; Upload &gt;&gt;</b> click "Browse..." and choose the file. Then click "Install Now".</p>

                        <img src="../images/static/woo_install_4.png" alt="">

                        <p>Once the installation is complete, activate the plugin by clicking on the "Activate Plugin" link.</p>
                    </li>
                </ol>
            </li>
            <li><p>As alternate you can install WooCommerce plugin by uploading it to your webserver via your favourite <b>FTP</b> application.</p> <p>For this upload the unzipped plugin folder to your WordPress installation's <i>wp-content/plugins</i> directory.</p>
                <p>After you have installed the plugin you can activate it under <b>WordPress Plugins &gt;&gt; Installed Plugins</b>.</p>
            </li>
        </ul>
    </div>
</div>

<div id="wp-footer">
    <?php wp_footer(); ?>
    <!-- <?php printf(__('%d queries. %s seconds.', 'default'), get_num_queries(), timer_stop(0, 3)); ?> -->
</div>
</body>
</html>