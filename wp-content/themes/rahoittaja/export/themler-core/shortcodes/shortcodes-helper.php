<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ShortcodesUtility
{
    public static function atts($defaults, $atts, $additional = array('')) {
        foreach($additional as $prefix) {
            foreach(array('', '_md', '_sm', '_xs') as $mode) {
                $defaults[$prefix . 'css' . $mode] = '';
                $defaults[$prefix . 'typography' . $mode] = '';
            }
            $defaults[$prefix . 'hide'] = '';
        }
        return shortcode_atts($defaults, $atts);
    }

    public static function getBool($value, $default_value = false) {
        if ($value === true || $value === '1' || $value === 'true' || $value === 'yes')
            return true;
        if ($value === false || $value === '0' || $value === 'false' || $value === 'no')
            return false;
        return $default_value;
    }

    public static function isPreview() {
        return function_exists('theme_can_view_preview') && theme_can_view_preview();
    }

    public static function escape($text) {
        return esc_attr($text);
    }

    public static $the_content_depth = 0;
    public static $unautop_storage;

    public static function doShortcode($content, $enable_shortcodes = true, $allow_paragraphs = false) {

        if ($enable_shortcodes) {
            $content = preg_replace('#^<\/p>|^<br \/>|<p>$#', '', $content);
            $content = do_shortcode(shortcode_unautop(trim($content)));
        } else {
            foreach (self::$shortcodes as $tag => $func) {
                remove_shortcode($tag);
            }

            $remove_wpautop = !$allow_paragraphs && has_filter('the_content', 'wpautop');

            if ($remove_wpautop) {
                remove_filter('the_content', 'wpautop');
            }

            $content = apply_filters('the_content', $content);

            if ($remove_wpautop) {
                add_filter('the_content', 'wpautop');
            }

            foreach (self::$shortcodes as $tag => $func) {
                add_shortcode($tag, $func);
            }
        }
        return $content;
    }

    public static function addShortcode($tag, $_func) {
        $func = array(new ShortcodeUnautopFuncWrapper($_func), 'func');

        self::$shortcodeTypes[$tag] = $func;
        self::$shortcodeType[$tag] = 'control';
        self::$shortcodes[$tag] = $func;

        add_shortcode($tag, $func);
    }

    public static function addEffectShortcode($tag, $_func) {
        self::addShortcode($tag, $_func);
        self::$shortcodeType[$tag] = 'effect';
    }

    public static $shortcodes = array();
    public static $shortcodeTypes = array();
    public static $shortcodeType = array();

    public static function extendTag($tag, $type) {
        $func = self::$shortcodeTypes[$type];
        if (!shortcode_exists($tag)) {
            add_shortcode($tag, $func);
            self::$shortcodes[$tag] = $func;
        }
    }

    public static function collectShortcodes($content) {
        if (self::$the_content_depth <= 1) {
            $pattern = '\[\/((' . join('|', array_keys(self::$shortcodeTypes)) . ')_\d+)\]';
            if (preg_match_all("#$pattern#", $content, $matches)) {
                $matches_count = count($matches[0]);
                for ($i = 0; $i < $matches_count; $i++) {
                    $tag = $matches[1][$i];
                    $type = $matches[2][$i];
                    self::extendTag($tag, $type);
                }
            }
        }
        return $content;
    }


    private static $_parentTags;

    public static function collectShortcodeFunc($atts, $content = '', $tag = '') {
        if (!is_array($atts))
            $atts = array();

        $closed = !isset(self::$_parentTags[$tag]);

        self::$_parentTags[$tag] = true;
        $content = do_shortcode($content);
        if ($closed) {
            unset(self::$_parentTags[$tag]);
        }

        if (empty(self::$_parentTags)) {
            $id = count(self::$unautop_storage);
            self::$unautop_storage[] = $content;
            $atts['_id'] = $id;
            return self::makeShortcode($tag . '_unautop', '', $atts, $closed);
        }

        return self::makeShortcode($tag . '_unautop', $content, $atts, $closed);
    }

    public static function beforeTheContent($content) {
        self::$the_content_depth++;
        if (self::$the_content_depth <= 1) {
            self::$unautop_storage = array();

            global $shortcode_tags;
            $orig_shortcode_tags = $shortcode_tags; // save original shortcodes
            $shortcode_tags = array();

            foreach (self::$shortcodes as $tag => $func) {
                add_shortcode($tag, 'ShortcodesUtility::collectShortcodeFunc');
            }

            self::$_parentTags = array();
            $content = do_shortcode($content);
            $shortcode_tags = $orig_shortcode_tags; // restore original shortcodes

            // replace themler shortcodes with unautop shortcodes
            foreach (self::$shortcodes as $tag => $func) {
                remove_shortcode($tag);
                add_shortcode($tag . '_unautop', $func);
            }
        }
        return $content;
    }

    public static function afterTheContent($content) {
        if (self::$the_content_depth <= 1) {
            global $shortcode_tags;
            $orig_shortcode_tags = $shortcode_tags; // save original shortcodes
            $shortcode_tags = array();

            foreach (self::$shortcodes as $tag => $func) {
                add_shortcode($tag . '_unautop', 'ShortcodesUtility::restoreUnrenderedShortcodes');
            }

            $content = do_shortcode($content);
            $shortcode_tags = $orig_shortcode_tags; // restore original shortcodes

            // inversely, replace unautop shortcodes with themler shortcodes
            foreach (self::$shortcodes as $tag => $func) {
                remove_shortcode($tag . '_unautop');
                add_shortcode($tag, $func);
            }
        }
        self::$the_content_depth--;
        return $content;
    }

    public static function restoreUnrenderedShortcodes($atts, $content = '', $tag = '') {
        if (isset($atts['_id'])) {
            $content = self::$unautop_storage[$atts['_id']];
            unset($atts['_id']);
        }
        return self::makeShortcode(str_replace('_unautop', '', $tag), do_shortcode($content), $atts);
    }

    public static function makeShortcode($tag, $content, $atts, $closed = true) {
        if (!is_array($atts))
            $atts = array();

        $code = "[$tag";
        foreach($atts as $key => $value) {
            if (is_numeric($key)) {
                $code .= " $value";
            } else {
                $code .= " $key=\"$value\"";
            }
        }
        if (!$closed && !$content) {
            return "$code]";
        }
        return "$code]$content" . "[/$tag]";
    }


    private static $_bad_quote = "\xe2\x80\x9d";

    private static function _convertBadAttributes($attrs) {
        return str_replace(self::$_bad_quote, '"', $attrs);
    }

    /**
     * This function if modified version of do_shortcode_tag
     * see wp-includes/shortcodes.php
     *
     * @param array $m matches
     * @return string
     */
    public static function convertBadShortcode($m) {
        // allow [[foo]] syntax for escaping a tag
        if ($m[1] == '[' && $m[6] == ']') {
            return $m[0];
        }

        /**
         * 2 - tag
         * 3 - attributes
         * 5 - inner content
         */

        $attrs = self::_convertBadAttributes($m[3]);
        if ($attrs) {
            $attrs = " $attrs";
        }

        if (isset($m[5])) {
            // enclosing tag - extra parameter
            return $m[1] . '[' . $m[2] . $attrs . ']' . self::convertBadContent($m[5]) . '[/' . $m[2] . ']' . $m[6];
        } else {
            // self-closing tag
            return $m[1] . '[' . $m[2] . $attrs . ']' . $m[6];
        }
    }

    /**
     * Replace 0x201D (unicode) symbol in shortcode attributes
     *
     * This function if modified version of do_shortcode
     * see wp-includes/shortcodes.php
     *
     * @param string $content
     * @return string
     */
    public static function convertBadContent($content) {

        global $shortcode_tags;

        if (false === strpos($content, '[') || false === strpos($content, self::$_bad_quote)) {
            return $content;
        }

        if (empty($shortcode_tags) || !is_array($shortcode_tags)) {
            return $content;
        }

        // Find all registered tag names in $content.
        preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
        $tagnames = array_intersect(array_keys($shortcode_tags), $matches[1]);

        if (empty($tagnames)) {
            return $content;
        }

        $pattern = get_shortcode_regex($tagnames);
        $content = preg_replace_callback("/$pattern/", 'ShortcodesUtility::convertBadShortcode', $content);

        return $content;
    }


    private static $_styles = array();

    public static function addResult($content, $styles = '') {
        return $styles . '<!--item-->' . $content . '<!--/item-->';
    }

    public static function _resultCollectStylesCallback($matches) {
        self::$_styles[] = $matches[1];
        return '';
    }

    public static function processResult($content) {
        self::$_styles = array();
        $parts = preg_split('#<!--\/?item-->#', $content);
        $length = count($parts);
        for ($i = 0; $i < $length; $i += 2) {
            $parts[$i] = preg_replace_callback('#<style>([\s\S]*?)<\/style>#', 'ShortcodesUtility::_resultCollectStylesCallback', $parts[$i]);
        }
        return array(join('', $parts), '<style>' . join("\n", self::$_styles) . '</style>');
    }

    private static $_shortcodes_stack = array();

    public static function stackPush($funcs) {
        global $shortcode_tags;

        self::$_shortcodes_stack[] = $shortcode_tags; // save original shortcodes
        $shortcode_tags = array();

        foreach(array_merge(self::$_shortcodes_stack[count(self::$_shortcodes_stack) - 1], self::$shortcodes) as $tag => $func) {
            foreach($funcs as $regex => $fn) {
                if (preg_match($regex, $tag)) {
                    add_shortcode($tag, $fn);
                    break;
                }
            }
        }
    }

    public static function stackPop() {
        global $shortcode_tags;
        $shortcode_tags = array_pop(self::$_shortcodes_stack); // restore original shortcodes
    }

    public static function renderShortcode($content, $tag, $func) {
        self::stackPush(array(
            "#^$tag$#" => $func
        ));
        $ret = do_shortcode($content);
        self::stackPop();
        return $ret;
    }
}


