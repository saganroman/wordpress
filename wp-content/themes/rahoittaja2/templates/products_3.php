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
<script src="<?php echo get_bloginfo('template_url', 'display') . '/layout.core.js' ?>"></script>
<script src="<?php echo get_bloginfo('template_url', 'display'); ?>/CloudZoom.js?ver=<?php echo wp_get_theme()->get('Version'); ?>" type="text/javascript"></script>
    
    <?php wp_head(); ?>
    
</head>
<?php do_action('theme_after_head'); ?>
<?php ob_start(); // body start ?>
<body <?php body_class(' hfeed bootstrap bd-body-3  bd-pagebackground bd-margins'); /*   */ ?>>
<header class=" bd-headerarea-1 bd-margins">
        <div data-affix
     data-offset=""
     data-fix-at-screen="top"
     data-clip-at-control="top"
     
 data-enable-lg
     
 data-enable-md
     
 data-enable-sm
     
     class=" bd-affix-1 bd-no-margins bd-margins "><section class=" bd-section-3 bd-tagstyles  " id="section3" data-section-title="">
    <div class="bd-container-inner bd-margins clearfix">
        <div class=" bd-layoutbox-3 bd-no-margins clearfix">
    <div class="bd-container-inner">
        <div class=" bd-layoutcontainer-46 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row 
 bd-row-flex 
 bd-row-align-top">
                <div class=" bd-columnwrapper-135 
 col-sm-4">
    <div class="bd-layoutcolumn-135 bd-column" ><div class="bd-vertical-align-wrapper"><a class="bd-imagelink-28  bd-own-margins "  href="https://rahoittaja.fi">
<img class=" bd-imagestyles" src="<?php echo theme_get_image_path('images/6c0705795bddc867243ee77902e3ca62_logo.png'); ?>">
</a></div></div>
</div>
	
		<div class=" bd-columnwrapper-137 
 col-sm-4">
    <div class="bd-layoutcolumn-137 bd-column" ><div class="bd-vertical-align-wrapper"><?php
    if (theme_get_option('theme_use_default_menu')) {
        wp_nav_menu( array('theme_location' => 'primary-menu-3') );
    } else {
        theme_hmenu_3();
    }
?></div></div>
</div>
	
		<div class=" bd-columnwrapper-79 
 col-sm-4">
    <div class="bd-layoutcolumn-79 bd-column" ><div class="bd-vertical-align-wrapper"><div class=" bd-customhtml-10 bd-tagstyles">
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
            </div>
        </div>
    </div>
</div>
    </div>
</div>
    </div>
</section></div>
</header>
	
		<div class="bd-contentlayout-3 bd-sheetstyles  bd-no-margins bd-margins" >
    <div class="bd-container-inner">

        <div class="bd-flex-vertical bd-stretch-inner bd-contentlayout-offset">
            
            <div class="bd-flex-horizontal bd-flex-wide bd-no-margins">
                
 <?php theme_sidebar_area_5(); ?>
                <div class="bd-flex-vertical bd-flex-wide bd-no-margins">
                    

                    <div class=" bd-layoutitemsbox-17 bd-flex-wide bd-no-margins">
    <div class=" bd-content-4">
    
    <?php theme_print_content(); ?>
</div>
</div>

                    
                </div>
                
            </div>
            
        </div>

    </div>
</div>
	
		<footer class=" bd-footerarea-1">
    <?php if (theme_get_option('theme_override_default_footer_content')): ?>
        <?php echo do_shortcode(theme_get_option('theme_footer_content')); ?>
    <?php else: ?>
        <div class=" bd-layoutcontainer-39 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class=" bd-columnwrapper-27 
 col-sm-2">
    <div class="bd-layoutcolumn-27 bd-column" ><div class="bd-vertical-align-wrapper"></div></div>
</div>
	
		<div class=" bd-columnwrapper-29 
 col-sm-8">
    <div class="bd-layoutcolumn-29 bd-column" ><div class="bd-vertical-align-wrapper"><p class=" bd-textblock-2 bd-content-element">
    <?php
