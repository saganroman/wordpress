<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Renaming [one_half], [one_third], etc => [row][column]
 *
 * @param string $content
 * @return mixed|string
 */
function themler_column_filter($content) {
    if (ShortcodesUtility::$the_content_depth > 1)
        return $content;


    ShortcodesUtility::stackPush(array(
        '#^column(_\d+)?$#' => 'themler_shortcode_single_column',
        '#^row(_\d+)?$#' => 'themler_shortcode_single_row',
        '#^columns(_\d+)?$#' => 'themler_shortcode_single_row',
    ));
    add_shortcode('one_half', 'themler_shortcode_single_column');
    add_shortcode('one_third', 'themler_shortcode_single_column');
    add_shortcode('two_third', 'themler_shortcode_single_column');
    add_shortcode('one_fourth', 'themler_shortcode_single_column');
    add_shortcode('three_fourth', 'themler_shortcode_single_column');
    add_shortcode('full_width', 'themler_shortcode_single_column');

    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();

    $content = preg_replace('/(<!--\/Column)(?:Last){0,1}(-->)(?!.*<!--\/Column)/s', '$1Last$2', $content, 1); // add 'last' for the last column
    $GLOBALS['inRow'] = false;
    return preg_replace_callback('/<!--Column--><([^>]*?)>(.*?)<!--\/Column(Last){0,1}-->/s', 'themler_column_filter_callback', $content);
}

function themler_column_filter_callback($matches) {
    $result = '';
    if (!$GLOBALS['inRow']) {
        $result .= '[row ' . $matches[1] . ']';
        $GLOBALS['inRow'] = true;
    }
    $result .= $matches[2];
    if (isset($matches[3])) {
        $result .= '[/row]';
        $GLOBALS['inRow'] = false;
    }
    return $result;
}

function themler_shortcode_single_row($atts, $content='', $tag='') {
    global $is_column_in_row;
    $is_column_in_row = true;
    $result = do_shortcode($content);
    $is_column_in_row = false;
    return ShortcodesUtility::makeShortcode($tag, $result, $atts);
}

function themler_shortcode_single_column($atts, $content='', $tag='') {
    switch($tag) {
        case 'one_half':
            $atts['width'] = "12";
            break;
        case 'one_third':
            $atts['width'] = "8";
            break;
        case 'two_third':
            $atts['width'] = "16";
            break;
        case 'one_fourth':
            $atts['width'] = "6";
            break;
        case 'three_fourth':
            $atts['width'] = "18";
            break;
        case 'full_width':
            $atts['width'] = "24";
            break;
    }

    global $is_column_in_row;
    if (isset($is_column_in_row) && $is_column_in_row)
        return ShortcodesUtility::makeShortcode($tag, $content, $atts);

    if (!is_array($atts)) {
        $atts = array();
    }
    $last = isset($atts['last']) ? $atts['last'] : false;

    $new_atts = array();
    foreach($atts as $key => $value) {
        if (is_numeric($key) && 'last' === $value)
            $last = true;
        else
            $new_atts[$key] = $value;
    }

    $row_atts = 'vertical_align="' . (isset($atts['vertical_align']) ? $atts['vertical_align'] : '') . '"' .
        ' auto_height="' . (isset($atts['auto_height']) ? $atts['auto_height'] : '') . '"' .
        ' collapse_spacing="' . (isset($atts['collapse_spacing']) ? $atts['collapse_spacing'] : '') . '"';

    remove_shortcode($tag, 'themler_shortcode_single_column');
    $content = '<!--Column--><' . $row_atts . '>' . ShortcodesUtility::makeShortcode('column', do_shortcode($content), $new_atts) . '<!--/Column' . ($last ? 'Last' : '') . '-->';
    add_shortcode($tag, 'themler_shortcode_single_column');
    return $content;
}


/**
 * Renaming [row] => [columns]
 *
 * @param string $content
 * @return string
 */
