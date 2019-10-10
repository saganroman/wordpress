<?php
function theme_logo_2(){
?>
<?php
    $logoAlt = get_option('blogname');
    $logoSrc = theme_get_option('theme_logo_url');
    $logoLink = theme_get_option('theme_logo_link');
?>

<a class=" bd-logo-2" href="<?php
    if (!theme_is_empty_html($logoLink)) {
        echo $logoLink;
    } else {
        ?><?php
    }
?>">
<img class=" bd-imagestyles"<?php
                if (!theme_is_empty_html($logoSrc)) {
                    echo ' src="' . $logoSrc . '"';
                } else {
                    ?>
 src="<?php echo theme_get_image_path('images/bd26c56c90712cde23212a86f33e8456_logo.png'); ?>"<?php
                } ?> alt="<?php echo $logoAlt ?>">
</a>
<?php
}