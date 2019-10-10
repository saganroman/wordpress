<?php

/**
 * Created by PhpStorm.
 * User: motointern
 * Date: 12/30/2015
 * Time: 10:32 AM
 */


class MPCE_CFA_ItemBase{

    protected $display;
    protected $label;
    protected $descrip; // Description of the value in the letter

    public static $cfa_numb = 1;

    protected $classCssDef;
    protected $options = array(
        'id' => '',
        'class' => '',
        'name' => ''
    );

    public function __construct(){}

    // Function for filtering input values.
    function stringPrepare( $data ){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        return $data;
    }

    public function render(){
        return '';
    }

    public function prepareProperty( $str ){
        return trim(preg_replace( '/[^A-Za-z0-9_-]/', '', $str));
    }

    public function getLabel(){
        return $this->label;
    }
    public function setLabel($label){
        $this->label = $label;
    }
    public function getClassCssDef(){
        return $this->classCssDef;
    }
    public function setClassCssDef($classCssDef){
        $this->classCssDef = $classCssDef;
    }
    public function getOptions(){
        return $this->options;
    }
    public function setOptions($options){
        $this->options = $options;
    }
    public function getDisplay(){
        return $this->display;
    }
    public function setDisplay($display){
        $this->display = $display;
    }
}