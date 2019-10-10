<?php
/**
 * The template for displaying the header
 */
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

<body <?php body_class('hfeed bootstrap'); ?>>
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