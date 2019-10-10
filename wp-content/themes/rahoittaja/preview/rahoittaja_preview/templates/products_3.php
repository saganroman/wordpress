<?php $GLOBALS['theme_content_function'] = 'theme_products'; ?>
<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<!DOCTYPE html>
<html <?php echo !is_rtl() ? 'dir="ltr" ' : ''; language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset') ?>" />
    
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
    
</head>
<?php do_action('theme_after_head'); ?>
<?php ob_start(); // body start ?>
<body <?php body_class('data-control-id-19 hfeed bootstrap bd-body-3  bd-pagebackground bd-margins'); /*   */ ?>>
<header class="data-control-id-936270 bd-headerarea-1 bd-margins">
        <div class="bd-containereffect-28 container-effect container data-control-id-1478919"><section class="data-control-id-1415044 bd-section-3 bd-tagstyles " id="section3" data-section-title="">
    <div class="bd-container-inner bd-margins clearfix">
        <div class="data-control-id-1414887 bd-layoutbox-3 bd-no-margins clearfix">
    <div class="bd-container-inner">
        <div class="data-control-id-1478485 bd-layoutcontainer-46 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row 
 bd-row-flex 
 bd-row-align-top">
                <div class="data-control-id-1478487 bd-columnwrapper-135 
 col-sm-4">
    <div class="bd-layoutcolumn-135 bd-column" ><div class="bd-vertical-align-wrapper"><a class="bd-imagelink-28  bd-own-margins data-control-id-1478880"  href="https://rahoittaja.sillipilvi.fi">
<img class="data-control-id-1478878 bd-imagestyles" src="<?php echo theme_get_image_path('images/6c0705795bddc867243ee77902e3ca62_logo.png'); ?>">
</a></div></div>
</div>
	
		<div class="data-control-id-1478489 bd-columnwrapper-137 
 col-sm-2">
    <div class="bd-layoutcolumn-137 bd-column" ><div class="bd-vertical-align-wrapper"></div></div>
</div>
	
		<div class="data-control-id-1478513 bd-columnwrapper-79 
 col-sm-3">
    <div class="bd-layoutcolumn-79 bd-column" ><div class="bd-vertical-align-wrapper"><div class="data-control-id-1478796 bd-customhtml-10 bd-tagstyles">
    <div class="bd-container-inner bd-content-element">
        <?php
echo <<<'CUSTOM_CODE'
<h6>0290 300 400</h6>

<p>(Puhelun hinta: 0,0835 €/puh + 0,0691 €/min)</p>
CUSTOM_CODE;
?>
    </div>
</div></div></div>
</div>
	
		<div class="data-control-id-1478498 bd-columnwrapper-78 
 col-sm-3">
    <div class="bd-layoutcolumn-78 bd-column" ><div class="bd-vertical-align-wrapper"><div class="data-control-id-1478564 bd-customhtml-8 bd-tagstyles bd-custom-button">
    <div class="bd-container-inner bd-content-element">
        <?php
echo <<<'CUSTOM_CODE'
<div align="right">
<a href="https://laskurahoitus.rahoittaja.fi/myy-laskusi" target="_blank">
<button type="button">MYY LASKUSI</button>
</a>
</div>
CUSTOM_CODE;
?>
    </div>
</div></div></div>
</div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
    </div>
</section></div>
</header>
	
		<div class="bd-contentlayout-3 bd-sheetstyles data-control-id-330 bd-no-margins bd-margins" >
    <div class="bd-container-inner">

        <div class="bd-flex-vertical bd-stretch-inner bd-contentlayout-offset">
            
            <div class="bd-flex-horizontal bd-flex-wide bd-no-margins">
                
 <?php theme_sidebar_area_5(); ?>
                <div class="bd-flex-vertical bd-flex-wide bd-no-margins">
                    

                    <div class="data-control-id-1118924 bd-layoutitemsbox-17 bd-flex-wide bd-no-margins">
    <div class="data-control-id-54392 bd-content-4">
    
    <?php theme_print_content(); ?>
</div>
</div>

                    
                </div>
                
            </div>
            
        </div>

    </div>
</div>
	
		<footer class="data-control-id-936277 bd-footerarea-1">
    <?php if (theme_get_option('theme_override_default_footer_content')): ?>
        <?php echo do_shortcode(theme_get_option('theme_footer_content')); ?>
    <?php else: ?>
        <div class="bd-containereffect-18 container-effect container data-control-id-1478190"><div class="data-control-id-1477547 bd-layoutcontainer-39  bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class="data-control-id-1477549 bd-columnwrapper-27 
 col-sm-2">
    <div class="bd-layoutcolumn-27 bd-column" ><div class="bd-vertical-align-wrapper"></div></div>
</div>
	
		<div class="data-control-id-1477560 bd-columnwrapper-29 
 col-sm-8">
    <div class="bd-layoutcolumn-29 bd-column" ><div class="bd-vertical-align-wrapper"><p class="data-control-id-1477668 bd-textblock-2 bd-content-element">
    <?php
