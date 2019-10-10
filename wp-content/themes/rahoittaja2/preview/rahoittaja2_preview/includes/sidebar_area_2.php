<?php
    function theme_sidebar_area_2() {
        $theme_hide_sidebar_area = true;
        ob_start();
?>
        <h1 class="data-control-id-1502834 bd-textblock-9 bd-content-element">
    <?php
echo <<<'CUSTOM_CODE'
ARKISTO
CUSTOM_CODE;
?>
</h1>
        <?php $area_content = trim(ob_get_clean()); ?>

        <?php if (theme_is_preview()): ?>
            <?php $hide = 
                !strlen(trim(preg_replace('/<!-- empty::begin -->[\s\S]*?<!-- empty::end -->/', '', $area_content))); /* no other controls */ ?>

            <aside class="bd-sidebararea-2-column data-control-id-1502761 bd-flex-vertical bd-flex-fixed <?php if ($hide) echo ' hidden bd-hidden-sidebar'; ?>">
                <div class="bd-sidebararea-2 bd-flex-wide  bd-margins">
                    
                    <?php echo $area_content ?>
                    
                </div>
            </aside>
        <?php else: ?>
            <?php if ($area_content): ?>
                <aside class="bd-sidebararea-2-column data-control-id-1502761 bd-flex-vertical bd-flex-fixed">
                    <div class="bd-sidebararea-2 bd-flex-wide  bd-margins">
                        
                        <?php echo $area_content ?>
                        
                    </div>
                </aside>
            <?php endif; ?>
        <?php endif; ?>
<?php
    }
?>