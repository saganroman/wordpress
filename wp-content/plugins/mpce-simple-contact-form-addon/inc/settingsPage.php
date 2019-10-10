<?php
if (!defined('ABSPATH')) exit;

function mpceCFASettingsTab($tabs) {
	$tabs[MPCE_CFA_PLUGIN_SHORT_NAME] = array(
		'label' => __('Simple Contact Form', 'mpce-cfa'),
		'priority' => 20,
		'callback' => 'mpceCFASettingsTabContent'
	);
	return $tabs;
}

function mpceCFASettingsTabContent() {
	if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        add_settings_error(
            'mpceCFASettings',
            esc_attr('settings_updated'),
            __('Settings saved.', 'mpce-cfa'),
            'updated'
        );
    }
	settings_errors('mpceCFASettings', false);
	echo '<form actoin="options.php" method="POST">';
//    settings_fields('mpceCFAOptionsFields');
	do_settings_sections('mpce_cfa_options');
	echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="' . __('Save', 'mpce-cfa') . '" /></p>';
	echo '</form>';
}
function mpceCFALicenseTab($tabs) {
    global $cfaLicense;
	$tabs[MPCE_CFA_PLUGIN_SHORT_NAME] = array(
		'label' => __('Simple Contact Form  Addon', 'mpce-cfa'),
		'priority' => 10,
        'callback' => array(&$cfaLicense, 'renderPage')
	);
	return $tabs;
}

/* Google reCAPTCHA */


function mpceCFASettingsSave() {
	if (!empty($_POST)) {
		$settings = get_option('mpce-cfa-settings', array());

		if (isset($_POST['mpce_cfa_mail_sender'])) {
			$settings['mpce_cfa_mail_sender'] = trim($_POST['mpce_cfa_mail_sender']);
		}
		if (isset($_POST['mpce_cfa_mail_recipient'])) {
			$settings['mpce_cfa_mail_recipient'] = trim($_POST['mpce_cfa_mail_recipient']);
		}
		if (isset($_POST['mpce_cfa_mail_subject'])) {
			$settings['mpce_cfa_mail_subject'] = trim($_POST['mpce_cfa_mail_subject']);
		}
		if (isset($_POST['mpce_cfa_mail_success'])) {
			$settings['mpce_cfa_mail_success'] = trim(wp_unslash($_POST['mpce_cfa_mail_success']));
		}
		if (isset($_POST['mpce_cfa_mail_fail'])) {
			$settings['mpce_cfa_mail_fail'] = trim(wp_unslash($_POST['mpce_cfa_mail_fail']));
		}

		if (isset($_POST['recaptch_site_key'])) {
			$settings['recaptch_site_key'] = $_POST['recaptch_site_key'];
		}
		if (isset($_POST['recaptch_secret_key'])) {
			$settings['recaptch_secret_key'] = $_POST['recaptch_secret_key'];
		}
		if (isset($_POST['recaptch_lang'])) {
			$settings['recaptch_lang'] = $_POST['recaptch_lang'];
		}

		if (isset($_POST['mpce_cfa_dir_writable'])) {

			$templates = isset($_POST['mpce_cfa_template']) ? $_POST['mpce_cfa_template'] : array();
			saveCFAForms($templates);
		}

		update_option('mpce-cfa-settings', $settings);
		wp_redirect(add_query_arg(array('page' => $_GET['page'], 'plugin' => $_GET['plugin'], 'settings-updated' => 'true'), admin_url('admin.php')));
	}
}
add_action('admin_init', 'mpceCFAInitOptions');




