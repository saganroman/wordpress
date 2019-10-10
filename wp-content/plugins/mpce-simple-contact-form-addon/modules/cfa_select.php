<?php

class MPCE_CFA_Select extends MPCE_CFA_ItemBase{

    private $list;
    private $name;
    private $firstChecked;
    private $isMultiple;
    private $required;
    private $asPlaceholder;

    public function __construct( $atts, $content = null  ){
        $this->classCssDef = 'cfa-select';
        $this->name = 'cfa-val-select-' . (MPCE_CFA_CAPTCHA::$cfa_numb++);

        $this->label = $atts['title'];
        $this->list = $atts['list'];
        $this->asPlaceholder = $atts['select_first'];
        $this->isMultiple = $atts['select_mult']==='true';
        $this->required = $atts['required'] ==='true';

        $this->options['id'] = $this->prepareProperty($atts['css_id']);
        $this->options['class'] = $this->prepareProperty($atts['css_class']);
        $name = $this->prepareProperty($atts['name']);

        if( $name === '' ){
            $name = 'cfa-val-tel-' .  (MPCE_CFA_CAPTCHA::$cfa_numb++);
        }
        $this->name = $name;
    }

    public function render(){
        $html = '';
        $id =( $this->options['id'] !== '' )? $this->options['id'] : $this->name;
        $this->list = $this->stringToList( $this->list );
        if(!isset($this->list)) return '';


//        $html .= '<input type="hidden" name="label-' . $this->name . '" value="' . $this->label . '">';
        if( !( $this->asPlaceholder === 'label'))
            $html .= '<label for="' . $id . '" class="mpce-cfa-label">' . $this->label . '</label><br />';

        $html .= '<select  name="' . $this->name . '"';

        $html .= ($this->isMultiple) ? ' name="' . $this->name . '[]"' : ' name="' . $this->name . '"';

        $html .= ' id="' . $id .'"';
        $html .= ' class="' . $this->classCssDef;
        $html .= ' class="' . $this->classCssDef;
        $html .= ( $this->required )? ' required' : '';
        $html .=  '"';
        if($this->isMultiple){
            $html .=  ' multiple="multiple"';
        }
        if($this->required){
            $html .= ' required="true"';
        }
        $html .= '>';

        if( !$this->isMultiple || ($this->isMultiple && ($this->asPlaceholder === 'label') ) ) {
            $html .= '<option disabled selected="selected" value="">';
            $html .= ($this->asPlaceholder === 'label') ? $this->label : "&nbsp;";
            $html .= '</option>';
        }

        foreach($this->list as $key=>$val){
            $html .= '<option';
            $val = htmlspecialchars(stripslashes(trim($val)));
            if($key === 0 && $this->firstChecked ){
                $html .= ' selected="selected"';
            }
            $html .= ' value="' . $val . '">' . $val;
            $html .= '</option>';
        }
        $html .= '</select>';

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