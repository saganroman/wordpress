<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
@ini_set( 'upload_max_filesize' , '1128M' );
@ini_set( 'post_max_size', '1128M');
@ini_set( 'memory_limit', '1256M' );
@ini_set( 'max_execution_time', '3000' );
@ini_set( 'max_input_time', '3000' );
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'M67*pRS!V~2SB.%[ HVoGik=5)Tshvr&Ai7q]xa:j:d%#q*8Gu6c?a)612:h|w,2' );
define( 'SECURE_AUTH_KEY',  ',R20E3:Dv& B5KdI4/bX07}}w,mlZGsH$<Q[2%Ij{;El2Wl;<sqYw4&r5%&H8{s1' );
define( 'LOGGED_IN_KEY',    'K,sRnulwT?Wu1WYY FkaR-SPkjk/pam/ln7~3H)f*ibY@4|U@b7h8)R3nOH7cgO2' );
define( 'NONCE_KEY',        '?Dm`*a o}wBA=D%d%28u@{+VXp#-puq[c1U,f-uNqg*t.^O1rLhuLhQQiEhHv1b!' );
define( 'AUTH_SALT',        'HFfdR{hIKHAVcJfxQh>ohA~7bT~9UJ@j`LYN/n(.Kng8Re^@3F=9p]4m@[NtAA!8' );
define( 'SECURE_AUTH_SALT', '*8$IL_R|-ryG2vauZ=6m]3m};C^qs-KC{+2U(FjyWgrhGS;nBuw&3{K^C%by|F(y' );
define( 'LOGGED_IN_SALT',   'FJ}L?6%dW&j:2Z/,&vq1gkR6G]PGt2Pom|^+[fLm_y%7MAyQE.4qTO*{fX|=7&Jm' );
define( 'NONCE_SALT',       'A2[pUx=xi)hq?RkQGF~]@v7O&W3jF[@Uo(=H M2;tWQ;dR1O)i/v&?jTP6.6JB+V' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
