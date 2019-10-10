<?php

add_action('mpce_add_simple_shortcode', 'mpceCFAAddSimpleShortcode');

function mpceCFAAddSimpleShortcode() {
	add_shortcode('mpce_cfa_contact_form', 'mpceCFABoxShortcodeSimple');
	add_shortcode('mpce_cfa_item', 'mpceCFAItemShortcodeSimple');
}

function mpceCFABoxShortcodeSimple() {
	return '';
}

function mpceCFAItemShortcodeSimple() {
	return '';
}
