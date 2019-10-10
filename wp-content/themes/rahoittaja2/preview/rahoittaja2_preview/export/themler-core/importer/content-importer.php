<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once(dirname(__FILE__) . '/widgets-importer.php');

class ThemlerContentImporter {

    /**
     * @var string Data folder path
     */
    private $_path;

    /**
     * @var string Path to images in data folder
     */
    private $_imagesPath;

    /**
     * @var array Content JSON
     */
    private $_data;

    /**
     * @var array List of attachment-id's that was imported
     */
    private $_importedImages = array();

    /**
     * @var array (post id in content.json) => (real post_id in WP) mapping
     */
    private $_newPostIds = array();

    /**
     * @var array (menu id in content.json) => (real menu_id in WP) mapping
     */
    private $_newMenuIds = array();

    private $_newTermIds = array();
    private $_addedTerms = array();
    private $_addedWidgets = array();

    /**
     * @var array (vmenu widget_id) => (menu id)
     */
    public $vmenus = array();

    /**
     * @var ThemlerWidgetsImporter
     */
    private $_widgetsImporter;

    private $_supportedTaxonomies = array(
        'category' => array('data_key' => 'Categories', 'placeholder_key' => 'category'),
        'post_tag' => array('data_key' => 'Tags', 'placeholder_key' => 'tag'),
    );

    public function __construct($path) {
        $this->_path = $path;
        $this->_imagesPath = $path . '/images';
        $json_path = $path . '/content.json';
        if (!file_exists($json_path)) {
            throw new Exception("Can't find content.json in zip archive");
        }

        $data = file_get_contents($json_path);
        $data = json_decode($data, true);
        if (!is_array($data)) {
            throw new Exception("Invalid json");
        }

        $this->_data = $data;

        $this->_widgetsImporter = new ThemlerWidgetsImporter($this);
    }

    private function _importPostType($post_type, &$data) {
        $this->_newPostIds[$post_type] = array();
        $id_map = &$this->_newPostIds[$post_type];
        foreach($data as $id => $post_data) {
            $new_id = wp_insert_post(array(
                'post_type' => $post_type,
                'post_title' => _at($post_data, 'caption', ''),
                'post_name' => _at($post_data, 'name', ''),
                'comment_status' => _at($post_data, 'commentStatus', 'closed'),
                'post_status' => 'publish'
            ));
            $id_map[$id] = $new_id;
        }
    }

