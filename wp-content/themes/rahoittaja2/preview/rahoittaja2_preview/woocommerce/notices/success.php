<?php
/**
 * Show messages
 *
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! $messages ){
    return;
}

?>

<?php foreach ( $messages as $message ) : ?>
    <?php theme_success_message($message); ?>
<?php endforeach; ?>