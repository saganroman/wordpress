<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (isset($_GET['themler-import'])) {

    $include_posts = _at($_REQUEST, 'posts');
    $include_pages = _at($_REQUEST, 'pages');
    $include_sections = _at($_REQUEST, 'sections');
    $include_images = _at($_REQUEST, 'images');
    $include_menus = _at($_REQUEST, 'menus');
    $include_categories = _at($_REQUEST, 'categories');
    $include_tags = _at($_REQUEST, 'tags');

    $exporter = new ThemlerContentExporter(array(
        'includeImages' => $include_images,
        'includeSections' => $include_sections,
        'includeTaxonomies' => array(
            'category' => $include_categories,
            'post_tag' => $include_tags,
        ),
    ));

    $result = $exporter->export(array(
        'posts' => $include_posts ? array(
                'limit' => _at($_REQUEST, 'post-limit'),
            ) : false,
        'pages' => $include_pages ? array(
                'limit' => _at($_REQUEST, 'page-limit'),
            ) : false,
        'menus' => $include_menus,
        'taxonomies' => array(
            'category' => $include_categories,
            'post_tag' => $include_tags,
        ),
    ));

    $storage = new ThemlerContentStorage();
    $storage->createFolder($result);
    $archive_path = $storage->createZip('themler-content.zip');
    $archive_name = 'content-' . date("Y-m-d");

    $levels = ob_get_level();
    for ($i = 0; $i < $levels; $i++)
        ob_end_clean();

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename=\"$archive_name.zip\"");
    header("Pragma: no-cache");

    ThemlerFilesUtility::readfile($archive_path);
    $storage->clear();

    die;
}

?>

<div class="wrap">
<h1><?php _e( 'Export' ); ?></h1>

<p><?php _e('When you click the button below Themler will create a ZIP file for you to save to your computer.', 'default'); ?></p>
<p><?php _e('This file will contain your posts, pages and images.', 'default'); ?></p>
<p><?php _e('Once you\'ve saved the download file, you can use Themler Import function in another WordPress installation to import the content from this site.', 'default'); ?></p>
<p><?php _e('<strong>Notice:</strong> Please make sure you have installed and activated Themler plugin before importing content.', 'default'); ?></p>

<h2><?php _e( 'Choose what to export' ); ?></h2>
<form method="get" id="export-filters">
    <input type="hidden" name="page" value="themler_export">
    <input type="hidden" name="themler-import" value="true">
    <fieldset>
        <p><label><input checked class="checkbox" type="checkbox" name="posts" value="posts" /> <?php _e('Posts', 'default'); ?></label></p>
        <ul class="export-filters">
            <li>
                <p>
                    <label>Get recent</label>
                    <input id="post-limit" style="width:90px;" type="number" name="post-limit" value="<?php echo themler_get_option('themler_export_post_limit'); ?>">
                    <label> posts</label>
                </p>
            </li>
        </ul>
    </fieldset>

    <fieldset>
        <p><label><input checked class="checkbox" type="checkbox" name="pages" value="pages" /> <?php _e('Pages', 'default'); ?></label></p>
        <ul class="export-filters">
            <li>
                <p>
                    <label>Get recent</label>
                    <input id="page-limit" style="width:90px;" type="number" name="page-limit" value="<?php echo themler_get_option('themler_export_page_limit'); ?>">
                    <label> pages</label>
                </p>
            </li>
        </ul>
    </fieldset>

    <fieldset>
        <p>
            <label><input checked class="checkbox" type="checkbox" name="menus" value="menus" /> <?php _e('Include menus', 'default'); ?></label>
        </p>
        <input checked class="checkbox" type="hidden" name="sections" value="sections" />
        <input checked class="checkbox" type="hidden" name="categories" value="categories" />
        <input checked class="checkbox" type="hidden" name="tags" value="tags" />
        <input checked class="checkbox" type="hidden" name="images" value="images" />
    </fieldset>

    <?php submit_button(__('Download Export File', 'default')); ?>
</form>
</div>


<script>
    jQuery(document).ready(function() {

        jQuery('#export-filters .checkbox').each(function() {
            var checkbox = jQuery(this);

            checkbox.change(function() {
                var checked = checkbox.is(':checked');
                var list = checkbox.closest('fieldset').find('ul.export-filters');

                if (checked) {
                    list.show();
                } else {
                    list.hide();
                }
            });
        });
    });
</script>