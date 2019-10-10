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
    </style>
</head>
<body <?php body_class(); ?>>

<div class="container">
    <div class="jumbotron">
        <h3>Blog Template is disabled.</h3>
        <p>
            There can be two reasons:
        </p>

        <ol>
            <li>WordPress shows your posts on the home page of your site. So you need to edit Home template now.</li>
            <li>Blog page is turned off in WordPress options.</li>
        </ol>

        <p>
            If you want to turn on blog page you should do these steps:
        </p>

        <ol>
            <li>
                <strong>Create a Front Page</strong>: In Pages choose <strong>Add New Page</strong>.
                <ul>
                    <li>Title it "Home".</li>
                    <li>Add content you would like to see within the content area of the static front page,
                        or leave it blank if it is a Dynamic front page.</li>
                    <li>Publish the Page.</li>
                </ul>
            </li>
            <li>
                <strong>Create a Blog Page</strong>: If choosing to add a blog, choose <strong>Add New Page</strong> again.
                <ul>
                    <li>Title it "Blog," "News," "Articles," or an appropriate name.</li>
                    <li>DO NOT add content. Leave it blank. Any content here will be ignored -- only the Title is used.</li>
                    <li>Publish the Page.</li>
                </ul>
            </li>
            <li>
                <strong>Go to</strong> <a href="../../../../wp-admin/options-reading.php" target="_blank">Administration > Settings > Reading</a> <strong>panel</strong>.
                <ul>
                    <li>Set <strong>Front page displays</strong> to a <strong>static page</strong>.</li>
                    <li>In the drop down menu for <strong>Front Page</strong> select "Home." </li>
                    <li>In the drop down menu for <strong>Posts page</strong> select "Blog" or the name you created.</li>
                    <li>Save changes.</li>
                </ul>
            </li>
        </ol>

        <p>
            <img src="../images/static/options-reading.png" alt="Reading Options" />
        </p>

    </div>
</div>

<div id="wp-footer">
    <?php wp_footer(); ?>
    <!-- <?php printf(__('%d queries. %s seconds.', 'default'), get_num_queries(), timer_stop(0, 3)); ?> -->
</div>
</body>
</html>