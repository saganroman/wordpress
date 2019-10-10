<?php

class MPCE_CFA_CAPTCHA{

    public static $cfa_numb = 0;

    public function __construct(){
        $this->classCssDef = 'cfa-captcha';
        self::$cfa_numb = uniqid();
    }

    public function render(){
        $html = '';

        $html .= ( isContentEditor() )? '<form>' : '';

        $html .= '<div';
        $html .= ' id="cfa-recaptcha' . self::$cfa_numb .'"';
        self::$cfa_numb++;

        $html .= ' class="g-recaptcha ' . $this->classCssDef . '"';
        $html .= '></div><div class="cfa-captcha-error">'
            . __( 'Please verify that you are not a robot.', 'mpce-cfa' ) . '</div>';

        $html .= ( isContentEditor() )? '</form>' : '';

        return $html;
    }

}