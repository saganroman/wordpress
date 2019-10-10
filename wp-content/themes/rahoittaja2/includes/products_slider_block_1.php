<?php
function theme_products_slider_block_1($title = '', $content = '', $class = '', $id = '') {
?>
    <div class=" bd-block bd-own-margins <?php echo $class; ?>" data-block-id="<?php echo $id; ?>">
        <?php if (!theme_is_empty_html($title)){ ?>
            <div class=" bd-blockheader bd-tagstyles">
                <h4><?php echo $title; ?></h4>
            </div>
        <?php } ?>
        <div class=" bd-blockcontent bd-tagstyles shape-only">
            <?php echo $content; ?>
        </div>
    </div>
<?php
}