echo <<<'CUSTOM_CODE'
Corona Capital Oy |&nbsp; Yliopistonkatu 5, 8. krs, 00100 Helsinki | Y-tunnus: 2559218-8 |&nbsp; info@coronacapital.fi |<br>+358 (0) 290 300 400 (Puhelun hinta: 0,0835 €/puh + 0,0691 €/min) | Avoinna: ma-pe 9-16
CUSTOM_CODE;
?>
</p></div></div>
</div>
	
		<div class=" bd-columnwrapper-28 
 col-sm-2">
    <div class="bd-layoutcolumn-28 bd-column" ><div class="bd-vertical-align-wrapper"></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-28 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class=" bd-columnwrapper-65 
 col-md-12
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-65 bd-column" ><div class="bd-vertical-align-wrapper"><p class=" bd-textblock-4 bd-content-element">
    <?php
echo <<<'CUSTOM_CODE'
Copyright © 2019 Corona Capital Oy Kaikki oikeudet pidätetään  <a href="https://rahoittaja.fi/yrityslainaehdot">Sopimusehdot &nbsp;</a> <a href="https://rahoittaja.fi/yritysrahoituspalvelun-kayttoehdot">Käyttöehdot &nbsp;</a> <a href="https://rahoittaja.fi/rekisteriseloste">Rekisteriseloste &nbsp;</a>  <a href="https://rahoittaja.fi/evasteet">Evästeet</a>
CUSTOM_CODE;
?>
</p>
	
		<div class=" bd-layoutcontainer-41 bd-columns bd-no-margins">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row ">
                <div class=" bd-columnwrapper-45 
 col-sm-12">
    <div class="bd-layoutcolumn-45 bd-column" ><div class="bd-vertical-align-wrapper"><a class="bd-imagelink-13 bd-no-margins  bd-own-margins "  href="https://www.facebook.com/Rahoittaja">
<img class=" bd-imagestyles" src="<?php echo theme_get_image_path('images/72852ade0d058234544fd1053871d216_facebook.svg'); ?>">
</a>
	
		<a class="bd-imagelink-22 bd-no-margins  bd-own-margins "  href="https://www.linkedin.com/company/rahoittaja-fi">
<img class=" bd-imagestyles" src="<?php echo theme_get_image_path('images/492947bc9b59d766f5e96802fe9e49d2_linkedin.svg'); ?>">
</a>
	
		<a class="bd-imagelink-24 bd-no-margins  bd-own-margins "  href="https://www.youtube.com/channel/UC8gsbvtYw-gfPlg0ebxE2rQ">
<img class=" bd-imagestyles" src="<?php echo theme_get_image_path('images/0811348b020f5e46b471ecd50d81a0e0_youtube.svg'); ?>">
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
</div>
	
		<div class=" bd-layoutbox-52 bd-no-margins clearfix">
    <div class="bd-container-inner">
        
	
		<form id="search-14" class=" bd-search-14 form-inline" method="<?php echo isset($_GET['preview']) ? 'post' : 'get'; ?>" name="searchform" action="<?php echo esc_url( home_url() ); ?>/">
    <div class="bd-container-inner">
        <div class="bd-search-wrapper">
            
                <input name="s" type="text" class=" bd-bootstrapinput-20 form-control input-sm" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php _e('Search', 'default'); ?>">
                <a href="#" class="bd-icon-130 bd-icon " link-disable="true"></a>
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
    <?php endif; ?>
</footer>
	
		<div data-smooth-scroll data-animation-time="250" class=" bd-smoothscroll-3"><a href="#" class=" bd-backtotop-1 ">
    <span class="bd-icon-67 bd-icon "></span>
</a></div>
<div id="wp-footer">
    <?php wp_footer(); ?>
    <!-- <?php printf(__('%d queries. %s seconds.', 'default'), get_num_queries(), timer_stop(0, 3)); ?> -->
</div>
</body>
<?php echo apply_filters('theme_body', ob_get_clean()); // body end ?>
</html>