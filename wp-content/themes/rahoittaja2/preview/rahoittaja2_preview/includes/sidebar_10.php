<?php
function theme_block_default_5_10($title = '', $content = '', $class = '', $id = ''){
?>
    <div class="data-control-id-31682 bd-block-5 bd-no-margins bd-own-margins <?php echo $class; ?>" id="<?php echo $id; ?>" data-block-id="<?php echo $id; ?>">
    <?php if (!theme_is_empty_html($title)){ ?>
    
    <div class="data-control-id-31683 bd-blockheader bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table bd-no-margins">
        <h4><?php echo $title; ?></h4>
    </div>
    
<?php } ?>
    <div class="data-control-id-31684 bd-blockcontent bd-tagstyles bd-custom-blockquotes bd-custom-button bd-custom-image bd-custom-table <?php if (theme_is_search_widget($id)) echo ' shape-only'; ?>">
<?php echo $content; ?>
</div>
</div>
<?php
}
?>