<?php
    if (!defined('ABSPATH'))
        exit; // Exit if accessed directly
    if (!$errors)
        return;

	foreach($errors as $message) {
		theme_error_message($message);
	}
?>