class ShortcodeParser {
    private static $_items;
    private static $_effects_stack;

    public static function shortcodeFunc($atts, $content, $tag) {
        if (!is_array($atts)) {
            $atts = array();
        }
        if (self::isEffect($tag)) {
            self::$_effects_stack[] = array(
                'tag' => $tag,
                'atts' => $atts,
            );
            do_shortcode($content);
            return;
        }
        self::$_items[] = array(
            'tag' => $tag,
            'content' => $content,
            'atts' => $atts,
            'effects' => self::$_effects_stack,
        );
        self::$_effects_stack = array();
    }

    /**
     * @param string $content
     * @return array [
     *  string tag
     *  string content
     *  array atts
     *  array effects
     * ]
     */
    public static function getChilds($content) {
        ShortcodesUtility::stackPush(array());
        foreach(ShortcodesUtility::$shortcodes as $tag => $func)
            add_shortcode($tag, 'ShortcodeParser::shortcodeFunc');
        self::$_items = array();
        self::$_effects_stack = array();
        do_shortcode($content);
        ShortcodesUtility::stackPop();
        return self::$_items;
    }

    /**
     * @param string $tag
     * @param string $type
     * @return bool
     */
    public static function isEffect($tag, $type = '') {
        $tag = preg_replace('#^(.*)_\d+$#', '$1', $tag);
        if (!isset(ShortcodesUtility::$shortcodeType[$tag]) || ShortcodesUtility::$shortcodeType[$tag] !== 'effect') {
            return false;
        }
        if ($type) {
            return $type === $tag;
        }
        return true;
    }

