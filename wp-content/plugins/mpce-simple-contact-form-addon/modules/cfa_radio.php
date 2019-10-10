<?php

class MPCE_CFA_Radio extends MPCE_CFA_ItemBase{

    private $list;
    private $name;
    private $required;
    private $checkFirst;

    public function __construct( $atts, $content = null ){
        $this->classCssDef = 'cfa-radio';
        $this->classCssItemDef = 'cfa-radio-item';

        $this->label = $atts['title'];
        $this->list = $atts['list'];
        $this->required = $atts['required'] ==='true';
        $this->checkFirst = $atts['check'] ==='true';
        $this->options['id'] = $this->prepareProperty($atts['css_id']);
        $this->options['class'] =$this->prepareProperty($atts['css_class']);
        $name = $this->prepareProperty($atts['name']);

        if( $name === '' ){
            $name = 'cfa-val-radio-' .  (MPCE_CFA_CAPTCHA::$cfa_numb++);
        }
        $this->name = $name;
    }

    public function render(){
        $html = '';
        $id =( $this->options['id'] !== '' )? $this->options['id'] : $this->name;
        $this->list = $this->stringToList( $this->list );
        if(!isset($this->list)) return '';

//        $html .= '<input type="hidden" name="label-' . $this->name . '" value="' . $this->label . '">';
        $html .= (trim($this->label) !== "")? '<label class="mpce-cfa-label">' . $this->label . '</label><br />' : '';

        $html .= '<span';
        $html .= ' id="' . $id .'"';
        $html .= ' class="' . $this->classCssDef;
        if( $this->options['class'] ){
            $html .=  ' ' . $this->options['class'];
        }
        $html .= '">';

        foreach($this->list as $key=>$val){
            $value = htmlspecialchars(stripslashes(trim($val)));

            $html .= '<span';
            $html .= ' class="' . $this->classCssItemDef . '">';
            $html .= ($key != 0)? '<br />': '';
            $html .= '<input type="radio" name="' . $this->name . '"';
            $html .= ' id="' . $id .$key . '"';
            if($key === 0 && $this->checkFirst){
                $html .= ' checked="checked"';
            }
            $html .= ( $this->required )? ' class="required"' : '';
            if( $this->required ){
                $html .= ' required="true"';
            }
            $html .= ' value="' . $value . '">';

            $html .= '<label for="' . $id .$key . '">&nbsp;' . $val . '</label>';
            $html .= '</span>';
        }
        $html .= '</span>';


        return $html;
    }

    public function stringToList($str){
        $str = base64_decode(strip_tags($str));
        $temp = preg_split('~[\r\n]~', $str);
        $list = array();

        foreach($temp as $value){
            $value = trim($value);
            if($value)
                $list[] = $value;
        }

        return $list ;
    }

}