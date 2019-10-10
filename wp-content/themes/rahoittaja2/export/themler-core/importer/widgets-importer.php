<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ThemlerWidgetsImporter {

    /**
     * @var ThemlerContentImporter
     */
    private $_contentImporter;

    /**
     * @var array
     * Common format => WP format mapping
     */
    private $_typeMap = array(
        'text'       => 'text',
        'search'     => 'search',
        'archives'    => 'archives',
        'vmenu'      => 'vmenuwidget',
        'login'      => 'loginwidget',
        'categories' => 'categories',
    );

    /**
     * @var array
     * Common format => WP format mapping
     */
    private $_sidebarsMap = array(
        'sidebar1'       => 'primary-widget-area',
        'sidebar2'       => 'secondary-widget-area',
        'content-before' => 'first-top-widget-area',
        'content-after'  => 'first-bottom-widget-area',
        'inactive'       => 'wp_inactive_widgets'
    );

    public function __construct($content_importer) {
        $this->_contentImporter = $content_importer;
    }

    /**
     * Split 'text-12' into array('text', '12')
     * array(false, false) in case of invalid $widget_id
     *
     * @param $widget_id
     * @return array
     */
    private function _splitTypeId($widget_id) {
        $type = false;
        $id = false;
        if (preg_match('/^(.*[^-])-([0-9]+)$/', $widget_id, $matches) && isset($matches[1]) && isset($matches[2])) {
            $type = $matches[1];
            $id = $matches[2];
        }
        return array($type, $id);
    }

    /**
     * @param string $sidebar - sidebar name
     * @param string $type
     * - text
     * - search
     * - archive
     * - vmenu
     * - login
     * - categories
     * @return string - new widget identifier
     */
    public function addWidget($sidebar, $type) {
        $sidebar = _at($this->_sidebarsMap, $sidebar, $sidebar);

        // gets the list of current sidebars and widgets from blog options
        $wp_sidebars = get_option('sidebars_widgets');

        if (!isset($wp_sidebars[$sidebar])) {
            $wp_sidebars[$sidebar] = array();
        }

        if (!isset($this->_typeMap[$type])) {
            $type = 'text';
        }
        $type = $this->_typeMap[$type];

        // gets the widget data
        $wp_widget = get_option('widget_' . $type);
        $wp_widget = $wp_widget ? $wp_widget : array();

        // new widget id is always unique
        $new_widget_id = 0;
        foreach ($wp_widget as $widget_id => $widget) {
            if (is_int($widget_id))
                $new_widget_id = max($new_widget_id, $widget_id);
        }
        $new_widget_id++;
        $new_widget_name = $type . '-' . $new_widget_id;

        // gets widgets from the selected sidebar
        $wp_sidebar_widgets = $wp_sidebars[$sidebar];

        $wp_sidebar_widgets[] = $new_widget_name;

        // puts new sidebar widgets in the list of sidebars
        $wp_sidebars[$sidebar] = $wp_sidebar_widgets;

        update_option('sidebars_widgets', $wp_sidebars);

        // creates new widget
        $wp_widget[$new_widget_id] = array();

        // default Artisteer widgets
        if ($type == 'text') {
            $wp_widget[$new_widget_id]['text'] = '';
            $wp_widget[$new_widget_id]['filter'] = false;
        }
        if ($type == 'vmenuwidget') {
            $wp_widget[$new_widget_id]['source'] = 'Pages';
            $wp_widget[$new_widget_id]['nav_menu'] = 0;
        }
        if ($type == 'archives') {
            $wp_widget[$new_widget_id]['count'] = 0;
            $wp_widget[$new_widget_id]['dropdown'] = 0;
        }
        if ($type == 'categories') {
            $wp_widget[$new_widget_id]['count'] = '0';
            $wp_widget[$new_widget_id]['dropdown'] = '0';
            $wp_widget[$new_widget_id]['hierarchical'] = '0';
        }

        $wp_widget[$new_widget_id]['title'] = '';

        if (!isset($wp_widget['_multiwidget'])) {
            $wp_widget['_multiwidget'] = 1;
        }

        update_option('widget_' . $type, $wp_widget);
        return $new_widget_name;
    }

    public function updateWidget($widget_id, $title, $content = null, $args = null) {

        list($type, $id) = $this->_splitTypeId($widget_id);
        if (!$type) {
            return false;
        }

        $wp_widget = get_option('widget_' . $type);

        if (!$wp_widget || !isset($wp_widget[$id])) {
            return false;
        }

        if (!empty($title)) {
            $wp_widget[$id]['title'] = $title;
        }

        if (!empty($content) && $type == 'text') {
            $wp_widget[$id]['text'] = $content;
        }

        if (is_array($args)) {
            $wp_widget[$id] = array_merge($wp_widget[$id], $args);
        }

        if (!isset($wp_widget['_multiwidget'])) {
            $wp_widget['_multiwidget'] = 1;
        }

        update_option('widget_' . $type, $wp_widget);
        return true;
    }

    public function deleteWidget($widget_id, $force_delete = false) {
        $widget_exist = false;
        $wp_sidebars = get_option('sidebars_widgets');
        foreach ($wp_sidebars as $sidebar_id => $widgets) {
            if (is_array($widgets)) {
                $new_widgets = array();
                foreach ($widgets as $widget) {
                    if ($widget != $widget_id) {
                        $new_widgets[] = $widget;
                        $widget_exist = true;
                    }
                }
                $wp_sidebars[$sidebar_id] = $new_widgets;
            }
        }
        if (!$force_delete && $widget_exist) {
            if (!is_array($wp_sidebars['wp_inactive_widgets'])) {
                $wp_sidebars['wp_inactive_widgets'] = array();
            }
            $wp_sidebars['wp_inactive_widgets'][] = $widget_id;
        }
        update_option('sidebars_widgets', $wp_sidebars);

        if ($force_delete && $widget_exist) {
            list($type, $id) = $this->_splitTypeId($widget_id);
            if (!$type) {
                return false;
            }

            $wp_widget = get_option('widget_' . $type);
            if (!$wp_widget || !isset($wp_widget[$id])) {
                return false;
            }
            unset($wp_widget[$id]);
            if (!isset($wp_widget['_multiwidget'])) {
                $wp_widget['_multiwidget'] = 1;
            }
            update_option('widget_' . $type, $wp_widget);
        }
        return true;
    }

    public function deactivateAllWidgets() {
        $wp_sidebars = get_option('sidebars_widgets');
        if (!is_array($wp_sidebars['wp_inactive_widgets'])) {
            $wp_sidebars['wp_inactive_widgets'] = array();
        }
        foreach ($wp_sidebars as $sidebar_id => $widgets) {
            if ('wp_inactive_widgets' != $sidebar_id && is_array($widgets)) {
                $wp_sidebars['wp_inactive_widgets'] = array_merge($wp_sidebars['wp_inactive_widgets'], $widgets);
                $wp_sidebars[$sidebar_id] = array();
            }
        }
        update_option('sidebars_widgets', $wp_sidebars);
    }

    /**
     * @param array $sidebars
     * @param array $widgets
     * @return array - list of added widget ids
     */
    public function importSidebars($sidebars, $widgets) {
        $added_widgets = array();

        foreach($sidebars as $sidebar) {
            $sidebar_widgets = _at($sidebar, 'widgets');
            $sidebar_name = _at($sidebar, 'name');

            if (empty($sidebar_widgets) || empty($sidebar_name))
                continue;

            $widgets_placeholders = explode(',', $sidebar_widgets);
            foreach($widgets_placeholders as $placeholder) {
                list(, $id) = $this->_contentImporter->parsePlaceholder($placeholder);
                if (!$id || empty($widgets[$id])) {
                    continue;
                }
                $widget = $widgets[$id];

                if (isset($widget['content'])) {
                    $widget['content'] = $this->_contentImporter->_processContent($widget['content']);
                }

                $widget_type = _at($widget, 'type');

                $widget_id = $this->addWidget($sidebar_name, $widget_type);

                $args = null;
                if ($widget_type === 'vmenu') {
                    $args = array(
                        'source' => 'Custom Menu',
                        'nav_menu' => _at($this->_contentImporter->vmenus, $id, ''),
                    );
                }
                $this->updateWidget($widget_id, _at($widget, 'title', ''), _at($widget, 'content', ''), $args);

                if (!empty($widget['pageHead'])) {
                    list($type, $id) = $this->_splitTypeId($widget_id);

                    $wp_widget = get_option('widget_' . $type);
                    if ($wp_widget && isset($wp_widget[$id])) {
                        $wp_widget[$id]['theme_widget_styling'] = $this->_contentImporter->_processContent($widget['pageHead']);
                        update_option('widget_' . $type, $wp_widget);
                    }
                }
                $added_widgets[] = $widget_id;
            }
        }
        return $added_widgets;
    }
}