    /**
     * Check if $tag presents the given $type
     * f.e. isControl('section_21', 'section') = true
     *
     * @param string $tag
     * @param string $type
     * @return bool
     */
    public static function isControl($tag, $type) {
        $tag = preg_replace('#^(.*)_\d+$#', '$1', $tag);
        if ($type) {
            return $type === $tag;
        }
        return true;
    }

    /**
     * @param array $control
     * @param string $type
     */
    public static function removeEffect(&$control, $type) {
        foreach($control['effects'] as $i => $effect) {
            if (self::isEffect($effect['tag'], $type)) {
                unset($control['effects'][$i]);
            }
        }
    }

    /**
     * @param array $control
     * @param string $type
     * @return bool
     */
    public static function hasEffect(&$control, $type) {
        return self::getEffect($control, $type) !== null;
    }

    /**
     * @param array $control
     * @param string $type
     * @return array|null
     */
    public static function getEffect(&$control, $type) {
        foreach($control['effects'] as $i => &$effect) {
            if (self::isEffect($effect['tag'], $type)) {
                return $effect;
            }
        }
        return null;
    }

    // HACK: because inner scope may contain small ids
    private static $_id_counter = 10000;

    public static function createControl($type) {
        $id = self::$_id_counter++;
        $tag = $type;
        if ($id) {
            $tag .= "_$id";
            ShortcodesUtility::extendTag($tag, $type);
        }
        $return = array(
            'tag' => $tag,
            'atts' => array(),
        );
        if (!self::isEffect($type)) {
            $return['effects'] = array();
        }
        return $return;
    }