function mpceCFAInitOptions() {
	register_setting('mpceCFAMailRecipientOptionsFields', 'mpceCFAMailRecipientOptions');
	add_settings_section('mpceCFAMailRecipientOptionsFields', '', 'mpceCFAMailRecipientOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFAMailRecipientOptions', __('To', 'mpce-cfa'), 'mpceCFAMailRecipientSettings', 'mpce_cfa_options', 'mpceCFAMailRecipientOptionsFields');

	register_setting('mpceCFAMailSenderOptionsFields', 'mpceCFAMailSenderOptions');
	add_settings_section('mpceCFAMailSenderOptionsFields', '', 'mpceCFAMailSenderOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFAMailSenderOptions', __('From', 'mpce-cfa'), 'mpceCFAMailSenderSettings', 'mpce_cfa_options', 'mpceCFAMailSenderOptionsFields');

	register_setting('mpceCFAMailSubjectOptionsFields', 'mpceCFAMailSubjectOptions');
	add_settings_section('mpceCFAMailSubjectOptionsFields', '', 'mpceCFAMailSubjectOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFAMailSubjectOptions', __('Subject', 'mpce-cfa'), 'mpceCFAMailSubjectSettings', 'mpce_cfa_options', 'mpceCFAMailSubjectOptionsFields');

	register_setting('mpceCFAMailSuccessOptionsFields', 'mpceCFAMailSuccessOptions');
	add_settings_section('mpceCFAMailSuccessOptionsFields', '', 'mpceCFAMailSuccessOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFAMailSuccessOptions', __('Success message', 'mpce-cfa'), 'mpceCFAMailSuccessSettings', 'mpce_cfa_options', 'mpceCFAMailSuccessOptionsFields');

	register_setting('mpceCFAMailFailOptionsFields', 'mpceCFAMailFailOptions');
	add_settings_section('mpceCFAMailFailOptionsFields', '', 'mpceCFAMailFailOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFAMailFailOptions', __('Fail message', 'mpce-cfa'), 'mpceCFAMailFailSettings', 'mpce_cfa_options', 'mpceCFAMailFailOptionsFields');

	register_setting('mpceCFASiteAPIOptionsFields', 'mpceCFASiteAPIOptions');
	add_settings_section('mpceCFASiteAPIOptionsFields', '', 'mpceCFASiteAPIOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFASiteAPIOptions', __('reCAPTCHA Site Key', 'mpce-cfa'), 'mpceCFASiteAPISettings', 'mpce_cfa_options', 'mpceCFASiteAPIOptionsFields');

	register_setting('mpceCFASecretAPIOptionsFields', 'mpceCFASecretAPIOptions');
	add_settings_section('mpceCFASecretAPIOptionsFields', '', 'mpceCFASecretAPIOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFASecretAPIOptions', __('reCAPTCHA Secret Key', 'mpce-cfa'), 'mpceCFASecretAPISettings', 'mpce_cfa_options', 'mpceCFASecretAPIOptionsFields');

	register_setting('mpceCFALangAPIOptionsFields', 'mpceCFALangAPIOptions');
	add_settings_section('mpceCFALangAPIOptionsFields', '', 'mpceCFALangAPIOptionsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFALangAPIOptions', __('reCAPTCHA Language', 'mpce-cfa'), 'mpceCFALangAPISettings', 'mpce_cfa_options', 'mpceCFALangAPIOptionsFields');

	//Google fonts
	register_setting('motopressCFAFormsFields', 'mpceCFAFormsOptions');
	add_settings_section('motopressCFAFormsFields', '', 'motopressCFAFormsSecTxt', 'mpce_cfa_options');
	add_settings_field('mpceCFAFormsOptions', __('E-mail templates', 'mpce-cfa'), 'motopressCFAFormsSettings', 'mpce_cfa_options', 'motopressCFAFormsFields');

}
function mpceCFASecretAPIOptionsSecTxt() {}
function mpceCFASecretAPISettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<input type="password" name="recaptch_secret_key" class="regular-text" style="-webkit-text-security: disc;" value="' . (isset($settings['recaptch_secret_key']) ? $settings['recaptch_secret_key'] : '') . '" />';
}
function mpceCFASiteAPIOptionsSecTxt() {}
function mpceCFASiteAPISettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<input type="text" name="recaptch_site_key" class="regular-text" value="' . (isset($settings['recaptch_site_key']) ? $settings['recaptch_site_key'] : '') . '" />';
	echo '<p class="description">'
		. sprintf(  __('To use reCAPTCHA, you need to <a href="%s" target="_blank">sign up for an API key pair</a> for your site.', 'mpce-cfa'),"http://www.google.com/recaptcha/admin")
		. '</p>';
}
function mpceCFALangAPIOptionsSecTxt() {}
function mpceCFALangAPISettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<input type="text" name="recaptch_lang" class="regular-text" value="' . (isset($settings['recaptch_lang']) ? $settings['recaptch_lang'] : '') . '" />';
	echo '<p class="description">'
		. sprintf( __('Read more about google <a href="%s" target="_blank">language codes</a>.', 'mpce-cfa'),"https://developers.google.com/recaptcha/docs/language")
		. '</p>';
}


