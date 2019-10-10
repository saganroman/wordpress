<?php

class MPCE_CFA_Textarea extends MPCE_CFA_ItemBase{

    private $asPlaceholder;
    private $required;
    private $name;

    public function __construct( $atts ){
        $this->classCssDef = 'cfa-textarea';

        $this->label = $atts['title'];
        $this->asPlaceholder = $atts['placeholder'] === 'true';
        $this->required = $atts['required'] === 'true';
        $this->options['id'] = $this->prepareProperty($atts['css_id']);
        $this->options['class'] = $this->prepareProperty($atts['css_class']);

        $name = $this->prepareProperty($atts['name']);

        if( $name === '' ){
            $name = 'cfa-val-textarea-' .  (MPCE_CFA_CAPTCHA::$cfa_numb++);
        }

        $this->name = $name;

    }

    public function render(){
        $html = '';
        $id =( $this->options['id'] !== '' )? $this->options['id'] : $this->name;

//        $html .= '<input type="hidden" name="label-' . $this->name . '" value="' . $this->label . '">';
        if( !$this->asPlaceholder ){
            $html .= '<label for="' . $id . '" class="mpce-cfa-label">' . $this->label . '</label><br>';
        }

        $html .= '<textarea name="' . $this->name . '"';
        $html .= ' id="' . $id .'"';
        $html .= ' class="' . $this->classCssDef;
        $html .= ( $this->required )? ' required' : '';
        $html .= '"';

        if( $this->asPlaceholder ){
                $html .= ' placeholder="'. $this->label .'"';
        }

        if($this->required){
            $html .= ' required="true"';
        }

        $html .= '  rows="6" ></textarea>';

        return $html;
    }


}