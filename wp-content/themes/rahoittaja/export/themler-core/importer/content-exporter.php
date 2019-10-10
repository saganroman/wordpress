<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ThemlerContentExporter {

    /**
     * @param array $options
     * bool includeImages
     * bool includeSections
     */
    public function __construct($options = array()) {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
        foreach($this->_supportedTaxonomies as $tax => $key) {
            $this->_presentsTaxonomies[$tax] = array();
        }
    }

    public $options = array(
        'includeImages' => true,
        'includeSections' => true,
        'includeTaxonomies' => array(
            'category' => true,
            'post_tag' => true,
        ),
    );

    private $_supportedTaxonomies = array(
        'category' => array('data_key' => 'Categories', 'placeholder_key' => 'category'),
        'post_tag' => array('data_key' => 'Tags', 'placeholder_key' => 'tag'),
    );

    /**
     * @param $options
     * string start_date    (strtotime-compatible string)
     * string end_date      (strtotime-compatible string)
     * int    limit
     * string post_type     (post, page, etc..)
     * string search_string
     * string post_status   (publish, draft, etc..)
     * string orderby       (date, name, etc..)
     * string order         (ASC, DESC)
     * array  ids
     *
     * @return array
     */
    private function _getPosts($options) {

        $limit = _at($options, 'limit', -1);
        $post_type = _at($options, 'post_type', 'any');
        $search_string = _at($options, 'search_string');
        $post_status = _at($options, 'post_status');
        $orderby = _at($options, 'orderby', 'modified');
        $order = _at($options, 'order');
        $ids = _at($options, 'ids');

        $query_options = array();
        $query_options['posts_per_page'] = $limit;
        $query_options['post_type'] = $post_type;

        if ($post_status) {
            $query_options['post_status'] = $post_status;
        }

        if ($search_string) {
            $query_options['s'] = $search_string;
        }

        $date_ranges = array();
        if (!empty($options['start_date'])) {
            $Y_m = date('Y-m', strtotime($options['start_date']));
            $date_ranges['after'] = "$Y_m-01 00:00:00";
        }
        if (!empty($options['end_date'])) {
            $Y_m = date('Y-m', strtotime($options['end_date']));
            $Y_m = date('Y-m', strtotime('+1 month', strtotime($Y_m)));

            $date_ranges['before'] = "$Y_m-01 00:00:00";
        }

        if ($date_ranges) {
            $query_options['date_query'] = array($date_ranges);
        }

        if ($orderby) {
            $query_options['orderby'] = $orderby;
        }
        if ($order) {
            $query_options['order'] = $order;
        }

        if (is_array($ids)) {
            if (!$ids) {
                // nothing to do
                return array();
            }
            $query_options['post__in'] = $ids;
        }

        $query = new WP_Query;
        $posts = $query->query($query_options);

        $result = array();

        foreach ($posts as $post) {
            $id = $post->ID;

            $thumb_id = get_post_thumbnail_id($id);
            $image = '';
            if ($thumb_id) {
                $image = wp_get_attachment_image_src($thumb_id, 'full');
                if ($image) {
                    list($image,) = $image;
                } else {
                    $image = '';
                }
            }

            $result[$id] = array();
            $r = &$result[$id];

            $r = array(
                "caption" => $post->post_title,
                "content" => str_replace('<!--more-->', '<!--CUT-->', $post->post_content),
                "name" => $post->post_name,
            );

            if ($post->post_excerpt)
                $r["excerpt"] = $post->post_excerpt;
            if ($image)
                $r["image"] = $image;
            if ($post->post_parent)
                $r["parent"] = $post->post_parent;

            foreach($this->_supportedTaxonomies as $tax => $key) {
                if (!$this->options['includeTaxonomies'][$tax]) {
                    continue;
                }

                $taxs = wp_get_object_terms($id, $tax, array('fields' => 'ids'));
                $value = '';
                foreach ($taxs as $tax_id) {
                    $placeholder = $this->_processTaxonomy($tax, $tax_id);
                    if ($placeholder)
                        $value .= ($value ? ',' : '') . $placeholder;
                }
                if ($value)
                    $r[strtolower($key['data_key'])] = $value;
            }

            $show_in_menu = get_post_meta($id, '_theme_show_in_menu', true);
            if (false !== $show_in_menu) $r['showInMenu'] = !!$show_in_menu;

            $show_page_title = get_post_meta($id, '_theme_show_page_title', true);
            if (false !== $show_page_title) $r['showPageTitle'] = !!$show_page_title;

            $autop = get_post_meta($id, '_theme_use_wpautop', true);
            if (false !== $autop) $r['autop'] = !!$autop;

            $title_in_menu = get_post_meta($id, '_theme_title_in_menu', true);
            if ($title_in_menu) $r['titleInMenu'] = $title_in_menu;

            $page_head = get_post_meta($id, 'theme_head', true);
            if ($page_head) $r['pageHead'] = $page_head;

            $page_title = get_post_meta($id, 'page_title', true);
            if ($page_title) $r['titleInBrowser'] = $page_title;

            $keywords = get_post_meta($id, 'page_keywords', true);
            if ($keywords) $r['keywords'] = $keywords;

            $description = get_post_meta($id, 'page_description', true);
            if ($description) $r['description'] = $description;

            $metaTags = get_post_meta($id, 'page_metaTags', true);
            if ($metaTags) $r['metaTags'] = $metaTags;

            $customHeadHtml = get_post_meta($id, 'page_customHeadHtml', true);
            if ($customHeadHtml) $r['customHeadHtml'] = $customHeadHtml;

            $hasCustomHeadHtml = get_post_meta($id, 'page_hasCustomHeadHtml', true);
            if ($hasCustomHeadHtml !== false) {
                $r['hasCustomHeadHtml'] = !!$hasCustomHeadHtml;
            }

            // set front and blog pages
            if (get_option('show_on_front') === 'page' && (int)get_option('page_on_front') === (int)$id) {
                $r['isFront'] = true;
            }
            if ((int)get_option('page_for_posts') === (int)$id) {
                $r['isBlog'] = true;
            }

            foreach(array('image', 'content', 'excerpt', 'pageHead') as $key) {
                if (isset($r[$key])) {
                    $this->_processContent($r[$key]);
                }
            }

            switch ($post->post_type) {
                case 'post':
                    $this->_placeholders["post_type_$id"] = "[post_$id]";
                    break;
                case 'page':
                    $this->_placeholders["post_type_$id"] = "[page_$id]";
                    break;
                case 'upage_section':
                    $this->_placeholders["post_type_$id"] = "[section_$id]";
                    break;
            }
        }
        return $result;
    }

    private $_placeholders = array();

    private $_paths = array();
    private $_presentsImages = array();
    private $_presentsImageNames = array();

    private function _getAbsoluteImagesData(&$content) {
        preg_match_all('#(https?:)?\/\/(\S+?)\.(png|jpg|gif|svg|ico|jpeg|bmp)#', $content, $matches);
        $result = array();
        $len = count($matches[0]);
        for ($i = 0; $i < $len; $i++) {
            $url = $matches[0][$i];

            $upload_info = wp_upload_dir();
            if ($upload_info['error']) {
                throw new Exception($upload_info['error']);
            }
            $upload_dir = $upload_info['basedir'];
            $upload_url = $upload_info['baseurl'];
            if (substr($url, 0, strlen($upload_url)) !== $upload_url) {
                continue;
            }
            $relative_path = substr($url, strlen($upload_url));
            $abs_path = ThemlerFilesUtility::normalizePath($upload_dir . $relative_path);
            if (!is_file($abs_path)) {
                continue;
            }

            $result[] = array(
                'context' => $url,
                'url' => $url,
                'path' => $abs_path,
            );
        }
        return $result;
    }

    private function _getRelativeImagesData(&$content) {
        preg_match_all('#["\'\(]\/((\S+?)\.(png|jpg|gif|svg|ico|jpeg|bmp))["\'\)]#', $content, $matches);
        $result = array();
        $len = count($matches[0]);
        for ($i = 0; $i < $len; $i++) {
            $path = ThemlerFilesUtility::normalizePath($_SERVER['DOCUMENT_ROOT'] . $matches[1][$i]);

            if (!is_file($path)) {
                continue;
            }

            $result[] = array(
                'context' => $matches[0][$i],
                'url' => '/' . $matches[1][$i],
                'path' => $path,
            );
        }
        return $result;
    }

    private function _getImagesData(&$content) {
        $absolute = $this->_getAbsoluteImagesData($content);
        $relative = $this->_getRelativeImagesData($content);
        $result = array_merge($absolute, $relative);
        return $result;
    }

    private function _createImageName($base_name) {
        if (!isset($this->_presentsImageNames[$base_name])) {
            return $base_name;
        }
        for ($i = 1; ; $i++) {
            $dot_pos = strrpos($base_name, '.');
            $name = $dot_pos === false
                ? $base_name . $i
                : substr_replace($base_name, "$i.", $dot_pos, 1);

            if (!isset($this->_presentsImageNames[$name])) {
                return $name;
            }
        }
        return '';
    }

    private function _processImages(&$content) {
        if (!$this->options['includeImages']) {
            return;
        }

        $replace_from = array();
        $replace_to = array();
        $images = $this->_getImagesData($content);
        foreach ($images as $image) {
            $path = $image['path'];

            if (!isset($this->_paths[$path])) {

                $id = count($this->_presentsImages) + 1;
                $file_name = $this->_createImageName(basename($path));
                $this->_presentsImages[$id] = array(
                    'fileName' => $file_name,
                    '_path' => $path,
                );
                $this->_presentsImageNames[$file_name] = true;
                $this->_paths[$path] = $id;
            }

            $replace_from[] = $image['context'];
            $replace_to[] = str_replace($image['url'], '[image_' . $this->_paths[$path] . ']', $image['context']);
        }
        $content = str_replace($replace_from, $replace_to, $content);
    }

    private $_presentsTaxonomies = array();

    private function _processTaxonomy($tax, $tax_id) {
        if (empty($this->options['includeTaxonomies'][$tax])) {
            return '';
        }
        $this->_placeholders["taxonomy_$tax_id"] = '[' . $this->_supportedTaxonomies[$tax]['placeholder_key'] . '_' . $tax_id . ']';
        $this->_presentsTaxonomies[$tax][$tax_id] = true;
        return $this->_placeholders["taxonomy_$tax_id"];
    }

    private function _getSectionsData(&$content) {
        preg_match_all('#\[upage_section[^\d\]]*(\d+)[^\]]*]#', $content, $matches);
        $result = array();
        $len = count($matches[0]);
        for ($i = 0; $i < $len; $i++) {
            $result[] = array(
                'text' => $matches[0][$i],
                'id' => $matches[1][$i],
            );
        }
        return $result;
    }

    private $_presentsSections = array();

    private function _processSections(&$content) {
        if (!$this->options['includeSections']) {
            return;
        }

        $replace_from = array();
        $replace_to = array();
        $sections = $this->_getSectionsData($content);
        foreach ($sections as $section) {
            $this->_presentsSections[$section['id']] = true;

            $replace_from[] = $section['text'];
            $replace_to[] = '[section_' . $section['id'] . ']';
        }
        $content = str_replace($replace_from, $replace_to, $content);
    }

    private function _processContent(&$content) {
        $this->_processImages($content);
        $this->_processSections($content);
    }

    public function getPosts($options) {
        $options['post_type'] = 'post';
        return $this->_getPosts($options);
    }

    public function getPages($options) {
        $options['post_type'] = 'page';
        return $this->_getPosts($options);
    }

    public function getSections($options) {
        $options['post_type'] = 'upage_section';
        $options['ids'] = array_keys($this->_presentsSections);
        return $this->_getPosts($options);
    }

    public function getImages() {
        return $this->_presentsImages;
    }

    private function _generateMenuItemHref($item) {
        $default = '#';
        if ($item->type === 'custom') {
            return $item->url;
        }

        $key = $item->type . '_' . $item->object_id;
        return _at($this->_placeholders, $key, $default);
    }

    public function getMenus() {
        $result = array();
        $nav_menus = wp_get_nav_menus();
        if (empty($nav_menus) || !is_array($nav_menus)) {
            return $result;
        }

        foreach ($nav_menus as $menu) {
            $locations = get_nav_menu_locations();
            $positions = array();
            foreach ($locations as $location => $id) {
                if ($id == $menu->term_id) {
                    $positions[] = $location;
                }
            }
            $result[$menu->term_id] = array(
                'items' => array(),
                'name' => $menu->slug,
                'caption' => $menu->name,
                'positions' => implode(',', $positions),
            );
            $items = wp_get_nav_menu_items($menu->slug);
            foreach ($items as $item) {
                $r = array();

                $r['caption'] = $item->title;
                $r['href'] = $this->_generateMenuItemHref($item);
                if ($item->menu_item_parent) {
                    $r['parent'] = $item->menu_item_parent;
                }

                $result[$menu->term_id]['items'][$item->ID] = $r;
            }
        }
        return $result;
    }

    public function getTaxonomy($taxonomy) {
        $result = array();
        foreach (array_keys($this->_presentsTaxonomies[$taxonomy]) as $term_id) {
            $term = get_term($term_id, $taxonomy);
            $result[$term_id] = array(
                'caption' => $term->name,
                'name' => $term->slug,
            );
            if ($term->description)
                $result[$term_id]['description'] = $term->description;
        }
        return $result;
    }

    public function export($options = array()) {
        $options = wp_parse_args($options, array(
            'posts' => true,
            'pages' => true,
            'sections' => true,
            'images' => true,
            'menus' => true,
            'taxonomies' => array(
                'category' => true,
                'post_tag' => true,
            ),
        ));
        foreach(array('posts', 'pages', 'sections') as $post_type) {
            if (true === $options[$post_type]) {
                $options[$post_type] = array();
            }
        }

        $result = array();
        if (false !== $options['posts']) {
            $result['Posts'] = $this->getPosts($options['posts']);
        }
        if (false !== $options['pages']) {
            $result['Pages'] = $this->getPages($options['pages']);
        }
        if ($this->options['includeSections'] && false !== $options['sections']) {
            $result['Sections'] = $this->getSections($options['sections']);
        }
        if ($this->options['includeImages'] && false !== $options['images']) {
            $result['Images'] = $this->getImages();
        }
        if (false !== $options['menus']) {
            $result['Menus'] = $this->getMenus();
        }
        foreach($this->_supportedTaxonomies as $tax => $key) {
            if (!empty($this->options['includeTaxonomies'][$tax]) && !empty($options['taxonomies'][$tax])) {
                $result[$key['data_key']] = $this->getTaxonomy($tax);
            }
        }

        $result['Parameters'] = $this->getParameters();

        return $result;
    }

    public function getParameters() {
        $result = array();
        $result['siteTitle'] = get_option('blogname');
        $result['siteSlogan'] = get_option('blogdescription');
        $result['siteSlogan'] = get_option('blogdescription');
        $result['showPostsOnFront'] = get_option('show_on_front') === 'posts';
        return $result;
    }
}


