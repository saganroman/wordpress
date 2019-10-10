<?php
function theme_fix_success_message() {
    if (!theme_woocommerce_enabled())
        return;
?>
    <script>
        jQuery(function ($) {
            $('.woocommerce_message, .woocommerce-message').each(function() {
                $(this).removeClass('woocommerce_message').removeClass('woocommerce-message').addClass('data-control-id-3398 bd-successmessage-1 alert alert-success').append('<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>'); // 
            });
        });
    </script>
<?php
}

add_action('wp_head', 'theme_fix_success_message');

function theme_success_message($msg){
?>
    <div class="data-control-id-3398 bd-successmessage-1 alert alert-success">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span><?php echo $msg; ?></span>
    </div>
<?php
}