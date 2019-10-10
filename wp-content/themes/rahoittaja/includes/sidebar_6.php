<?php
function theme_block_footer_6_6($title = '', $content = '', $class = '', $id = ''){
?>
    <div class=" bd-block-6 bd-own-margins <?php echo $class; ?>" id="<?php echo $id; ?>" data-block-id="<?php echo $id; ?>">
    <?php if (!theme_is_empty_html($title)){ ?>
    
    <div class=" bd-blockheader bd-tagstyles">
        <h4><?php echo $title; ?></h4>
    </div>
    
<?php } ?>
    <div class=" bd-blockcontent bd-tagstyles <?php if (theme_is_search_widget($id)) echo ' shape-only'; ?>">
<?php echo $content; ?>
</div>
</div>
<?php
}
?>