<?php
/**
 * The template for displaying the footer
 */
?>

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

<div id="wp-footer">
    <?php wp_footer(); ?>
    <!-- <?php printf(__('%d queries. %s seconds.', 'default'), get_num_queries(), timer_stop(0, 3)); ?> -->
</div>
</body>