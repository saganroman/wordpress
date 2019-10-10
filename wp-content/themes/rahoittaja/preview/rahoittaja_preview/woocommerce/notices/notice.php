<?php
/**
 * Show messages
 *
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! $messages ){
    return;
}

?>

<?php foreach ( $messages as $message ) : ?>
    <?php theme_information_message($message); ?>
<?php endforeach; ?>