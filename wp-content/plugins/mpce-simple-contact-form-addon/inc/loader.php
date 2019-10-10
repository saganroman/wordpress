<?php
class MPCE_CFA_RequireModuls {

    public static $modules = array(
        'textarea' => 'textarea',
        'text' => 'text',
        'tel' => 'tel',
        'email' => 'email',
        'submit' => 'submit',
        'number' => 'number',
        'checkbox' => 'checkbox',
        'radio' => 'radio',
        'captcha' => 'captcha',
        'select' => 'select',
        'label' => 'label',
    );

    public
    static function load_modules(){

        require_once MPCE_CFA_PLUGIN_DIR . 'modules/MPCE_CFA_ItemBase.php';

        foreach(  self::$modules as $module ){
            self::load_module('cfa_' . $module);
        }

    }

    protected
    static function load_module($mod)
    {
        $dir = MPCE_CFA_PLUGIN_DIR . 'modules/';
        if (empty($dir) || !is_dir($dir)) {
            return false;
        }
        $file = path_join($dir, $mod . '.php');
        if (file_exists($file)) {
            require_once $file;
        }
    }
}


MPCE_CFA_RequireModuls::load_modules();
$mpce_cfa_modules = MPCE_CFA_RequireModuls::$modules;