    private function _updatePostType($post_type, &$data) {

        $post_time = time() - count($data);
        $menu_order = 0;

        foreach($data as $id => $post_data) {
            $post_id = $this->_newPostIds[$post_type][$id];
            $post_date = gmdate('Y-m-d H:i:s', ($post_time++) + get_option('gmt_offset') * 3600);

            $update_data = array(
                'ID' => $post_id,
                'post_content' => str_replace('<!--CUT-->', '<!--more-->', $this->_processContent(_at($post_data, 'content', ''))),
                'post_excerpt' => $this->_processContent(_at($post_data, 'excerpt', '')),
                'post_date' => $post_date,
                'post_date_gmt' => get_gmt_from_date($post_date),
                'menu_order' => ++$menu_order,
            );

            $parent_id = intval(_at($this->_newPostIds[$post_type], $post_data['parent']));
            if ($parent_id) {
                $update_data['post_parent'] = $parent_id;
            }

            // set taxonomies (categories, tags, etc)
            foreach($this->_supportedTaxonomies as $tax => $key) {
                $terms = explode(',', _at($post_data, strtolower($key['data_key']), ''));
                $new_term_ids = array();
                foreach($terms as $term) {
                    list(,$term_id) = $this->parsePlaceholder($term);
                    if (isset($this->_newTermIds[$term_id])) {
                        $new_term_ids[] = $this->_newTermIds[$term_id];
                    }
                }
                if ($new_term_ids) {
                    wp_set_post_terms($post_id, $new_term_ids, $tax);
                }
            }

            // add featured image to post
            if (isset($post_data['image'])) {
                list($tag_name, $image_id) = $this->parsePlaceholder($post_data['image']);
                if ($tag_name === 'image') {
                    $attach_id = $this->_data['Images'][$image_id]['_attachId'];

                    if ($attach_id) {
                        update_post_meta($post_id, '_thumbnail_id', $attach_id);
                    }
                }
            }

            $show_in_menu = _at($post_data, 'showInMenu', true);
            update_post_meta($post_id, '_theme_show_in_menu', $show_in_menu ? '1' : '0');

            $show_page_title = _at($post_data, 'showPageTitle', true);
            update_post_meta($post_id, '_theme_show_page_title', $show_page_title ? '1' : '0');

            $title_in_menu = _at($post_data, 'titleInMenu');
            if ($title_in_menu) {
                update_post_meta($post_id, '_theme_title_in_menu', $title_in_menu);
            }

            $autop = _at($post_data, 'autop', true);
            update_post_meta($post_id, '_theme_use_wpautop', $autop ? '1' : '0');


            $page_head = _at($post_data, 'pageHead');
            if ($page_head) {
                $page_head = $this->_processContent($page_head); // replace images
                add_post_meta($post_id, 'theme_head', $page_head);
            }

            $page_title = _at($post_data, 'titleInBrowser');
            if ($page_title) {
                add_post_meta($post_id, 'page_title', $page_title);
            }

            $keywords = _at($post_data, 'keywords');
            if ($keywords) {
                add_post_meta($post_id, 'page_keywords', $keywords);
            }

            $description = _at($post_data, 'description');
            if ($description) {
                add_post_meta($post_id, 'page_description', $description);
            }

            $metaTags = _at($post_data, 'metaTags');
            if ($metaTags) {
                add_post_meta($post_id, 'page_metaTags', $metaTags);
            }

            $customHeadHtml = _at($post_data, 'customHeadHtml');
            if (false !== $customHeadHtml) {
                add_post_meta($post_id, 'page_customHeadHtml', $customHeadHtml);
                add_post_meta($post_id, 'page_hasCustomHeadHtml', 'true');
            }

            // set front and blog pages
            $is_front = _at($post_data, 'isFront');
            $is_blog = _at($post_data, 'isBlog');
            if ($is_front) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $post_id);
            }
            if ($is_blog) {
                update_option('page_for_posts', $post_id);
            }

