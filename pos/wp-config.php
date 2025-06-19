<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'pos_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '9so*,b>EALhVJl$6t-|HlI*ek,4t>I[*3K;BxPk}sJB n-QN.Fi^Vc&*Go1QzY9!' );
define( 'SECURE_AUTH_KEY',  'FUFMHPB).M ~Ov}s&@YLA58nbQ~H4gEA6#YJz;UtN;qgDA?:VmJ=jB-OI[<p,6gn' );
define( 'LOGGED_IN_KEY',    '5YEbJ}cOs5}MApk?cF Yb>S;&Vq#@W}lEKV8{1 HlZ#~zhnA;JFZjE1|a-@Asz?0' );
define( 'NONCE_KEY',        ',H7jK`Y8DZiNN/|{M*)?jY7=E#g48vgctkq$XhX,O|(p3,@Rpo0Eu(LByX+[Guvj' );
define( 'AUTH_SALT',        'an*JvF>`wRt@<.b~xwh XJ?*=2+&1WNj1i-(n$zr;,2vj?h3?F-e9?TPrfHrqr,^' );
define( 'SECURE_AUTH_SALT', 'KkEcoSg9UOpdPY^__]6Dwd49>Zh$9uROu:dIF8|Z^N=UC!7q3$xAwnPx1QV2:zYx' );
define( 'LOGGED_IN_SALT',   '[9.k,3*?H4ja#$<dyZ.;u_#I|IX9J.mvIi7>rMO{9q>Mfy7[)x2<^-.A[+ivA #N' );
define( 'NONCE_SALT',       'q:<SGMMprN2ewdm.kJAG}Cv5Nlk7SyCP@30g(HLxd0f<NB2=(D%IbOLv7~$TIZeA' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
