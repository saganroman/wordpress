<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ShortcodesEffects
{
    const CONTROL = 0;
    const HTML_EFFECT = 1;
    const CSS_EFFECT = 2;
    
    private static $_stack = array();

    public static $responsive_rules = array(
        '' => array('', ''),
        '_md' => array('@media (max-width: 1199px) {', '}'),
        '_sm' => array('@media (max-width: 991px) {', '}'),
        '_xs' => array('@media (max-width: 767px) {', '}')
    );

    public static $responsive_rules_min = array(
        '' => array('', ''),
        '_md' => array('@media (min-width: 1200px) {', '}'),
        '_sm' => array('@media (min-width: 992px) {', '}'),
        '_xs' => array('@media (min-width: 768px) {', '}')
    );

    public static function getResponsiveModes() {
        return array_keys(self::$responsive_rules);
    }

    // todo: fill from themler.CONST
    private static $_effects_defaults = array(
        'positioning' => array(
            'position' => 'relative',
            'left'     => 'auto',
            'right'    => 'auto',
            'top'      => 'auto',
            'bottom'   => 'auto',
            'float'    => 'none',
            'width'    => '100%',
            'height'   => '100%',
            'z-index'  => 'auto',
            'margin-left'   => '0',
            'margin-right'  => '0',
            'margin-top'    => '0',
            'margin-bottom' => '0',
            'margin' => '0',
            'display' => 'inline'
        ),
        'transform' => array(
            'transform' => 'rotate(0deg)'
        )
    );

    private static $_skip_attributes = array(
        'positioning' => array(
            'display' => array('inline-block', 'block')
        )
    );


    private static $_css_groups = array(
        'background' => array(
            'background-clip', 'background-origin', 'background-size',
            'background-attachment', 'background-color', 'background-image',
            'background-repeat', 'background-position'
        ),
        'border' => array(
            'border-top-width', 'border-top-style', 'border-top-color',
            'border-right-width', 'border-right-style', 'border-right-color',
            'border-bottom-width', 'border-bottom-style', 'border-bottom-color',
            'border-left-width', 'border-left-style', 'border-left-color'
        ),
        'border-radius' => array(
            'border-top-left-radius', 'border-top-right-radius',
            'border-bottom-right-radius', 'border-bottom-left-radius'
        ),
        'margin' => array('margin-top', 'margin-right', 'margin-bottom', 'margin-left'),
        'padding' => array('padding-top', 'padding-right', 'padding-bottom', 'padding-left'),
        'size' => array('width', 'min-width', 'max-width', 'height', 'min-height', 'max-height'),
        'overflow' => array('overflow-x', 'overflow-y'),
        'transform' => array('transform'),
        'transition' => array('transition', '-webkit-transition'),
        'positioning' => array(
            'top', 'left', 'right', 'bottom',
            'clear', 'clip', 'cursor', 'display',
            'position', 'visibility', 'z-index'
        )
    );

    private static $_tags_styles_types = array(
        // name => type_category
        "blockquotes" => "blockquotes",
        "list" => "BulletList",
        "button" => "Button",
        "image" => "Image",
        "table" => "Table",
        "input" => "inputs",
        "ordered" => "OrderedList"
    );

    public static function tagsStylesAtts($atts, $prefixes = array('')) {

        foreach($prefixes as $prefix) {
            $atts["{$prefix}tags_styles"] = true;
            foreach (self::$_tags_styles_types as $type => $type_category) {
                $atts["{$prefix}tag_{$type}_style"] = '';
            }
            $atts["{$prefix}tag_button_type"] = '';
            $atts["{$prefix}tag_button_style"] = '';
            $atts["{$prefix}tag_button_size"] = '';

            $atts["{$prefix}tag_image_type"] = '';
            $atts["{$prefix}tag_image_shape"] = '';
            $atts["{$prefix}tag_image_responsive"] = '';

            $atts["{$prefix}tag_table_type"] = '';
            $atts["{$prefix}tag_table_striped"] = '';
            $atts["{$prefix}tag_table_bordered"] = '';
            $atts["{$prefix}tag_table_hover"] = '';
            $atts["{$prefix}tag_table_condensed"] = '';
            $atts["{$prefix}tag_table_responsive"] = '';
        }
        return $atts;
    }

    private static function _cssGroups($prop) {
        $groups = array();
        $groups[] = $prop;

        if (array_key_exists($prop, self::$_css_groups)) {
            $groups = array_merge($groups, self::$_css_groups[$prop]);
        } else {
            $groups = array_merge($groups, array($prop));
        }

        return $groups;
    }

    public static function css_prop($atts, $prop, $mode = '', $prefix = '') {
        $atts = self::filter($atts, $prop, $prefix);
        $css = isset($atts[$prefix . "css$mode"]) ? $atts[$prefix . "css$mode"] : '';
        if (false !== ($pos = strpos($css, ':'))) {
            if (trim(substr($css, 0, $pos)) !== $prop) {
                return '';
            }
            $css = trim(substr($css, $pos + 1));
            $css = preg_replace('#;$#', '', $css);
        }
        return $css;
    }

    public static function filter($atts, $filter, $prefix = '') {
        $filters = array_map('trim', explode(',', $filter));

        $excludes = array();
        $includes = array();

        foreach($filters as $filter) {
            if (!$filter)
                continue;
            if ($filter[0] === '!') {
                $excludes = array_merge(
                    $excludes,
                    self::_cssGroups(substr($filter, 1))
                );
            } else {
                $includes = array_merge(
                    $includes,
                    self::_cssGroups($filter)
                );
            }
        }

        $excludeRE = count($excludes) ? '#^(' . join('|', $excludes) . ')\s*:#' : '';
        $includeRE = count($includes) ? '#^(' . join('|', $includes) . ')\s*:#' : '';

        foreach(array('css', 'css_md', 'css_sm', 'css_xs') as $css_mode) {
            $css_mode = $prefix . $css_mode;
            if (empty($atts[$css_mode])) continue;

            preg_match_all ('#(?:[\w\-]+):(?:url\([^\)]+\)|[^;]+?)+#', $atts[$css_mode], $rules);
            $rules = array_map('trim', $rules[0]);

            if ($includeRE) {
                $rules = preg_grep($includeRE, $rules);
            }

            if ($excludeRE) {
                $rules = preg_grep($excludeRE, $rules, PREG_GREP_INVERT);
            }

            $rules = array_filter(array_map('trim', $rules));

            if ($rules)
                $atts[$css_mode] = join(';', $rules) . ';';
            else
                $atts[$css_mode] = '';
        }

        return $atts;
    }

    private static function _effectCssCurrent($rules = '', $groups, $skipProps) {
        $result = array();
        $rules = explode(';', $rules);

        foreach ($groups as $group) {
            $defaultCss = self::$_effects_defaults[$group];

            foreach ($rules as $rule) {
                if (!trim($rule)) continue;
                $ruleParts = explode(':', $rule, 2);
                if (count($ruleParts) !== 2) continue;

                list ($prop, $value) = $ruleParts;
                $prop = trim($prop);
                if (!in_array($prop, $skipProps) && isset($defaultCss[$prop])) {
                    $result[] = $prop . ':' . $value;
                }
            }
        }

        return join(';', $result);
    }

    private static function _effectCssDefaults($rules = '', $groups, $skipProps) {
        $result = array();
        $rules = explode(';', $rules);

        foreach ($groups as $group) {
            $defaultCss = self::$_effects_defaults[$group];

            foreach ($rules as $rule) {
                if (!trim($rule)) continue;
                $ruleParts = explode(':', $rule, 2);
                if (count($ruleParts) !== 2) continue;

                list ($prop, ) = $ruleParts;
                $prop = trim($prop);
                if (!in_array($prop, $skipProps) && isset($defaultCss[$prop])) {
                    $result[] = $prop . ':' . $defaultCss[$prop];
                }
            }
        }

        return join(';', $result);
    }

    public static function init($name, $atts, $type = ShortcodesEffects::CONTROL) {
        $id = rand();
        $css = array();
        $className = ($name ? $name : 'additional-class') . '-' . $id;

        foreach (array('css', 'css_md', 'css_sm', 'css_xs') as $key) {
            if (empty($atts[$key])) {
                $css[$key] = '';
            } else {
                $css[$key] = $atts[$key];
            }
        }

        self::$_stack[] = array(
            'id' => $id,
            'css' => $css,
            'className' => $className,
            'selector' => '.' . $className,
            'type' => is_array($type) ? $type['type'] : $type,
            'supportTransform' => is_array($type) ? $type['supportTransform'] : true,
            'atts' => $atts
        );

        return $id;
    }

    private static function _stackItem($id) {
        for ($i = 0, $length = count(self::$_stack); $i < $length; $i++) {
            if (self::$_stack[$i]['id'] === $id) {
                return array(
                    'index' => $i,
                    'info' => self::$_stack[$i]
                );
            }
        }

        return null;
    }

    private static function _stackHasHtmlEffect($item) {
        do {
            $item = self::_stackPrevControl($item);
            if ($item['info']['type'] === ShortcodesEffects::HTML_EFFECT) {
                return true;
            }
        } while ($item && $item['info']['type'] !== ShortcodesEffects::CONTROL);

        return false;
    }

    private static function stack_get_effect_name($item, $name) {
        do {
            $item = self::_stackPrevControl($item);
            if (strpos($item['info']['className'], $name) === 0) {
                return $item;
            }
        } while ($item && $item['info']['type'] !== ShortcodesEffects::CONTROL);

        return false;
    }

    private static function _stackGetBackgroundWidthEffect($item) {
        return self::stack_get_effect_name($item, 'bd-background-width');
    }

    private static function _stackGetPageWidthEffect($item) {
        return self::stack_get_effect_name($item, 'bd-page-width');
    }

    private static function _stackGetAlignContentEffect($item) {
        return self::stack_get_effect_name($item, 'bd-align-content');
    }

    private static function _stackPrevControl($item) {
        return empty(self::$_stack[$item['index'] - 1]) ?
            null :
            array(
                'index' => $item['index'] - 1,
                'info' => self::$_stack[$item['index'] - 1]
            );
    }

    private static function _stackInnerControl($item) {

        $i = $item['index'];

        do {
            $i++;
        } while (
            !empty(self::$_stack[$i]) &&
            self::$_stack[$i]['type'] !== ShortcodesEffects::CONTROL
        );

        return empty(self::$_stack[$i]) ?
            null :
            array(
                'index' => $i,
                'info' => self::$_stack[$i]
            );
    }

    public static function target_control($id) {
        $target = null;

        if ($item = self::_stackItem($id)) {
            $target = self::_stackInnerControl($item);
            $target = $target['info'];
        }

        return $target;
    }

    public static function print_all_css($atts, $prop, $selector) {

        $style = '';

        foreach (self::$responsive_rules as $sfx => $wrap) {
            $key = $prop . $sfx;
            if (empty($atts[$key])) continue;

            $style .= "\n" . $wrap[0] . $selector . '{' . $atts[$key] . '}' . $wrap[1];
        }

        return $style;
    }

    public static function print_all_typography($atts, $prop, $selector, $baseSelector = '') {

        $style = '';

        foreach (self::$responsive_rules as $sfx => $wrap) {
            $key = $prop . $sfx;
            if (empty($atts[$key])) continue;

            $style .= "\n" . $wrap[0] .
                self::_processTypographyCss($atts[$key], $selector, $baseSelector) .
                $wrap[1];
        }

        return $style;
    }

    private static function _controlCss($currentControl, $atts) {
        if (!self::_stackHasHtmlEffect($currentControl))
            return '';

        $groups = array('positioning', 'transform');
        $skipProps = array();
        if (!self::_hasCss($currentControl, 'position', array('absolute', 'fixed'))) {
            $skipProps = array('height', 'width');
        }

        foreach (self::$_skip_attributes as $group => $props) {
            foreach ($props as $prop => $values) {
                if (self::_hasCss($currentControl, $prop, $values)) {
                    $skipProps[] = $prop;
                }
            }
        }

        return self::print_all_css(array(
            ''    => self::_effectCssDefaults($atts['css'], $groups, $skipProps),
            '_md' => self::_effectCssDefaults($atts['css_md'], $groups, $skipProps),
            '_sm' => self::_effectCssDefaults($atts['css_sm'], $groups, $skipProps),
            '_xs' => self::_effectCssDefaults($atts['css_xs'], $groups, $skipProps)
        ), '', $currentControl['info']['selector']);
    }

    private static function _hasCss($control, $prop, $values, $responsive = 'css') {
        return preg_match('/' . $prop .  '\s*:\s*(' . join('|', $values) . ');/', $control['info']['css'][$responsive]);
    }

    private static function _htmlEffectCss($currentControl, $targetControl) {
        $style = '';
        $isAbsoluteControl = self::_hasCss($targetControl, 'position', array('absolute', 'fixed'));

        $groups = array('positioning');
        $skipProps = array();
        if (!$isAbsoluteControl) {
            $skipProps = array('height', 'width');
        }

        if ($currentControl['info']['supportTransform']) {
            $groups[] = 'transform';
        }

        if (self::_stackHasHtmlEffect($currentControl)) {
            // html эффект в html эффекте
            if ($isAbsoluteControl) {
                $style .= self::print_all_css(
                    array('' => 'height: 100%;'),
                    '',
                    $currentControl['info']['selector']
                );
            }
        } else {
            // top html эффект
            $style .= self::print_all_css(array(
                ''    => self::_effectCssCurrent($targetControl['info']['css']['css'], $groups, $skipProps),
                '_md' => self::_effectCssCurrent($targetControl['info']['css']['css_md'], $groups, $skipProps),
                '_sm' => self::_effectCssCurrent($targetControl['info']['css']['css_sm'], $groups, $skipProps),
                '_xs' => self::_effectCssCurrent($targetControl['info']['css']['css_xs'], $groups, $skipProps)
            ), '', $currentControl['info']['selector']);
        }

        return $style;
    }

    private static function _effectCss($currentControl, $atts) {
        $style = '';

        switch ($currentControl['info']['type']) {
            case ShortcodesEffects::CONTROL:
                $style .= self::_controlCss($currentControl, $atts);
                break;
            case ShortcodesEffects::HTML_EFFECT:
                $targetControl = self::_stackInnerControl($currentControl);
                $style .= self::_htmlEffectCss($currentControl, $targetControl);
                break;
        }

        return $style;
    }

    private static function _processTagStyles($atts, $prefix) {

        if (!isset($atts["{$prefix}tags_styles"]))
            return '';

        $classes = array();

        foreach(self::$_tags_styles_types as $name => $type) {

            $style = $atts["{$prefix}tag_{$name}_style"];

            $is_bootstrap = isset($atts["{$prefix}tag_{$name}_type"]) && $atts["{$prefix}tag_{$name}_type"] === 'bootstrap';

            if (!$style && !$is_bootstrap) {
                continue;
            }

            $class_name = ShortcodesStyles::getStyleClassname($type, $style);
            if (!$class_name && !$is_bootstrap) {
                continue;
            }

            switch($name) {
                case 'list':
                    $classes[] = 'bd-custom-bulletlist';
                    break;
                case 'button':
                    if ($is_bootstrap) {
                        $classes[] = 'bd-bootstrap-btn';
                        $classes[] = "bd-$style";
                        if ($size = $atts["{$prefix}tag_button_size"])
                            $classes[] = "bd-$size";
                    }
                    $classes[] = 'bd-custom-button';
                    break;
                case 'table':
                    if ($is_bootstrap) {
                        $classes[] = 'bd-bootstrap-tables';
                        if (ShortcodesUtility::getBool($atts["{$prefix}tag_table_striped"]))
                            $classes[] = 'bd-table-striped';
                        if (ShortcodesUtility::getBool($atts["{$prefix}tag_table_bordered"]))
                            $classes[] = 'bd-table-bordered';
                        if (ShortcodesUtility::getBool($atts["{$prefix}tag_table_hover"]))
                            $classes[] = 'bd-table-hover';
                        if (ShortcodesUtility::getBool($atts["{$prefix}tag_table_condensed"]))
                            $classes[] = 'bd-table-condensed';
                        if (ShortcodesUtility::getBool($atts["{$prefix}tag_table_responsive"]))
                            $classes[] = 'bd-table-responsive';
                    }
                    $classes[] = 'bd-custom-table';
                    break;
                case 'ordered':
                    $classes[] = 'bd-custom-orderedlist';
                    break;
                case 'blockquotes':
                    $classes[] = 'bd-custom-blockquotes';
                    break;
                case 'image':
                    if ($is_bootstrap) {
                        $classes[] = 'bd-bootstrap-img';
                        if ($shape = $atts["{$prefix}tag_image_shape"])
                            $classes[] = "bd-$shape";
                        if (ShortcodesUtility::getBool($atts["{$prefix}tag_image_responsive"]))
                            $classes[] = 'bd-img-responsive';
                    }
                    $classes[] = 'bd-custom-image';
                    break;
                case 'input':
                    $classes[] = 'bd-custom-inputs';
                    break;
            }

            if (!$is_bootstrap) {
                $classes[] = ShortcodesStyles::getMixinClassname($type, $style);
            }
        }
        return join(' ', $classes);
    }

    public static function css($id, $atts, $prefix = '', $selector_pattern = '{selector}', $additional_class = '', $baseSelector = '') {
        $currentControl = self::_stackItem($id);
        $className = $prefix . $currentControl['info']['className'];

        $className = $additional_class ? $additional_class : $className;
        $classNames = array($className);

        $selector_pattern = str_replace('{selector}', '.' . $className, $selector_pattern);
        $baseSelector = str_replace('{selector}', '.' . $className, $baseSelector);

        $style = '';
        $style .= self::print_all_css($atts, $prefix . 'css', $selector_pattern);
        $style .= self::print_all_typography($atts, $prefix . 'typography', $selector_pattern, $baseSelector);

        if (!$prefix) {
            // только топ контролы
            $style .= self::_effectCss($currentControl, $atts);

            if (self::_stackGetBackgroundWidthEffect($currentControl)) {
                $classNames[] = 'bd-background-width';
            }

            if (self::_stackGetPageWidthEffect($currentControl)) {
                $classNames[] = 'bd-page-width';
            }
        }

        $classNames = array_merge($classNames, self::hidden_classes($atts, $prefix));

        $classNames[] = self::_processTagStyles($atts, $prefix);

        if (self::css_prop($atts, 'margin-left', '', $prefix) || self::css_prop($atts, 'margin-right', '', $prefix) || self::css_prop($atts, 'margin', '', $prefix)) {
            $classNames[] = 'bd-no-margins';
        }
        
        return array(
            $style ? "<style>$style</style>" : '',
            ' ' . join(' ', $classNames),
            '.' . $className
        );
    }

    public static function hidden_classes($atts, $prefix = '') {
        $classNames = array();
        if (isset($atts[$prefix . 'hide']) && $atts[$prefix . 'hide']) {
            $hide = explode(',', $atts[$prefix . 'hide']);
            foreach($hide as $hide_type) {
                $classNames[] = (' hidden-' . $hide_type);
            }
        }
        return $classNames;
    }

    private static function _getTypographySelectors() {
        $typography_selectors = <<<EOT
{"TypographyLabel":"label","TypographyLabelTag":"label","TypographyInput":"input","TypographyInputTag":"input","TypographyButton":"button","TypographyButtonTag":"button","TypographySelect":"select","TypographySelectTag":"select","TypographyTextArea":"textarea","TypographyTextAreaTag":"textarea","TypographyQuote":"{selector}","TypographyQuoteTag":"blockquote","TypographyText":"{selector}","TypographyTextTag":"","TypographyTextLinkPassive":"{selector}","TypographyTextLinkPassiveTag":"a","TypographyTextLinkHovered":"{selector}:hover","TypographyTextLinkHoveredTag":"a","TypographyTextLinkVisited":"{selector}:visited","TypographyTextLinkVisitedTag":"a","TypographyTextLinkActive":"{selector}:active","TypographyTextLinkActiveTag":"a","TypographyH1":"{selector}","TypographyH1Tag":"h1","TypographyH1LinkPassive":"{selector} a","TypographyH1LinkPassiveTag":"h1","TypographyH1LinkHovered":"{selector} a:hover","TypographyH1LinkHoveredTag":"h1","TypographyH1LinkVisited":"{selector} a:visited","TypographyH1LinkVisitedTag":"h1","TypographyH1LinkActive":"{selector} a:active","TypographyH1LinkActiveTag":"h1","TypographyH2":"{selector}","TypographyH2Tag":"h2","TypographyH2LinkPassive":"{selector} a","TypographyH2LinkPassiveTag":"h2","TypographyH2LinkHovered":"{selector} a:hover","TypographyH2LinkHoveredTag":"h2","TypographyH2LinkVisited":"{selector} a:visited","TypographyH2LinkVisitedTag":"h2","TypographyH2LinkActive":"{selector} a:active","TypographyH2LinkActiveTag":"h2","TypographyH3":"{selector}","TypographyH3Tag":"h3","TypographyH3LinkPassive":"{selector} a","TypographyH3LinkPassiveTag":"h3","TypographyH3LinkHovered":"{selector} a:hover","TypographyH3LinkHoveredTag":"h3","TypographyH3LinkVisited":"{selector} a:visited","TypographyH3LinkVisitedTag":"h3","TypographyH3LinkActive":"{selector} a:active","TypographyH3LinkActiveTag":"h3","TypographyH4":"{selector}","TypographyH4Tag":"h4","TypographyH4LinkPassive":"{selector} a","TypographyH4LinkPassiveTag":"h4","TypographyH4LinkHovered":"{selector} a:hover","TypographyH4LinkHoveredTag":"h4","TypographyH4LinkVisited":"{selector} a:visited","TypographyH4LinkVisitedTag":"h4","TypographyH4LinkActive":"{selector} a:active","TypographyH4LinkActiveTag":"h4","TypographyH5":"{selector}","TypographyH5Tag":"h5","TypographyH5LinkPassive":"{selector} a","TypographyH5LinkPassiveTag":"h5","TypographyH5LinkHovered":"{selector} a:hover","TypographyH5LinkHoveredTag":"h5","TypographyH5LinkVisited":"{selector} a:visited","TypographyH5LinkVisitedTag":"h5","TypographyH5LinkActive":"{selector} a:active","TypographyH5LinkActiveTag":"h5","TypographyH6":"{selector}","TypographyH6Tag":"h6","TypographyH6LinkPassive":"{selector} a","TypographyH6LinkPassiveTag":"h6","TypographyH6LinkHovered":"{selector} a:hover","TypographyH6LinkHoveredTag":"h6","TypographyH6LinkVisited":"{selector} a:visited","TypographyH6LinkVisitedTag":"h6","TypographyH6LinkActive":"{selector} a:active","TypographyH6LinkActiveTag":"h6","TypographyBulletList":"{selector}","TypographyBulletListTag":"ul > li","TypographyBulletListLinkPassive":"{selector} a","TypographyBulletListLinkPassiveTag":"ul > li","TypographyBulletListLinkHovered":"{selector} a:hover","TypographyBulletListLinkHoveredTag":"ul > li","TypographyBulletListLinkVisited":"{selector} a:visited","TypographyBulletListLinkVisitedTag":"ul > li","TypographyBulletListLinkActive":"{selector} a:active","TypographyBulletListLinkActiveTag":"ul > li","TypographyOrderedList":"{selector}","TypographyOrderedListTag":"ol > li","TypographyOrderedListLinkPassive":"{selector} a","TypographyOrderedListLinkPassiveTag":"ol > li","TypographyOrderedListLinkHovered":"{selector} a:hover","TypographyOrderedListLinkHoveredTag":"ol > li","TypographyOrderedListLinkVisited":"{selector} a:visited","TypographyOrderedListLinkVisitedTag":"ol > li","TypographyOrderedListLinkActive":"{selector} a:active","TypographyOrderedListLinkActiveTag":"ol > li"}
EOT;
        return json_decode($typography_selectors, true);
    }

    private static function _parseRules($css) {
        return array_filter(array_map('trim', explode(';', $css)));
    }

    private static function _stringifyCss($selector, $rules) {
        if (empty($rules))
            return '';
        return $selector . "{\n\t" . implode(";\n\t", $rules) . ";\n}\n";
    }

    private static function _parseTypography($typography) {
        $groups = explode('}', $typography);
        $result = array();
        foreach($groups as $group) {
            $group = explode('{', $group);
            if (count($group) !== 2)
                continue;
            $result[trim($group[0])] = self::_parseRules($group[1]);
        }
        return $result;
    }

    private static function _getTypographyRules($typographyName, $selector, $rules) {
        if (substr($typographyName, -3) === 'Tag') {
            return self::_stringifyCss($selector, $rules);
        }
        $paragraphRules = array();
        $textRules = array();
        $linkRules = array();
        foreach($rules as $rule) {
            if (!preg_match('#([^:]*):(.*)$#', $rule, $matches)) {
                continue;
            }
            list(, $property, $val) = $matches;

            switch ($property) {
                case 'margin-top':
                case 'margin-right':
                case 'margin-bottom':
                case 'margin-left':
                case 'padding-top':
                case 'padding-right':
                case 'padding-bottom':
                case 'padding-left':
                case 'text-indent':
                    if ($typographyName === 'Text')
                        $paragraphRules[] = "$property: $val";
                    else
                        $textRules[] = "$property: $val";
                    break;

                case 'text-shadow':
                    $textRules[] = "-webkit-$property: $val";
                    $textRules[] = "-o-$property: $val";
                    $textRules[] = "-ms-$property: $val";
                    $textRules[] = "-moz-$property: $val";
                    $textRules[] = "$property: $val";
                    break;

                case 'transition':
                    $linkRules[] = "$property: $val";
                    break;

                default:
                    $textRules[] = "$property: $val";
                    break;
            }
        }
        return self::_stringifyCss($selector, $textRules) . self::_stringifyCss("$selector p", $paragraphRules) . self::_stringifyCss("$selector a", $linkRules);
    }

    private static function _processTypographyCss($css, $parentSelector, $baseSelector) {
        $typography = self::_parseTypography($css);

        $result = '';
        foreach($typography as $typographyName => $rules) {
            $selector = self::_getTypographySelector("Typography$typographyName", $parentSelector, $baseSelector);
            $result .= self::_getTypographyRules($typographyName, $selector, $rules);
        }
        return $result;
    }

    private static function _getTypographySelector($typographyName, $parentSelector, $baseSelector) {
        $typographySelectors = self::_getTypographySelectors();
        if (!$baseSelector) {
            $baseSelector = $parentSelector . ' {tag}';
        }
        $selector = $typographySelectors[$typographyName];
        $tag = $typographySelectors[$typographyName . 'Tag'] ? $typographySelectors[$typographyName . 'Tag'] : '';
        $baseSelectorValue = trim(str_replace('{tag}', $tag , $baseSelector));
        $selector = str_replace('{selector}', $baseSelectorValue , $selector);

        $tmp_selectors = explode(',', $selector);
        $selectors = array();
        for ($i = 0; $i < count($tmp_selectors); $i++) {
            $value = $tmp_selectors[$i];
            if ($value === '' && $typographyName === 'TypographyText') {
                $value = 'body';
            }
            $value = trim($value);
            if($value) {
                array_push($selectors, $value);
            }
        }
        return implode (', ', $selectors);
    }


    private static $_icon_styles = array();

    public static function putIconStyles($stylesJson) {
        $styles = (array)json_decode($stylesJson);
        self::$_icon_styles = array_merge(self::$_icon_styles, $styles);
    }

    public static function getIconStyle($icon) {
        return isset(self::$_icon_styles[$icon]) ? self::$_icon_styles[$icon] : '';
    }

    public static function addClassesAndAttrs($content, $target_control, $additional_classes, $additional_attributes) {
        $target_class = $target_control["className"];
        $open_tag_regexp = "/(class=['\"][^'\"]*\\b$target_class\\b)([^'\"]*)(['\"])([^>]*)>/";
        $content = preg_replace_callback(
            $open_tag_regexp,
            function ($matches) use ($additional_classes, $additional_attributes) {
                $existed_classes = $matches[2];
                $existed_classes = preg_split('/\s+/', $existed_classes, -1, PREG_SPLIT_NO_EMPTY);
                $existed_classes = array_merge($existed_classes, $additional_classes);
                $existed_classes = array_unique($existed_classes);
                $existed_classes = ' ' . implode(' ', $existed_classes);

                $existed_attrs = $matches[4];
                foreach($additional_attributes as $name => $val) {
                    if(false !== strpos ($existed_attrs, $name . '="')) {
                        $existed_attrs = str_replace($name . '="', $name . '="' . $val . ',', $existed_attrs);
                    } else {
                        $existed_attrs .= (' ' . $name . '=' . json_encode($val . ''));
                    }
                }
                return $matches[1] . $existed_classes . $matches[3] . $existed_attrs . '>';
            },
            $content
        );

        return $content;
    }
}