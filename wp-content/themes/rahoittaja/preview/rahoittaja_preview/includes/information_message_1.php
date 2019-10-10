<?php
function theme_fix_info_message() {
    if (!theme_woocommerce_enabled())
        return;
?>
    <script>
        jQuery(function ($) {
            $('.woocommerce_info, .woocommerce-info').each(function() {
                $(this).removeClass('woocommerce_info').removeClass('woocommerce-info').addClass('data-control-id-3396 bd-informationmessage-1 alert alert-info').append('<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>'); // 
            });
        });
    </script>
<?php
}

add_action('wp_head', 'theme_fix_info_message');

function theme_information_message($msg){
?>
    <div class="data-control-id-3396 bd-informationmessage-1 alert alert-info">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span><?php echo $msg; ?></span>
    </div>
<?php
}