class ThemlerContentStorage {

    private $_tmpDir;
    private $_clearFolders = array();

    public function __construct($dir = '') {
        if (!$dir) {
            $upload_info = wp_upload_dir();
            if ($upload_info['error']) {
                wp_die($upload_info['error']);
            }
            $upload_root = $upload_info['basedir'];
            $dir = $upload_root . '/themler-export';
        }
        $this->_tmpDir = $dir;
        $this->_clearFolders[] = $dir;
    }

    public function getDataDirectory() {
        return $this->_tmpDir;
    }

    public function createFolder($json) {
        ThemlerFilesUtility::emptyDir($this->_tmpDir);
        ThemlerFilesUtility::createDir($this->_tmpDir);

        if (isset($json['Images'])) {
            $images = $json['Images'];
            $json['Images'] = array();
            foreach($images as $id => $image) {
                $json['Images'][$id] = $image;
                unset($json['Images'][$id]['_path']);
            }

            ThemlerFilesUtility::createDir($this->_tmpDir . '/images');
            foreach($images as $image) {
                $path = $image['_path'];
                ThemlerFilesUtility::copyRecursive($path, $this->_tmpDir . '/images/' . $image['fileName']);
            }
        }
        ThemlerFilesUtility::write($this->_tmpDir . '/content.json', json_encode($json, JSON_PRETTY_PRINT));
    }

    public function createZip($archive_name) {
        $archive_path = dirname($this->_tmpDir) . '/' . $archive_name;
        ThemlerFilesUtility::createZip($this->_tmpDir, $archive_path);
        $this->_clearFolders[] = $archive_path;
        return $archive_path;
    }

    public function clear() {
        foreach($this->_clearFolders as $path) {
            ThemlerFilesUtility::emptyDir($path, true);
        }
    }
}