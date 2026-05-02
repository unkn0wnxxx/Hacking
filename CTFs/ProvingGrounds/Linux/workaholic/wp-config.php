<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'wpadmin' );

/** MySQL database password */
define( 'DB_PASSWORD', 'rU)tJnTw5*ShDt4nOx' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '%|X7>+ujGW6aeD,T5$V,SdIJ4=G>Wx(,^W|U$)Zb[/3)-*[W:EK+AHH/V Zl?A+8');
define('SECURE_AUTH_KEY',  'lwvqDQt{~|2>9fSbs;^bt,wb+;<lXAr+P@R*/jS}-dqgG]Frb|0_&~!,`||=/o!w');
define('LOGGED_IN_KEY',    '~}m3syWu?K6{s}b`bRn|jf%*z.R<Uoi+RTH65i!y&Wi V)w=B3EzHf %j,+I41|o');
define('NONCE_KEY',        'n _Ay4Rxg&?HxS(WqfU&:-gbl$^~+!7V9@NQb%-{K[}d/i~+`U-1(fN8xb$47]mC');
define('AUTH_SALT',        'pEd>^-5$.Tu=H6(d_E]{6sTF_k!lSEztv,-zhzzPc<yPQqX1c;~irIHpKjj5ZxIE');
define('SECURE_AUTH_SALT', 'AjBwd3Sl{F0+C+3Ma~S9s3fG=-W?mt?x+3Z_3+2&.LCs|!n pX5|ta56$[0-t>bw');
define('LOGGED_IN_SALT',   ' :+Wl:8U!Jyd2zc wEqYKG}Ug?bQ!b$|_:ktrzixd-<,$]9Vl@($5+Gc9Xvx.(Gm');
define('NONCE_SALT',       'i#u-MwU.K;n-.,;GoISHB|l6,{p::ucK!XOUBq)vXj`^>=9 ;Z<[<nNhvvM(}-u~');

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
