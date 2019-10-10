<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_generate_posts_data($post) {

    $thumb_id = get_post_thumbnail_id($post->ID);
    $image = '';
    if ($thumb_id) {
        $image = wp_get_attachment_image_src($thumb_id, 'full');
        if ($image) {
            list($image,) = $image;
        } else {
            $image = '';
        }
    }

    return array(
        "caption" => $post->post_title,
        "content" => $post->post_content,
        "excerpt" => $post->post_excerpt,
        "name" => $post->post_name,
        "image" => $image,
        "date" => $post->post_date,
        "parent" => $post->post_parent,
        "order" => $post->menu_order,
    );
}

function get_cms_content() {
    $request_data = $_REQUEST['data'];

    $return = array();


    foreach($request_data as $blog_type => $options) {

        $limit = theme_array_get($options, 'limit', 1);
        $post_type = theme_array_get($options, 'postType', 'any');
        $ids = theme_array_get($options, 'ids');
        $search_string = theme_array_get($options, 'searchString');

        $query_options = array();
        $query_options['posts_per_page'] = $limit;
        $query_options['post_status'] = 'publish';
        $query_options['post_type'] = $post_type;

        if ($search_string) {
            $query_options['s'] = $search_string;
        }
        if (is_array($ids)) {
            $query_options['post__in'] = $ids;
        }

        $query = new WP_Query;
        $posts = $query->query($query_options);

        $result = array();

        foreach ($posts as $post) {
            $result[$post->ID . ''] = theme_generate_posts_data($post);
        }

        $return[$blog_type] = array('contentJson' => $result);
    }
    return array('result' => 'done', 'data' => $return);
}
theme_add_export_action('get_cms_content');


function put_cms_content() {

    $request_data = $_REQUEST['data'];

    $uploaded_images = theme_data_json_get(theme_get_uploaded_images_json_path());

    foreach($request_data as $post_type => $options) {
        $data = $options['contentJson'];

        $put_method = strtolower(theme_array_get($options, 'putMethod', 'insert'));
        $template_name = theme_array_get($options, 'templateName');

        $post_time = time() - count($data);

        $id_map = array();
        foreach($data as $id => $post_data) {

            $post_date = gmdate('Y-m-d H:i:s', ($post_time++) + get_option('gmt_offset') * 3600);
            $type = theme_array_get($post_data, 'type', 'post');

            $insert_data = array(
                'post_type' => $type,
                'post_title' => theme_array_get($post_data, 'caption', ''),
                'post_content' => theme_array_get($post_data, 'content', ''),
                'post_excerpt' => theme_array_get($post_data, 'excerpt', ''),
                'post_name' => theme_array_get($post_data, 'name', ''),
                'post_parent' => 0,
                'menu_order' => intval(theme_array_get($post_data, 'order', '')),
                'comment_status' => $type === 'post' ? 'open' : 'closed',
                'post_date' => $post_date,
                'post_date_gmt' => get_gmt_from_date($post_date),
                'post_status' => 'publish'
            );

            $new_id = wp_insert_post($insert_data);
            $id_map[$id] = $new_id;
        }


        foreach($data as $id => $post_data) {
            $new_id = $id_map[$id];
            $new_parent_id = intval(theme_array_get($id_map, $post_data['parent']));

            if ($new_parent_id) {
                wp_update_post(array(
                    'ID' => $new_id,
                    'post_parent' => $new_parent_id
                ));
            }

            // add featured image to post
            if (isset($post_data['image'])) {
                $image_url = preg_replace('#url\((.*)\)#', '$1', $post_data['image']);
                $attach_id = theme_array_get($uploaded_images, $image_url);
                if ($attach_id) {
                    update_post_meta($new_id, '_thumbnail_id', $attach_id);
                }
            }
        }

        if ($template_name) {
            if ($put_method === 'replace') {
                $ids_to_remove = get_option('theme_template_' . $template_name . '_query_ids');
                if (!is_string($ids_to_remove)) {
                    $ids_to_remove = '';
                }
                $ids_to_remove = array_map('trim', explode(',', $ids_to_remove));

                // move to trash
                foreach($ids_to_remove as $id) {
                    if (is_numeric($id)) {
                        wp_delete_post($id);
                    }
                }
            }

            $ids = implode(',', $id_map);
            update_option('theme_template_' . $template_name . '_query_ids', $ids);
        }
    }

    FilesHelper::remove_file(theme_get_uploaded_images_json_path());
    return array('result' => 'done');
}
theme_add_export_action('put_cms_content');