echo <<<'CUSTOM_CODE'
Corona Capital | Oy Mannerheimintie 20 B, 6. krs, 00100 Helsinki | Y-tunnus: 2559218-8 |&nbsp; info@coronacapital.fi | +358 (0) 290 300 400 (Puhelun hinta: 0,0835 €/puh + 0,0691 €/min) | Avoinna: ma-pe 9-16
CUSTOM_CODE;
?>
</p></div></div>
</div>
	
		<div class="data-control-id-1477551 bd-columnwrapper-28 
 col-sm-2">
    <div class="bd-layoutcolumn-28 bd-column" ><div class="bd-vertical-align-wrapper"></div></div>
</div>
            </div>
        </div>
    </div>
</div></div>
	
		<div class="bd-containereffect-19 container-effect container data-control-id-1478192"><div class="data-control-id-2772 bd-layoutcontainer-28  bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class="data-control-id-2770 bd-columnwrapper-65 
 col-md-12
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-65 bd-column" ><div class="bd-vertical-align-wrapper"><p class="data-control-id-1477722 bd-textblock-4 bd-content-element">
    <?php
echo <<<'CUSTOM_CODE'
Copyright © 2019 Corona Capital Oy Kaikki oikeudet pidätetään  <a href="https://rahoittaja.fi/yrityslainaehdot">Sopimusehdot &nbsp;</a> <a href="https://rahoittaja.fi/yritysrahoituspalvelun-kayttoehdot">Käyttöehdot &nbsp;</a> <a href="https://rahoittaja.fi/rekisteriseloste">Rekisteriseloste &nbsp;</a>  <a href="https://rahoittaja.fi/evasteet">Evästeet</a>
CUSTOM_CODE;
?>
</p>
	
		<div class="data-control-id-1477755 bd-layoutcontainer-41 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class="data-control-id-1477759 bd-columnwrapper-45 
 col-sm-12">
    <div class="bd-layoutcolumn-45 bd-column" ><div class="bd-vertical-align-wrapper"><a class="bd-imagelink-13 bd-no-margins  bd-own-margins data-control-id-1477778"  href="https://www.facebook.com/Rahoittaja">
<img class="data-control-id-1477776 bd-imagestyles" src="<?php echo theme_get_image_path('images/72852ade0d058234544fd1053871d216_facebook.svg'); ?>">
</a>
	
		<a class="bd-imagelink-22 bd-no-margins  bd-own-margins data-control-id-1477829"  href="https://www.linkedin.com/company/rahoittaja-fi">
<img class="data-control-id-1477827 bd-imagestyles" src="<?php echo theme_get_image_path('images/492947bc9b59d766f5e96802fe9e49d2_linkedin.svg'); ?>">
</a>
	
		<a class="bd-imagelink-24 bd-no-margins  bd-own-margins data-control-id-1477842"  href="https://www.youtube.com/channel/UC8gsbvtYw-gfPlg0ebxE2rQ">
<img class="data-control-id-1477840 bd-imagestyles" src="<?php echo theme_get_image_path('images/0811348b020f5e46b471ecd50d81a0e0_youtube.svg'); ?>">
</a></div></div>
</div>
            </div>
        </div>
    </div>
</div></div></div>
</div>
            </div>
        </div>
    </div>
</div></div>
	
		<div class="bd-containereffect-20 container-effect container data-control-id-1478193"><div class="data-control-id-1477419 bd-layoutbox-52  bd-no-margins clearfix">
    <div class="bd-container-inner">
        
	
		<form id="search-14" class="data-control-id-1477446 bd-search-14 form-inline" method="<?php echo isset($_GET['preview']) ? 'post' : 'get'; ?>" name="searchform" action="<?php echo esc_url( home_url() ); ?>/">
    <div class="bd-container-inner">
        <div class="bd-search-wrapper">
            
                <input name="s" type="text" class="data-control-id-1477437 bd-bootstrapinput-20 form-control input-sm" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php _e('Search', 'default'); ?>">
                <a href="#" class="bd-icon-130 bd-icon data-control-id-1477445" link-disable="true"></a>
        </div>
    </div>
    <?php
        $post_type = theme_get_option('theme_search_mode');
        if (!$post_type || $post_type === 'product' && !theme_woocommerce_enabled()) {
            $post_type = 'all';
        }
        if ($post_type !== 'all') {
            echo '<input type="hidden" name="post_type" value="' . $post_type . '" />';
        }
    ?>
    <script>
        (function (jQuery, $) {
            jQuery('.bd-search-14 .bd-icon-130').on('click', function (e) {
                e.preventDefault();
                jQuery('#search-14').submit();
            });
        })(window._$, window._$);
    </script>
</form>
    </div>
</div>
</div>
    <?php endif; ?>
</footer>
	
		<div data-smooth-scroll data-animation-time="250" class="data-control-id-520690 bd-smoothscroll-3"><a href="#" class="data-control-id-2787 bd-backtotop-1 ">
    <span class="bd-icon-67 bd-icon data-control-id-2786"></span>
</a></div>
<div id="wp-footer">
    <?php wp_footer(); ?>
    <!-- <?php printf(__('%d queries. %s seconds.', 'default'), get_num_queries(), timer_stop(0, 3)); ?> -->
</div>
</body>
<?php echo apply_filters('theme_body', ob_get_clean()); // body end ?>
</html>