function mpceCFAMailRecipientOptionsSecTxt() {}
function mpceCFAMailRecipientSettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<input type="text" name="mpce_cfa_mail_recipient" class="regular-text" placeholder="email@example.com" value="' . (isset($settings['mpce_cfa_mail_recipient']) ? $settings['mpce_cfa_mail_recipient'] : '') . '" />';
}
function mpceCFAMailSenderOptionsSecTxt() {}
function mpceCFAMailSenderSettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<input type="text" name="mpce_cfa_mail_sender" class="regular-text" placeholder="email@example.com" value="' . (isset($settings['mpce_cfa_mail_sender']) ? $settings['mpce_cfa_mail_sender'] : '') . '" />';
}
function mpceCFAMailSubjectOptionsSecTxt() {}
function mpceCFAMailSubjectSettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<input type="text" name="mpce_cfa_mail_subject" class="regular-text" value="' . (isset($settings['mpce_cfa_mail_subject']) ? $settings['mpce_cfa_mail_subject'] : '[form-name] from [blog-name]') . '" />';
	echo '<p class="description">' . __('Use [form-name] and [blog-name] to generate dynamic subject', 'mpce-cfa') . '</p>';
}
function mpceCFAMailSuccessOptionsSecTxt() {}
function mpceCFAMailSuccessSettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<textarea cols="40" rows="3" style="width:100%;max-width:1000px;" name="mpce_cfa_mail_success">' . (isset($settings['mpce_cfa_mail_success']) ? $settings['mpce_cfa_mail_success'] : __("Sender's message was sent successfully", 'mpce-cfa')) . '</textarea>';
}
function mpceCFAMailFailOptionsSecTxt() {}
function mpceCFAMailFailSettings() {
	$settings = get_option('mpce-cfa-settings', array());
	echo '<textarea cols="40" rows="3" style="width:100%;max-width:1000px;"  name="mpce_cfa_mail_fail">' . (isset($settings['mpce_cfa_mail_fail']) ? $settings['mpce_cfa_mail_fail'] : __("Sender's message was failed to send", 'mpce-cfa')) . '</textarea>';
}



