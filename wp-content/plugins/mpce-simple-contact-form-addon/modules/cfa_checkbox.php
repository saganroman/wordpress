<?php

class MPCE_CFA_Check extends MPCE_CFA_ItemBase{

    private $list;
    private $required;
    private $name;
    private $checkFirst;

    public function __construct( $atts){
        $this->classCssDef = 'cfa-checkbox';

        $this->label = $atts['title'];
        $this->list =  $atts['list'];
        $this->required = $atts['required'] ==='true';
        $this->checkFirst = $atts['check'] ==='true';
        $this->options['id'] = $this->prepareProperty($atts['css_id']);
        $this->options['class'] = $this->prepareProperty($atts['css_class']);
        $name = $this->prepareProperty($atts['name']);

        if( $name === '' ){
            $name = 'cfa-val-checkbox-' . (MPCE_CFA_CAPTCHA::$cfa_numb++);
        }

        $this->name = $name;
    }

    public function render(){
        $this->list = $this->stringToList( $this->list );
        if(!isset($this->list)) return '';
        $html = '';

        $id =( $this->options['id'] !== '' )? $this->options['id'] : $this->name;

//        $html .= '<input type="hidden" name="label-' . $this->name . '" value="' .  $this->label . '">';
        $html .= (trim($this->label) !== "")? '<label class="mpce-cfa-label">' . $this->label . '</label><br />' : '';

        foreach($this->list as $key=>$val){
            $value = htmlspecialchars(stripslashes(trim($val)));

            $html .= ($key != 0)? '<br />': '';
            $html .= '<input type="checkbox" name="' . $this->name . '[]" value="' . $value . '" ';
            $html .= ' id="' . $id . $key .'"';
            if( $this->required ){
                $html .= ' required="true"';
            }

            $html .= ( $this->required )? ' class="required"' : '';

            if($key === 0 && $this->checkFirst){
                $html .= ' checked="checked"';
            }
            $html .= '>';
            $html .= '<label for="' . $id . $key .'">&nbsp;' . $val . '</label>';
        }

        return $html;
    }



    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param mixed $list
     */
    public function setList($list)
    {
        $this->list = $list;
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