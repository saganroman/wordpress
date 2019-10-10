<?php
function theme_warning_message($msg){
?>
    <div class="data-control-id-3395 bd-warningmessage-1 alert alert-warning">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span><?php echo $msg; ?></span>
    </div>
<?php
}