function themler_old_row_filter($content) {
    if (ShortcodesUtility::$the_content_depth > 1)
        return $content;

    $add_shortcodes = array();
    foreach(ShortcodesUtility::$shortcodes as $tag => $func) {
        if (preg_match('#^row(_\d+)?$#', $tag))
            $add_shortcodes[str_replace('row', 'columns', $tag)] = $func;
        if (preg_match('#^columns(_\d+)?$#', $tag))
            $add_shortcodes[str_replace('columns', 'row', $tag)] = $func;
    }
    // недостаточно добавить row и columns, нужно добавить row_1, row_2, columns_1, columns_2, и.т.д
    ShortcodesUtility::$shortcodes = array_merge(ShortcodesUtility::$shortcodes, $add_shortcodes);

    ShortcodesUtility::stackPush(array('#^row(_\d+)?$#' => 'themler_old_row_shortcode'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();

    return $content;
}

global $old_rows_stack, $old_columns_atts;
$old_rows_stack = array();

function themler_old_column_shortcode_collect_width($atts, $content, $tag) {
    global $old_rows_stack;
    if (count($old_rows_stack) > 0) {
        $old_rows_stack[count($old_rows_stack) - 1][] = $atts;
    }
    return '';
}

function themler_old_column_shortcode_set_width($atts, $content, $tag) {
    global $old_row_data;
    $result = ShortcodesUtility::makeShortcode($tag, $content, $old_row_data[0]);
    $old_row_data = array_splice($old_row_data, 1);
    return $result;
}

function themler_old_row_shortcode($atts, $content, $tag) {
    global $old_rows_stack, $old_row_data;

    $old_rows_stack[] = array();

    ShortcodesUtility::stackPush(array('#^column(_\d+)?$#' => 'themler_old_column_shortcode_collect_width'));
    do_shortcode($content);
    ShortcodesUtility::stackPop();

    $old_row_data = array_pop($old_rows_stack);
    $items = &$old_row_data;

    $supports = get_theme_support('themler-core');
    if (is_array($supports) && in_array(array('grid-columns-12'), $supports)) {
        foreach (array('width', 'width_sm', 'width_lg', 'width_xs') as $prop) {

            $sum = 0;
            $odds = array();
            $evens = array();
            $difference = 0;

            $len = count($items);

            for ($i = 0; $i < $len; $i++) {
                if (!isset($items[$i][$prop])) {
                    continue;
                }

                $value = intval($items[$i][$prop]);

                if ($value) {
                    if (!isset($max_value_index) || $value > intval($items[$max_value_index][$prop])) {
                        $max_value_index = $i;
                    }

                    if ($value % 2 === 0) {
                        $evens[$i] = $value;
                    } else {
                        $odds[$i] = $value;
                    }

                    $sum += intval($items[$i][$prop]);
                }
            }

            if ($sum && isset($max_value_index)) {
                for ($i = 0; $i < $len; $i++) {
                    if (!isset($items[$i][$prop])) {
                        continue;
                    }

                    $value = intval($items[$i][$prop]);

                    if (isset($odds[$i])) {
                        if ($odds[$i] === 1) {
                            $items[$i][$prop] = 1;
                            $difference -= 0.5;
                        } else {
                            $items[$i][$prop] = floor($value / 2);
                            $difference += 0.5;
                        }
                    } else if (isset($evens[$i])) {
                        $items[$i][$prop] = $value / 2;
                    }
                }

                if ($difference > 0 && intval($difference) == $difference) {
                    $items[$max_value_index][$prop] = intval($items[$max_value_index][$prop]) + $difference;
                }
            }
        }
    }

    ShortcodesUtility::stackPush(array('#^column(_\d+)?$#' => 'themler_old_column_shortcode_set_width'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();

    return ShortcodesUtility::makeShortcode(str_replace('row', 'columns', $tag), do_shortcode($content), $atts);
}


/**
 * Remove align content
 * See Common backward-4.12.0 for more details
 *
 * @param string $content
 * @return string
 */
function themler_old_align_content_filter($content) {
    if (ShortcodesUtility::$the_content_depth > 1)
        return $content;

    ShortcodesUtility::stackPush(array('#^align_content(_\d+)?$#' => '_themler_old_align_content_shortcode'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();
    return $content;
}

function _themler_get_first_inner_container(&$control) {
    if (!_themler_is_container($control['tag']) || ShortcodeParser::isControl($control['tag'], 'columns'))
        return null;

    $content = &$control['content'];
    $items = ShortcodeParser::getChilds($content);
    if (count($items) == 0) {
        return null;
    }
    if (count($items) == 1) {
        return $items[0];
    }
    $res = ShortcodeParser::createControl('layoutbox');
    $res['content'] = $content;
    return $res;
}

ShortcodesUtility::addEffectShortcode('align_content', '__return_null');

function _themler_old_align_content_shortcode($atts, $content, $tag) {
    $content = do_shortcode($content);
    $childs = ShortcodeParser::getChilds($content);
    if (count($childs) !== 1) {
        return $content;
    }
    $control = &$childs[0];

    ShortcodeParser::copyCss($atts, $control['atts'], array(
        'padding-top',
        'padding-right',
        'padding-bottom',
        'padding-left',
        'padding',
    ));

    $align_width = ShortcodesEffects::css_prop($atts, 'width');
    $height = ShortcodesEffects::css_prop($control['atts'], 'height');
    if ($align_width && $align_width !== '100%' ||
        ShortcodesEffects::css_prop($control['atts'], 'width') ||
        $height && strpos($height, '%') === false) {

        $inner_container = _themler_get_first_inner_container($control);
        if ($inner_container) {
            $margin_top = _themler_get_margin($atts, 'top');
            $margin_bottom = _themler_get_margin($atts, 'bottom');
            $margin_left = _themler_get_margin($atts, 'left');
            $margin_right = _themler_get_margin($atts, 'right');

            if (!ShortcodesEffects::css_prop($inner_container['atts'], 'width'))
                ShortcodeParser::copyCss($atts, $inner_container['atts'], array('width'));

            $is_vertically_aligned = $margin_top !== '0px' && ($height && strpos($height, '%') === false);
            $transform_left = '0';
            $transform_top = '0';
            if ($is_vertically_aligned) {
                ShortcodeParser::applyCss($inner_container, 'position', 'absolute');
                if ($margin_left === 'auto' && $margin_right === 'auto') {
                    ShortcodeParser::applyCss($inner_container, 'left', '50%');
                    $transform_left = '-50%';
                }
                if ($margin_left === '0px') {
                    ShortcodeParser::applyCss($inner_container, 'left', '0');
                }
                if ($margin_right === '0px') {
                    ShortcodeParser::applyCss($inner_container, 'right', '0');
                }

                if ($margin_top === 'auto' && $margin_bottom === 'auto') {
                    ShortcodeParser::applyCss($inner_container, 'top', '50%');
                    $transform_top = '-50%';
                }
                if ($margin_top === '0px') {
                    ShortcodeParser::applyCss($inner_container, 'top', '0');
                }
                if ($margin_bottom === '0px') {
                    ShortcodeParser::applyCss($inner_container, 'bottom', '0');
                }
            } else {
                if ($margin_left)
                    ShortcodeParser::applyCss($inner_container, 'margin-left', $margin_left);
                if ($margin_right)
                    ShortcodeParser::applyCss($inner_container, 'margin-right', $margin_right);

                if (!ShortcodesEffects::css_prop($inner_container['atts'], 'width') && $margin_left === 'auto' && $margin_right === 'auto') {
                    ShortcodeParser::applyCss($inner_container, 'display', 'inline-block');
                    ShortcodeParser::applyCss($inner_container, 'margin-left', '50%');
                    ShortcodeParser::applyCss($inner_container, 'margin-right', '-50%');
                    $transform_left = '-50%';
                }
            }
            if ($transform_top || $transform_left) {
                ShortcodeParser::applyCss($inner_container, 'transform', 'translateX(' . $transform_left . ') translateY(' . $transform_top . ')');
            }

            if (isset($atts['sheet_align']) && ShortcodesUtility::getBool($atts['sheet_align']) && !ShortcodeParser::hasEffect($control, 'background_width')) {
                $background_width = ShortcodeParser::createControl('background_width');
                ShortcodeParser::applyEffect($control, $background_width);
            }

            $inner_container = ShortcodeParser::stringify($inner_container);
            $control['content'] = $inner_container;
        }
    }

    if (ShortcodeParser::isControl($control['tag'], 'layoutbox') && (!isset($atts['sheet_align']) || !ShortcodesUtility::getBool($atts['sheet_align'])) && !ShortcodeParser::hasEffect($control, 'container_inner_effect')) {
        $container_inner_effect = ShortcodeParser::createControl('container_inner_effect');
        ShortcodeParser::applyEffect($control, $container_inner_effect);
    }

    if (ShortcodeParser::isControl($control['tag'], 'section') && isset($atts['sheet_align']) && ShortcodesUtility::getBool($atts['sheet_align']) && !ShortcodeParser::hasEffect($control, 'background_width')) {
        $background_width = ShortcodeParser::createControl('background_width');
        ShortcodeParser::applyEffect($control, $background_width);
    }

    $content = ShortcodeParser::stringify($control);
    return $content;
}

/**
 * @param string $content
 * @return string
 */
function themler_old_box_absolute_filter($content) {
    if (ShortcodesUtility::$the_content_depth > 1)
        return $content;

    ShortcodesUtility::stackPush(array('#^box_absolute(_\d+)?$#' => '_themler_old_box_absolute_shortcode'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();

    return $content;
}

// for collecting [box_absolute_1], [box_absolute_2], etc
ShortcodesUtility::addShortcode('box_absolute', '__return_null');

function _themler_old_box_absolute_shortcode($atts, $content, $tag) {
    if ($tag === 'box_absolute') {
        $tag = 'layoutbox_0';
    } else {
        $tag = str_replace('box_absolute_', 'layoutbox_0', $tag);
    }
    ShortcodesUtility::extendTag($tag, 'layoutbox');
    return ShortcodesUtility::makeShortcode($tag, do_shortcode($content), $atts);
}


/**
 * @param string $content
 * @return string
 */
function themler_old_container_inner_effect_filter($content) {
    if (ShortcodesUtility::$the_content_depth > 1)
        return $content;

    ShortcodesUtility::stackPush(array('#^container_inner_effect(_\d+)?$#' => '_themler_old_container_inner_effect_shortcode'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();

    ShortcodesUtility::stackPush(array('#^container_inner_effect(_\d+)?$#' => '_themler_convert_old_container_inner_effect_shortcode'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();
    return $content;
}
ShortcodesUtility::addEffectShortcode('container_inner_effect', '__return_null');

function _themler_get_margin(&$atts, $dir) {
    if (empty($atts['css']))
        return '';

    $margin = ShortcodesEffects::css_prop($atts, 'margin');
    if ($margin)
        return $margin;
    return ShortcodesEffects::css_prop($atts, 'margin-' . $dir);
}

function _themler_convert_old_container_inner_effect_shortcode($atts, $content, $tag) {
    $content = do_shortcode($content);
    $new_tag = str_replace('container_inner_effect', 'fluid', $tag);
    ShortcodesUtility::extendTag($new_tag, 'fluid');
    return ShortcodesUtility::makeShortcode($new_tag, $content, $atts);
}

function _themler_old_container_inner_effect_shortcode($atts, $content, $tag) {
    $childs = ShortcodeParser::getChilds($content);
    if (!$childs || count($childs) !== 1)
        return $content;

    $control = &$childs[0];
    $control['effects'][] = array(
        'atts' => is_array($atts) ? $atts : array(),
        'tag' => $tag,
    );
    _themler_set_fluid_to_childrens($control);
    return ShortcodeParser::stringify($control);
}

function _themler_is_container($tag) {
    foreach(array(
                'columns',
                'column',
                'slider',
                'slide',
                'layoutbox',
                'box_absolute',
                'section',
            ) as $type) {
        if (ShortcodeParser::isControl($tag, $type)) {
            return true;
        }
    }
    return false;
}

function _themler_set_fluid_to_childrens(&$control) {
    if (!_themler_is_container($control['tag']))
        return;
    if (ShortcodeParser::isControl($control['tag'], 'slider')) {
        $control['atts']['slides_wide'] = '0';
    }

    $childs = ShortcodeParser::getChilds($control['content']);
    foreach($childs as &$child) {
        if (!_themler_is_row_positioned($child))
            continue;
        if (_themler_has_container_inner($child['tag'])) {
            if (!ShortcodeParser::hasEffect($child, 'background_width') && !ShortcodeParser::hasEffect($child, 'container_effect')) {
                if (_themler_can_apply_container_inner_effect($child)) {
                    $effect = ShortcodeParser::createControl('container_inner_effect');
                    ShortcodeParser::applyEffect($child, $effect);
                    _themler_set_fluid_to_childrens($child);
                }
            }
        } else {
            _themler_set_fluid_to_childrens($child);
        }
    }
    $control['content'] = ShortcodeParser::stringifyList($childs);
}

function _themler_has_container_inner($tag) {
    foreach(array(
                'separator',
                'columns',
                'text_group',
                'slider',
                'box_absolute',
                'html',
                'layoutbox',
                'section',
            ) as $type) {
        if (ShortcodeParser::isControl($tag, $type)) {
            return true;
        }
    }
    return false;
}

function _themler_can_apply_container_inner_effect(&$control) {
    $align_effect = ShortcodeParser::getEffect($control, 'align_content');
    return !ShortcodeParser::hasEffect($control, ' background_width') &&
    !ShortcodeParser::hasEffect($control, 'container_effect') &&
    !ShortcodeParser::hasEffect($control, 'container_inner_effect') &&
    (!$align_effect || !isset($align_effect['atts']['sheet_align']) || !ShortcodesUtility::getBool($align_effect['atts']['sheet_align']));
}

function _themler_is_row_positioned(&$control) {
    $position = ShortcodesEffects::css_prop($control['atts'], 'position');
    $float = ShortcodesEffects::css_prop($control['atts'], 'float');
    $width = ShortcodesEffects::css_prop($control['atts'], 'width');
    $max_width = ShortcodesEffects::css_prop($control['atts'], 'max-width');
    return !(
        ShortcodeParser::isControl($control['tag'], 'column') ||
        ($position === 'absolute' && !$width && !$max_width) ||
        $position === 'fixed' ||
        $float === 'left' || $float === 'right'
    );
}


/**
 * @param string $content
 * @return string
 */
function themler_old_section_filter($content) {
    if (ShortcodesUtility::$the_content_depth > 1)
        return $content;

    ShortcodesUtility::stackPush(array('#^background_width(_\d+)?$#' => '_themler_old_background_width_shortcode'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();

    ShortcodesUtility::stackPush(array('#^section(_\d+)?$#' => '_themler_old_section_shortcode'));
    $content = do_shortcode($content);
    ShortcodesUtility::stackPop();

    return $content;
}

function _themler_old_background_width_shortcode($atts, $content, $tag) {
    $content = do_shortcode($content);
    $childs = ShortcodeParser::getChilds($content);
    if (count($childs) === 1 && ShortcodeParser::isControl($childs[0]['tag'], 'section')) {
        $childs[0]['atts']['wide'] = '1';
        $content = ShortcodeParser::stringify($childs[0]);
    }
    return ShortcodesUtility::makeShortcode($tag, $content, $atts);
}

function _themler_old_section_shortcode($atts, $content, $tag) {
    $content = do_shortcode($content);
    $shortcode = ShortcodesUtility::makeShortcode($tag, $content, $atts);
    if (!isset($atts['wide']) || !ShortcodesUtility::getBool($atts['wide'])) {
        $control = ShortcodeParser::getChilds($shortcode);
        $control = $control[0];
        $fluid = ShortcodeParser::createControl('fluid');
        ShortcodeParser::applyEffect($control, $fluid);

        $inner_container = _themler_get_first_inner_container($control);

        if ($inner_container && !ShortcodeParser::hasEffect($inner_container, 'background_width') && !ShortcodeParser::hasEffect($inner_container, 'fluid')) {
            $fluid = ShortcodeParser::createControl('fluid');
            ShortcodeParser::applyEffect($inner_container, $fluid);
        }
        $control['content'] = ShortcodeParser::stringify($inner_container);
        $shortcode = ShortcodeParser::stringify($control);
    }
    return $shortcode;
}


// [box css="" full_width="yes|no" content_width="yes|no"]content with shortcodes[/box]
function themler_shortcode_box($atts, $content = '') {
    $atts = shortcode_atts(array(
        'css' => '',
        'content_width' => 'yes',
        'class_names' => ''
    ), $atts);

    $css = esc_attr($atts['css']);
    $content_width = $atts['content_width'] === 'yes';
    $class_names = esc_attr($atts['class_names']);

    $result = '<div';
    if ($class_names !== '') {
        $result .= ' class="' . $class_names . '"';
    }
    if ($css !== '') {
        $result .= ' style="' . $css . '"';
    }
    $result .= '>';
    if ($content_width) {
        $result .= '<div class="bd-container-inner">';
    }
    $result .= do_shortcode($content);
    if ($content_width) {
        $result .= '</div>';
    }
    $result .= '</div>';
    return $result;
}
add_shortcode('box', 'themler_shortcode_box');