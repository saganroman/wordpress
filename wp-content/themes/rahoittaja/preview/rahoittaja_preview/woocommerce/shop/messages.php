<?php
    if (!defined('ABSPATH'))
        exit; // Exit if accessed directly
    if (!$messages)
        return;

	foreach($messages as $message) {
		theme_success_message($message);
	}
?>