<?php

class MPCE_CFA_Mailer{
    private $mailPrepared;

    private $mail;
    private $subject;

    private $attachments;
    private $errors;
    private $from;
    private $to;

    public function __construct( $from, $to, $subj ){
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subj;
    }

    public function prepareMail( $post ){
        $this->errors = array();
        $response = true;

        if( array_key_exists ( 'g-recaptcha-response', $post ) ) {
            $response = $this->responseReCAPTCHA($post['g-recaptcha-response']);
        }

         if ($response === true) {

            unset($post['g-recaptcha-response']);
            unset($post['cfa-submit']);
            unset($post['cfa_name']);
            unset($post['action']);
            unset($post['security']);

             $templates = get_option('mpce_cfa_template', array());
            if( isset($templates[ $post['cfa_id'] ]) ){
                $template =  trim($templates[ $post['cfa_id'] ]);
            } else {
                $template = false;
            }

             if( $template ){
                 $mail = $this->generateByTemplate( $post, $template);
             }  else {
                 unset($post['cfa_id']);
                 $mail = $this->generateByDefault($post);
             }

            if ( count($this->errors) === 0 ){
                $this->mailPrepared = true;
                $this->mail = $mail;

                return true;
            }
        }

        return false;
    }

    public function sendMailWithAttach( $path='', $filename=''){


        if($path === ''){
            $headers = '';
            $headers .= "From: " . $this->from . "" . PHP_EOL;
            $headers .= "Reply-To: " . $this->from . "" . PHP_EOL;
            $headers .= "Return-Path: " . $this->from . "" . PHP_EOL;
            $headers .= "Content-Type: text/html; charset=UTF-8" . PHP_EOL;

            //Send the email
            add_filter('wp_mail_from', array(&$this, 'set_message_from') );
            add_filter('wp_mail_content_type', array(&$this, 'set_html_content_type') );
            $sended = wp_mail( $this->to, $this->subject, $this->mail, $headers);
            remove_filter( 'wp_mail_content_type', array(&$this, 'set_message_from') );
            remove_filter( 'wp_mail_from', array(&$this, 'set_html_content_type') );

            if ( $sended ) {
                return true;
            }

            $this->errors = __( 'Function wp_mail returned false.', 'mpce-cfa' );
        }

        return false;
    }

    public function sendMail(){
        if( !$this->mailPrepared) return false;

        if( count( $this->attachments ) > 0 ){
            return $this->sendMailWithAttach($this->attachments);
        }

        return $this->sendMailWithAttach();
    }

    public function generateByTemplate( $post, $template){
        $mail = $template;
        foreach( $post as $key => $value ){
            $replace = array();
            if(is_array($value)){
                foreach($value as $numb => $val){
                    $val =  $this->protectString($val);
                    $replace [$numb] = $val;
                }
                $replace = implode(',', $replace);
            } else{
                $replace = $this->protectString($value);
            }
            $mail = preg_replace( '/\[' . $key . '\]/', $replace, $mail);
        }

        return $mail;
    }

    public function generateByDefault( $post ){
        $mail = "";
        foreach($post as $key=>$val){
            $mail .= "<p>";
            $replace = array();

            if(is_array($val)){
                foreach($val as $numb => $value){
                    $replace[$numb] = $this->protectString($value);
                }
                $replace = implode(',', $replace);
            } else{
                $replace =  $this->protectString($val);
            }
            $mail .= '<b>' . $this->protectString($key) . '</b>' . '<br />';
            $mail .= $replace;

            $mail .= "</p>";
        }

        return $mail;
    }

/*
 * return true if reCAPTCHA submit 'not robot'
 * */
    private function responseReCAPTCHA( $recaptcha ){
        $captcha = '';
        $settings = get_option('mpce-cfa-settings', array());

        if (isset($recaptcha)) {
            $captcha = $recaptcha;
        }
        if (!$captcha) {
            $this->errors[] = __( 'Please check the reCAPTCHA.', 'mpce-cfa' );
            return false;
        }

        $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $settings['recaptch_secret_key']
            . "&response=" . $captcha
            . "&remoteip=" . $_SERVER['REMOTE_ADDR'];

        $args = array(
            'timeout'     => 15,
            'sslverify'   => false,
        );
        $response = wp_remote_get( $url, $args );

        try {
            $json = json_decode( $response['body'] );
        } catch ( Exception $ex ) {
            $json = null;
        }

        $response = $json->success;

        if ($response !== true) {
            $this->errors[] = __( 'ReCAPTCHA Error', 'mpce-cfa' );
        }

        return $response;
    }

    private function protectString($value){
        return htmlspecialchars(stripslashes(trim($value)));
    }

    /**
     * @return errors rised during prepareing mail
     */
    public function getErrors(){
        return implode(",",  (array)$this->errors);
    }

    public function set_html_content_type(){
        return "text/html";
    }

    public function set_message_from(){
        return $this->from;
    }

}


function mpce_cfa_contact_ajax(){
    ob_start();
    $json = array('errors' => array(), 'success' => '');

    if (empty($_POST) || !wp_verify_nonce(  $_POST['security'],'mpce-cfa-special-string') ){
        $json['success'] = false;
        $json['errors'] = array( __( 'Security error!', 'mpce-cfa' ) );

       ob_clean();
        wp_send_json($json);
        die();
    }

    $settings = get_option('mpce-cfa-settings', array());
    $replacements = array(
        'blogname' => array(
            'search' => '/\[blog-name\]/',
            'replace' => get_bloginfo( 'name', 'display' )
        ),
        'formname' => array(
            'search' => '/\[form-name\]/',
            'replace' => $_POST['cfa_name']
        ),
    );

    $from = trim($settings['mpce_cfa_mail_sender']);
    $to = trim($settings['mpce_cfa_mail_recipient']);
    $subj = trim($settings['mpce_cfa_mail_subject']);

    foreach( $replacements as $key => $value ){
        $subj = preg_replace( $value['search'], $value['replace'], $subj);
    }

    $to = ($to === '') ? get_option( 'admin_email' ) : $to;
    $from = ($from === '') ? get_option( 'admin_email' ) : $from;

    $mailer = new MPCE_CFA_Mailer( $from, $to, $subj );
    $mailer->prepareMail( $_POST );

    if(!$mailer->getErrors()) {
        $send = $mailer->sendMail();
    }

    if( $send ){
        $json['success'] = true;
    } else {
        $json['success'] = false;
        $json['errors'] = $mailer->getErrors();
    }

    ob_clean();
    wp_send_json($json);
}

add_action( 'wp_ajax_mpce_cfa_contact_ajax', 'mpce_cfa_contact_ajax' );
add_action( 'wp_ajax_nopriv_mpce_cfa_contact_ajax', 'mpce_cfa_contact_ajax' );
