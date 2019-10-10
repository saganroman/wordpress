<?php
if (!defined('ABSPATH')) exit;

$mpceCFAShortcodeFunctions = array(
			'mpce_cfa_contact_form' => 'mpceCFABoxShortcode',
			'mpce_cfa_item' => 'mpceCFAItemShortcode'
	);

foreach ($mpceCFAShortcodeFunctions as $sortcode_name => $function_name) {
	add_shortcode($sortcode_name, $function_name);
}

function mpceCFABoxShortcode($attrs, $content = null) {
	$settings = get_option('mpce-cfa-settings', array());
	global $mpce_cfa_modules;
	$output = '';
	$mpceClasses = '';
	$tag = (isContentEditor()) ? 'div' : 'form';
	$defaultAttrs = array(
		'submit' => '',
		'name' => '',
		'captcha' => '',
		'position' => '',
		'form' => '',
	);
	$mpceActive = is_plugin_active('motopress-content-editor/motopress-content-editor.php') || is_plugin_active('motopress-content-editor-lite/motopress-content-editor.php');

	//enqueue scripts
	wp_enqueue_style('mpce-cfa-style');
	wp_enqueue_script( 'mpce-cfa-modernizr');
	
	wp_enqueue_script( 'mpce-cfa-script-ajax');
	wp_enqueue_script('mpce-cfa-recaptcha');


	if ($mpceActive) $defaultAttrs = MPCEShortcode::addStyleAtts($defaultAttrs);
	$shortcode_atts = shortcode_atts($defaultAttrs, $attrs);
	extract($shortcode_atts);


	if ($mpceActive) {
		if (!empty($mp_style_classes)) $mp_style_classes = ' ' . $mp_style_classes;
		$mpceClasses .= ' ' . MPCEShortcode::getBasicClasses('mpce_cfa_contact_form') . MPCEShortcode::getMarginClasses($margin) . $mp_style_classes;
	}

	if( $position == 'left' || $position == 'right' ){
		$mpceClasses .= ' mpce-cfa-' . $position;
	}

	$formID = ( $form )? ' id="' . $form . '"' : '';

	$output .= '<' . $tag . ' role="form"' . $formID . ' class="mpce-cfa-form' . $mpceClasses . '" >';
    $output .= do_shortcode($content);

	if($captcha === 'true' && isset($settings['recaptch_site_key']) && isset($settings['recaptch_secret_key']) &&
		!( $settings['recaptch_site_key'] === '' || $settings['recaptch_secret_key'] === '' )){
		$item = new MPCE_CFA_CAPTCHA();
		$output .= $item->render();
	}

	// Submit button
	$item = new MPCE_CFA_Submit($shortcode_atts );
	$output .= '<div class="mpce-cfa-message"></div>'
		. '<input type="hidden" name="cfa_name" value="' . $name .'">'
		. '<input type="hidden" name="cfa_id" value="' . $form .'">'
		. '<p class="mpce-cfa-form-group ' . 'mpce-cfa-form-' . $mpce_cfa_modules['submit'] .  '">'
		. $item->render()
		. '</p>'
		. '</' . $tag . '>';

	return $output;
}

function mpceCFAItemShortcode($attrs, $content = null) {
	global $mpce_cfa_modules;
	$defaultAttrs = array(
		'title' => '',
		'item_type' => '',
		'required' => '',
		'placeholder' => '',
		'check' => '',
		'captch_pos' => '',
		'list' => '',
		'select_first' => '',
		'select_mult' => '',
		'css_class' => '',
		'css_id' => '',
		'name' => '',
	);

	$shortcode_atts = shortcode_atts($defaultAttrs, $attrs);
	extract($shortcode_atts);

	$item= null;

	switch ($item_type) {
		case $mpce_cfa_modules['text']:
			$item = new MPCE_CFA_Text($shortcode_atts);
			break;
		case $mpce_cfa_modules['textarea']:
			$item = new MPCE_CFA_Textarea($shortcode_atts);
			break;
		case  $mpce_cfa_modules['email']:
			$item = new MPCE_CFA_EMail($shortcode_atts);
			break;
		case  $mpce_cfa_modules['number']:
			$item = new MPCE_CFA_Number($shortcode_atts);
			break;
		case  $mpce_cfa_modules['tel']:
			$item = new MPCE_CFA_Tel($shortcode_atts);
			break;
		case  $mpce_cfa_modules['checkbox']:
			$item = new MPCE_CFA_Check($shortcode_atts, $content);
			break;
		case  $mpce_cfa_modules['radio']:
			$item = new MPCE_CFA_Radio($shortcode_atts, $content);
			break;
		case  $mpce_cfa_modules['select']:
			$item = new MPCE_CFA_Select($shortcode_atts, $content);
			break;
		case  $mpce_cfa_modules['label']:
			$item = new MPCE_CFA_Label($shortcode_atts);
			break;
	}

	$output = '';
	if( $item ){
		$output .= '<p class="mpce-cfa-form-group ' . 'mpce-cfa-form-' . $item_type;
		$output .= ( $css_class ) ? ' ' . $css_class : '';
		$output .= '">';
		$output .= $item->render();
		$output .= '</p>';
	}

	return $output;
}