// Mail Forms
function motopressCFAFormsSecTxt() {}
function motopressCFAFormsSettings() {
	$mailTemplates = get_option('mpce_cfa_template', array());
	$form_tabs = array();
	$content = '';

	$labels = array(
		'temp' => '<b>' . __('E-mail template', 'mpce-cfa') . '</b><br />',
		'form' => '<b>' . __('Form ID:', 'mpce-cfa') . '</b>',
		'remove' => __('Remove', 'mpce-cfa')
	);

	wp_register_script( 'mpce-cfa-settings', MPCE_CFA_PLUGIN_DIR_URL . 'assets/js/engine-settings.min.js',array('jquery'), MPCE_CFA_VERSION, true);
	wp_localize_script( 'mpce-cfa-settings', 'MPCE_CFA_Lang', array(
		'temp' => $labels['temp'],
		'form' => $labels['form'],
		'remove' => $labels['remove'],
	));
	wp_enqueue_script( 'mpce-cfa-settings');
	wp_enqueue_style( 'mpce-cfa-settings', MPCE_CFA_PLUGIN_DIR_URL . 'assets/css/style-settings.min.css', array(), MPCE_CFA_VERSION);


	foreach ($mailTemplates as $formID => $formBody) {
		$form_tabs[$formID] = $formID;

		$content .=  '<div id="' . $formID . '" class="tab-content">'
			  		 .  '<div class="mpce-cfa-form_id-container">'
					 .  '<span class="mpce-cfa-form_id">' . $labels['form'] . ' ' . $formID . '</span>'
			  		 .  '<button class="mpce-cfa-remove-entry">' . $labels['remove'] . '</button>'
					 .  '</div>'
					 .  '<div class="mpce-cfa-template-details">'
					 .  '<label class="mpce-cfa-container">'
					 .  ' <span> '. $labels['template'] .'</span><textarea rows="7" name="mpce_cfa_template[' . $formID . ']">' . $formBody . '</textarea>'
					 .  '</label>'
					 .  '</div>'
					 . '</div>';
	}

	$headers = mpce_cfa_admin_tabs($form_tabs);

	$creatingBlock = '<input class="class-name" type="text" placeholder="contact-form-id" />'
		. '<button class="mp-create-template-entry">' . __('Add Template', 'mpce-cfa') . '</button>'
		. '<p class="description">' . __('Only numbers, letters, underscores and hyphens are permitted.', 'mpce-cfa') . '</p>';

	$infoBlock =  '<p class="mpce-cfa-form-info"><span class="wrong-form-name hidden">'
		. __('Error: Only numbers, letters, underscores and hyphens are permitted', 'mpce-cfa')  . '</span><span class="duplicate-form-name hidden">'
		. __('Error: Template with this ID already exists', 'mpce-cfa')  . '</span></p>';


	$forms = '<p><label>' . __('Add new e-mail template. Enter contact form ID:', 'mpce-cfa') . '</label></p>'
		.'<div id="mpce-cfa-template-manager-tools">'
		. $creatingBlock
		. $infoBlock
		. '</div>'
		. '<div id="mpce-cfa-tabs-container"><hr>'
		. '<input type="hidden" name="mpce_cfa_dir_writable" value="true">'
		. $headers
		. '<div class="tab">'
		. $content
		. '</div>'
		. '<hr>'
		. '</div>'
		. '<p class="description">' . __('You can use [field-names] from your form in this template. All [field-names] will be replaced with a corresponding field values. Example: Message from [user-name]', 'mpce-cfa') . '</p>';


	echo $forms;
}

function saveCFAForms ($templates){
	
	$oldTemplates = get_option('mpce_cfa_template', array());
	//remove unused files
	$removeClasses = array_diff_key($oldTemplates, $templates);

	foreach($removeClasses as $formID => $formTemplate) {
		if( array_key_exists ( $formID , $oldTemplates )) {
			unset($oldTemplates[ $formID ]);
		}
	}

	foreach ($templates as $formID => $formTemplate) {
		$templates[$formID] = stripslashes(trim($formTemplate));
	}

	foreach ($templates as $formID => $formTemplate) {
		if( !isset($formTemplate) ){
			$templates[$formID] = $oldTemplates[$formID];
		}
	}

	update_option('mpce_cfa_template',$templates);
}

function mpce_cfa_admin_tabs($tabs, $current=NULL){
	if(is_null($current)){
		if(isset($_GET['page'])){
			$current = $_GET['page'];
		}
	}
	$content = '';
	$content .= '<h2 class="nav-tab-wrapper">';
	foreach($tabs as $location => $tabname){
		$class = ($current == $location) ? ' nav-tab-active' : '';
		$content .= '<a class="nav-tab'.$class.'" href="#'.$location.'">'.$tabname.'</a>';
	}
	$content .= '</h2>';
	return $content;
}
























