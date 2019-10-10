<?php
if (!defined('ABSPATH')) exit;

function mpceContactFormAddonLibrary($mpceLibrary) {

    $tableObj = new MPCEObject('mpce_cfa_contact_form', __('Contact form', 'mpce-cfa'), 'plugins/' . MPCE_CFA_PLUGIN_NAME . '/assets/images/contact-form.png', array(
        'elements' => array(
            'type' => 'group',
            'contains' => 'mpce_cfa_item',
            'items' => array(
                'label' => array(
                    'default' => __('Label', 'mpce-cfa'),
                    'parameter' => 'title'
                ),
                'count' => 1
            ),
            'text' => __('Add New Field', 'mpce-cfa'),
            'disabled' => 'false',
        ),

        'submit' => array(
            'type' => 'text',
            'label' => __('Submit button label', 'mpce-cfa'),
            'default' => __('Submit', 'mpce-cfa'),
        ),

        'captcha' => array(
            'type' => 'checkbox',
            'label' => __('Use reCAPTCHA', 'mpce-cfa'),
            'default' => 'true',
            'description' => __('Protect this form from spam and abuse. Configure Google reCAPTCHA in plugin settings first.', 'mpce-cfa'),
        ),

        'position' => array(
            'type' => 'radio-buttons',
            'label' => __('Alignment', 'mpce-cfa'),
            'default' => 'default',
            'list' => array(
                'default' => __('Default', 'mpce-cfa'),
                'left' => __('Left', 'mpce-cfa'),
                'right' => __('Right', 'mpce-cfa')
            ),
        ),
        'form' => array(
            'type' => 'text',
            'label' => __('Form ID', 'mpce-cfa'),
            'description' => __('Is used in e-mail template', 'mpce-cfa'),
        ),
        'name' => array(
            'type' => 'text',
            'label' => __('Form name', 'mpce-cfa'),
            'default' => __('Contact Form', 'mpce-cfa'),
            'description' => __('Is used in e-mail subject', 'mpce-cfa'),
        ),


    ), 94, MPCEObject::ENCLOSED, MPCEObject::RESIZE_HORIZONTAL);


    $tableItemObj = new MPCEObject('mpce_cfa_item', __('Form Field', 'mpce-cfa'), null, array(
        'title' => array(
            'type' => 'text',
            'label' => __('Label', 'mpce-cfa'),
            'default' => __('Label', 'mpce-cfa'),
        ),
        'item_type' => array(
            'type' => 'select',
            'label' => __('Type', 'mpce-cfa'),
            'default' => 'text',
            'list' => array(
                'text' => __('Text', 'mpce-cfa'),
                'email' => __('E-mail', 'mpce-cfa'),
                'textarea' => __('Text area', 'mpce-cfa'),
                'select' => __('Select', 'mpce-cfa'),
                'tel' => __('Telephone', 'mpce-cfa'),
                'checkbox' => __('Checkboxes', 'mpce-cfa'),
                'radio' => __('Radio buttons', 'mpce-cfa'),
                'number' => __('Number', 'mpce-cfa'),
                'label' => __('Label', 'mpce-cfa'),
            ),
        ),

        'placeholder' => array(
            'type' => 'checkbox',
            'label' => __('Use Label as the placeholder', 'mpce-cfa'),
            'default' => 'false',
            'description' => '',
            'dependency' => array(
                'parameter' => 'item_type',
                'value' => array('text','textarea','email','number','tel')
            )
        ),

        'required' => array(
            'type' => 'checkbox',
            'label' => __('Required', 'mpce-cfa'),
            'default' => 'false',
            'dependency' => array(
                'parameter' => 'item_type',
                'except' => 'label'
            )
        ),

        'check' => array(
            'type' => 'checkbox',
            'label' => __('First option is selected', 'mpce-cfa'),
            'default' => '',
            'description' => '',
            'dependency' => array(
                'parameter' => 'item_type',
                'value' => array('radio', 'checkbox')
            )
        ),

        'select_mult' => array(
            'type' => 'checkbox',
            'label' => __('Multiple options can be selected at once', 'mpce-cfa'),
            'default' => '',
            'dependency' => array(
                'parameter' => 'item_type',
                'value' => 'select'
            )
        ),

        'select_first' => array(
            'type' => 'radio-buttons',
            'label' => __('Use Label as the first option', 'mpce-cfa'),
            'default' => 'empty',
            'list' => array(
                'empty' => __('No', 'mpce-cfa'),
                'label' => __('Yes', 'mpce-cfa')
            ),
            'dependency' => array(
                'parameter' => 'item_type',
                'value' => 'select'
            )
        ),

        'list' => array(
            'type' => 'longtext64',
            'label' => __('Options', 'mpce-cfa'),
            'default' => '',
            'description' => __('One option per line', 'mpce-cfa'),
            'text' => __('Edit', 'mpce-cfa'),
            'dependency' => array(
                'parameter' => 'item_type',
                'value' => array('checkbox','select','radio')
            )
        ),

        //DIFFERENCE
        'name' => array(
            'type' => 'text',
            'label' => __('Field name', 'mpce-cfa'),
            'default' => __('your-name', 'mpce-cfa'),
            'description' => __('Is used in e-mail template', 'mpce-cfa'),
            'dependency' => array(
                'parameter' => 'item_type',
                'except' => 'label'
            )
        ),
        'css_class' => array(
            'type' => 'text',
            'label' => __('CSS Class', 'mpce-cfa'),
            'default' => '',
            'description' => '',
        ),
        'css_id' => array(
            'type' => 'text',
            'label' => __('ID', 'mpce-cfa'),
            'default' => '',
            'description' => '',
        ),

    ), null, MPCEObject::ENCLOSED, MPCEObject::RESIZE_NONE, false);

    $mpceLibrary->addObject($tableObj, MPCEShortcode::PREFIX . 'other');
    $mpceLibrary->addObject($tableItemObj, MPCEShortcode::PREFIX . 'other');
}
add_action('mp_library', 'mpceContactFormAddonLibrary', 11, 1);
