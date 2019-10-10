<?php
function theme_fix_error_message() {
    if (!theme_woocommerce_enabled())
        return;
?>
    <script>
        jQuery(function ($) {
            $('.woocommerce_error, .woocommerce-error').each(function() {
                $(this).removeClass('woocommerce_error').removeClass('woocommerce-error').addClass('data-control-id-3397 bd-errormessage-1 alert alert-danger').append('<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>'); // 
            });
        });
    </script>
<?php
}

add_action('wp_head', 'theme_fix_error_message');

function theme_error_message($msg){
?>
    <div class="data-control-id-3397 bd-errormessage-1 alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span><?php echo $msg; ?></span>
    </div>
<?php
}