<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function theme_strlen($str) {
	if (function_exists('mb_strlen')) {
		return mb_strlen($str);
	}
	return strlen($str);
}

function theme_strpos($source, $target) {
	if (function_exists('mb_strpos')) {
		return mb_strpos($source, $target);
	}
	return strpos($source, $target);
}

function theme_array_get(&$arr, $key, $def = false) {
	if (@isset($arr[$key])) {
		return $arr[$key];
	}
	return $def;
}

function theme_get_array_value($arr = array(), $key = null, $def = false) {
	if (is_array($arr) && @isset($arr[$key])) {
		return $arr[$key];
	}
	return $def;
}

function theme_is_empty_html($str) {
	return !is_string($str) || theme_strlen(str_replace(array('&nbsp;', ' ', "\n", "\r", "\t"), '', $str)) == 0;
}

function theme_is_vmenu_widget($id) {
	return strpos($id, 'vmenu') !== false;
}

function theme_is_search_widget($id) {
    return strpos($id, 'search') === 0;
}

function theme_is_products_slider_widget($id) {
    $names = array('random_products', 'best_sellers', 'featured-products', 'onsale', 'recent_products',
        'recently_viewed_products', 'top-rated-products',
		'related_products', 'upsells_products', 'crosssells_products');
    foreach ($names as $name) {
        if (strpos($id, $name) !== false) {
            return true;
        }
    }
    return false;
}

function theme_trim_long_str($str, $len = 50) {
    $excerpt = theme_create_excerpt($str, $len, 0, true);
    if ($excerpt) {
        return force_balance_tags($excerpt . '&hellip;');
    }
    return $str;
}

function theme_prepare_attr($attr = array()) {
	$attr = wp_parse_args($attr);
	if (count($attr) == 0)
		return '';
	$result = '';
	foreach ($attr as $name => $value) {
		if (empty($name) || empty($value))
			continue;
		$result .= ' ' . strtolower($name) . '="' . esc_attr($value) . '"';
	}
	return $result;
}

function theme_html_has_class($html, $class) {
    return preg_match('#(class="?[^"]*["\s])' . $class . '("|\s)#', $html);
}

function theme_html_replace_class($html, $old_class, $new_class, $additional_attributes = '') {
    return preg_replace('#(class="?[^"]*["\s])' . $old_class . '("|\s)#', "$additional_attributes $1$new_class$2", $html);
}

class ThemeUrlsHelper {

    /**
     * @return string
     */
    public static function getServerUrl() {
        $server_URL = 'http';
        if (is_ssl()) {
            $server_URL .= 's';
        }
        $server_URL .= '://' . $_SERVER['SERVER_NAME'];
        if ($_SERVER['SERVER_PORT'] != '80') {
            $server_URL .= ':' . $_SERVER["SERVER_PORT"];
        }
        return $server_URL;
    }

    /**
     * @return string
     */
    public static function getCurrentUrl() {
        return self::getServerUrl() . $_SERVER["REQUEST_URI"];
    }

    /**
     * Convert relative url to absolute
     *
     * @param $url
     * @param null|string $base_url
     * @return string
     */
    public static function getAbsoluteUrl($url, $base_url = null) {
        if ($base_url === null) {
            $base_url = self::getCurrentUrl();
        }
        if (preg_match('#(https?:)?//#', $url)) {
            return $url; // already absolute
        }
        $base = parse_url($base_url);
        if (substr($url, 0, 1) === '/') {
            $result = $base['scheme'] . '://' . $base['host'] . $url;
        } else {
            $path = isset($base['path'])
                ? preg_replace('#/[^/]*$#', '/', $base['path'])
                : '/';
            $result = $base['scheme'] . '://'.  $base['host'] . $path . $url;
        }
        return $result;
    }

    /**
     * Returns GET parameters of given url
     *
     * @param $url
     * @return array|null
     */
    public static function getQueryParams($url) {
        $parsed_url = parse_url($url);
        if (!is_array($parsed_url))
            return null;
        $query = isset($parsed_url['query']) ? $parsed_url['query'] : '';
        parse_str($query, $get_params);
        return $get_params;
    }


    /**
     * Compare GET parameters considering excludes
     *
     * @param array $params1
     * @param array $params2
     * @return bool
     */
    public static function compareQueryParams($params1, $params2) {
        $ignored = array(
            'preview', 'preview_iframe', 'template', 'stylesheet', 'page', 'TB_iframe', // preview-theme
            'original', 'theme', 'wp_customize', 'nonce', // wp-customize
            'custom_template', 'custom_page', 'default' // custom templates
        );
        foreach ($ignored as $key) {
            unset($params1[$key]);
            unset($params2[$key]);
        }
        return $params1 == $params2;
    }

    /**
     * @param string $url1
     * @param string $url2
     * @return bool
     */
    public static function compareUrls($url1, $url2) {
        $url1 = self::getAbsoluteUrl($url1);
        $url2 = self::getAbsoluteUrl($url2);
        $params1 = self::getQueryParams($url1);
        $params2 = self::getQueryParams($url2);

        $parsed_url1 = parse_url($url1);
        $parsed_url2 = parse_url($url2);
        return self::compareQueryParams($params1, $params2)
            && untrailingslashit(theme_get_array_value($parsed_url1, 'path', '')) == untrailingslashit(theme_get_array_value($parsed_url2, 'path', ''))
            && $parsed_url1['host'] === $parsed_url2['host'];
    }
}