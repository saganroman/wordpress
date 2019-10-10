<?php

class MPCE_CFA_Submit extends MPCE_CFA_ItemBase{

    private $position;
    private $style;

    public function __construct( $atts ){
        $this->classCssDef = 'cfa-submit';
        $this->name = 'cfa-submit';
        $this->label = $atts['submit'];
    }

    public function render(){
        $html = '';

        $html .= '<input type="submit" name="' . $this->name . '"';
        if( $this->options['id'] ){
            $html .= ' id="' . $this->options['id'] .'"';
        }
        $html .= ' class="' . $this->classCssDef . ' form-submit"';
        if( $this->label ){
            $html .= ' value="' . $this->label . '"';
        }
        if( $this->style ){
            $html .= ' style="' . $this->style . '"';
        }
        $html .= ' />';
        $html .= '<img src="' . MPCE_CFA_PLUGIN_DIR_URL . '/assets/images/loader.gif'.'"  class="mpce-cfa-loader">';

        return $html;
    }


    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }


    /**
     * @param mixed $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
    }


}