    /**
     * Add effect outside (at first position)
     *
     * @param array $control
     * @param array $effect
     */
    public static function applyEffect(&$control, $effect) {
        array_unshift($control['effects'], $effect);
    }

    public static function copyCss(&$src, &$dest, $rules) {
        foreach(array('', '_md', '_sm', '_xs') as $mode) {
            $dest_css = isset($dest["css$mode"]) ? $dest["css$mode"] : '';
            foreach($rules as $rule) {
                $value = ShortcodesEffects::css_prop($src, $rule, $mode);
                if ($value) {
                    $dest_css .= $rule . ':' . ShortcodesEffects::css_prop($src, $rule, $mode) . ';';
                }
            }
            $dest["css$mode"] = $dest_css;
        }
    }


    /**
     * Convert control to shortcode
     *
     * @param $control
     * @param int $effects_iter
     * @return string
     */
    public static function stringify(&$control, $effects_iter = 0) {
        if (empty($control)) {
            return '';
        }
        if (is_string($control)) {
            return $control;
        }

        if (count($control['effects']) === $effects_iter) {
            return ShortcodesUtility::makeShortcode($control['tag'], $control['content'], $control['atts']);
        }
        return ShortcodesUtility::makeShortcode($control['effects'][$effects_iter]['tag'], self::stringify($control, $effects_iter + 1), $control['effects'][$effects_iter]['atts']);
    }

    public static function stringifyList(&$list) {
        $res = '';
        foreach($list as &$item) {
            $res .= self::stringify($item);
        }
        return $res;
    }

    public static function applyCss(&$control, $key, $value) {
        if (!isset($control['atts']['css'])) {
            $control['atts']['css'] = '';
        }
        $control['atts']['css'] .= $key . ':' . $value . ';';
    }
}

class ShortcodeUnautopFuncWrapper {
    private $_func;

    public function __construct($func) {
        $this->_func = $func;
    }

    public function func($atts, $content, $tag) {
        if (isset($atts["_id"])) {
            $content = ShortcodesUtility::$unautop_storage[$atts["_id"]];
        }
        return call_user_func($this->_func, $atts, $content, str_replace("_unautop", "", $tag));
    }
}