            wp_update_post($update_data);
        }
    }

    private function _importPosts() {
        if (isset($this->_data['Sections'])) {
            $this->_importPostType('upage_section', $this->_data['Sections']);
            foreach($this->_newPostIds['upage_section'] as $old_id => $new_id) {
                $this->_replaceFrom[] = "[section_$old_id]";
                $this->_replaceTo[] = "[upage_section id=$new_id]";
            }
        }
        if (isset($this->_data['Posts'])) {
            $this->_importPostType('post', $this->_data['Posts']);
            foreach($this->_newPostIds['post'] as $old_id => $new_id) {
                $this->_replaceFrom[] = "[post_$old_id]";
                $this->_replaceTo[] = get_permalink($new_id);
            }
        }
        if (isset($this->_data['Pages'])) {
            $this->_importPostType('page', $this->_data['Pages']);
            foreach($this->_newPostIds['page'] as $old_id => $new_id) {
                $this->_replaceFrom[] = "[page_$old_id]";
                $this->_replaceTo[] = get_permalink($new_id);
            }
        }


        if (isset($this->_data['Sections'])) {
            $this->_updatePostType('upage_section', $this->_data['Sections']);
        }
        if (isset($this->_data['Posts'])) {
            $this->_updatePostType('post', $this->_data['Posts']);
        }
        if (isset($this->_data['Pages'])) {
            $this->_updatePostType('page', $this->_data['Pages']);
        }
    }

    public function _processContent($content) {
        return str_replace($this->_replaceFrom, $this->_replaceTo, $content);
    }

    private $_replaceFrom = array();
    private $_replaceTo = array();

    private function _importImages() {
        if (!isset($this->_data['Images'])) {
            return;
        }
        $base_upload_dir = wp_upload_dir();
        $images_dir = $base_upload_dir['path'];

        foreach($this->_data['Images'] as $id => &$image) {
            $filename = $image['fileName'];
            $filename = wp_unique_filename($images_dir, $filename);
            $image_path = $images_dir . '/' . $filename;

            ThemlerFilesUtility::copyRecursive($this->_imagesPath . '/' . $image['fileName'], $image_path);

            $wp_filetype = wp_check_filetype($filename, null);
            $attachment = array(
                'guid' => $base_upload_dir['url'] . '/' . $filename,
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
            );
            $attach_id = wp_insert_attachment($attachment, $image_path);
            $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
            wp_update_attachment_metadata($attach_id, $attach_data);

            $image_url = wp_get_attachment_url($attach_id);
            $image['_url'] = $image_url;
            $image['_attachId'] = $attach_id;

            $this->_replaceFrom[] = '[image_' . $id . ']';
            $this->_replaceTo[] = $image_url;
            $this->_importedImages[] = $attach_id;
        }
    }

    private function _getObjectInfoByPlaceholder($placeholder) {
        list($name, $id) = $this->parsePlaceholder($placeholder);
        if (!$name) {
            // invalid link
            return false;
        }
        if (isset($this->_newPostIds[$name][$id])) {
            return array(
                'type' => 'post_type',
                'object' => $name,
                'object_id' => $this->_newPostIds[$name][$id],
            );
        }

        if (isset($this->_newTermIds[$id])) {
            foreach($this->_supportedTaxonomies as $tax => $key) {
                if ($key['placeholder_key'] === $name) {
                    return array(
                        'type' => 'taxonomy',
                        'object' => $tax,
                        'object_id' => $this->_newTermIds[$id],
                    );
                }
            }
        }
        return false;
    }

    public function parsePlaceholder($placeholder) {
        $name = false;
        $id = false;
        if (preg_match('#\[(.*)_(\d+)\]#', $placeholder, $matches)) {
            $name = $matches[1];
            $id = $matches[2];
        }
        return array($name, $id);
    }

    private function _importMenus() {
        if (!isset($this->_data['Menus'])) {
            return;
        }

        foreach($this->_data['Menus'] as $menu_id => $menu) {

            $menu_name = _at($menu, 'caption', 'Menu');
            // generate unique name
            for ($i = 0; ; $i++) {
                $new_name = $menu_name . ($i ? ' #' . $i : '');
                $_possible_existing = get_term_by('name', $new_name, 'nav_menu');
                if (!$_possible_existing || is_wp_error($_possible_existing) || !isset($_possible_existing->term_id)) {
                    $menu_name = $new_name;
                    break;
                }
            }

            $menu_new_id = wp_update_nav_menu_object(0, array('menu-name' => $menu_name));
            $this->_newMenuIds[$menu_id] = $menu_new_id;

            if (isset($menu['items']) && is_array($menu['items'])) {
                $id_map = array();
                foreach($menu['items'] as $menu_item_id => $menu_item) {
                    $id_map[$menu_item_id] = wp_update_nav_menu_item($menu_new_id, 0, array());
                }

                $order = 0;
                foreach($menu['items'] as $menu_item_id => $menu_item) {
                    $menu_item_data = array();
                    $menu_item_caption = _at($menu_item, 'caption');
                    if ($menu_item_caption) {
                        $menu_item_data['menu-item-title'] = $menu_item_caption;
                    }
                    $menu_item_parent = _at($menu_item, 'parent');
                    if ($menu_item_parent) {
                        $menu_item_data['menu-item-parent-id'] = $id_map[$menu_item_parent];
                    }
                    $menu_item_href = _at($menu_item, 'href', '#');
                    $menu_item_data['menu-item-position'] = ++$order;
                    if ($menu_item_href) {
                        $href = $this->_getObjectInfoByPlaceholder($menu_item_href);
                        if ($href) {
                            $menu_item_data['menu-item-type'] = $href['type'];
                            $menu_item_data['menu-item-object'] = $href['object'];
                            $menu_item_data['menu-item-object-id'] = $href['object_id'];
                        } else {
                            $menu_item_data['menu-item-type'] = 'custom';
                            $menu_item_data['menu-item-url'] = $menu_item_href;
                        }
                    }
                    wp_update_nav_menu_item($menu_new_id, $id_map[$menu_item_id], $menu_item_data);
                }
            }

            $positions = _at($menu, 'positions');
            if (is_string($positions) && $positions) {
                $positions = explode(',', $positions);
                $nav_menu_locations = get_nav_menu_locations();
                foreach($positions as $position) {
                    $position = trim($position);
                    if ($position) {
                        $nav_menu_locations[$position] = $menu_new_id;
                    }
                }
                set_theme_mod('nav_menu_locations', $nav_menu_locations);
            }

            $widgets = _at($menu, 'widgets');
            if (is_string($widgets) && $widgets) {
                $widgets = explode(',', $widgets);
                foreach($widgets as $widget_placeholder) {
                    $widget_placeholder = trim($widget_placeholder);
                    list(, $id) = $this->parsePlaceholder($widget_placeholder);
                    if ($id) {
                        $this->vmenus[$id] = $menu_new_id;
                    }
                }
            }
        }
    }

    private function _importTaxonomies($data_key, $tax) {
        if (!isset($this->_data[$data_key])) {
            return;
        }

        foreach($this->_data[$data_key] as $id => $term) {
            $name = _at($term, 'name', '');
            $caption = _at($term, 'caption', 'Unknown');
            $description = _at($term, 'description', '');

            if (!term_exists($caption, $tax)) {
                $inserted_term = wp_insert_term($caption, $tax, array(
                    'slug' => $name,
                    'description' => $description,
                ));
                if (is_array($inserted_term) && isset($inserted_term['term_id'])) {
                    $this->_addedTerms[] = array(
                        'term_id' => (int)$inserted_term['term_id'],
                        'taxonomy' => $tax
                    );
                }
            }
            $exists_term = get_term_by('name', $caption, $tax);
            $this->_newTermIds[$id] = (int)$exists_term->term_id;
        }
    }

    private function _importSidebars() {
        if (!isset($this->_data['Sidebars'])) {
            return;
        }
        $this->_widgetsImporter->deactivateAllWidgets();
        $this->_addedWidgets = $this->_widgetsImporter->importSidebars($this->_data['Sidebars'], $this->_data['Widgets']);
    }

    private function _importParameters() {
        if (!isset($this->_data['Parameters'])) {
            return;
        }

        $data = &$this->_data['Parameters'];

        if (isset($data['siteTitle'])) {
            update_option('blogname', $data['siteTitle']);
        }
        if (isset($data['siteSlogan'])) {
            update_option('blogdescription', $data['siteSlogan']);
        }
        if (!empty($data['showPostsOnFront'])) {
            update_option('show_on_front', 'posts');
        }
    }

    public function import($remove_previous_content = false) {
        if ($remove_previous_content) {
            $prev_data = get_option('themler_imported_content');
            if (is_array($prev_data)) {
                foreach ($prev_data['posts'] as $post_id) {
                    wp_delete_post($post_id);
                }
                foreach ($prev_data['menus'] as $menu_id) {
                    wp_delete_nav_menu($menu_id);
                }
                foreach ($prev_data['terms'] as $term) {
                    wp_delete_term($term['term_id'], $term['taxonomy']);
                }
                foreach ($prev_data['widgets'] as $widget_id) {
                    $this->_widgetsImporter->deleteWidget($widget_id, true);
                }
            }
        }

        foreach($this->_supportedTaxonomies as $tax => $key) {
            $this->_importTaxonomies($key['data_key'], $tax);
        }
        $this->_importImages();
        $this->_importPosts();
        $this->_importMenus();
        $this->_importSidebars();
        $this->_importParameters();

        $imported_posts = array();
        foreach($this->_newPostIds as $ids) {
            $imported_posts = array_merge(array_values($imported_posts), $ids);
        }
        update_option('themler_imported_content', array(
            'posts' => $imported_posts,
            'menus' => array_values($this->_newMenuIds),
            'images' => $this->_importedImages,
            'terms' => $this->_addedTerms,
            'widgets' => $this->_addedWidgets,
        ));
    }
}