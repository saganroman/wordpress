<?php

class MPCE_CFA_Label extends MPCE_CFA_ItemBase{

    public function __construct( $atts ){
        $this->classCssDef = 'mpce-cfa-label';

        $this->label = $atts['title'];
        $this->options['id'] = preg_replace( '/[^A-Za-z0-9_-]/', '', $atts['css_id']);
        $this->options['class'] = preg_replace( '/[^A-Za-z0-9_-]/', '', $atts['css_class']);
    }

    public function render(){
        $html = '';

        $html .= '<label';
        if( $this->options['id'] ){
            $html .= ' id="' . $this->options['id'] .'"';
        }
        $html .= ' class="' . $this->classCssDef;
        $html .= '"';
        $html .= '>';
        $html .= $this->label;
        $html .= '</label>';

        return $html;
    }

}