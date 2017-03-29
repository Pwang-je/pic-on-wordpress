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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'a1473695');

/** MySQL database username */
define('DB_USER', 'a1473695');

/** MySQL database password */
define('DB_PASSWORD', 'wogns1224');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ' i+WFi+cl+g1^ t8B|W|zhbYL.9?o &U|=EJpYnwt&y#Y_Q/^3 |]B|M$Dj<5%*H');
define('SECURE_AUTH_KEY',  'wG%uJ[dcqA}Nm,,]rSnQ>1Q1&[8fb&-h%K34 xFf-wKh@yjAiY7Bho VdLem]hH0');
define('LOGGED_IN_KEY',    '+U6&?+YUu+,UTFozFn+x4<8S0,5%Y:l|qg&:*c;(/*m/&!v%jLZHZ9i+n_E9I,NI');
define('NONCE_KEY',        '@FNJ~^qX9FtS2&8]Cv}?8}iQK/E0}MYK|D|tSf&b&#Zk2Zc[hc;V+-dBfk)>{(QQ');
define('AUTH_SALT',        'y#~61c.@%c#OH;c6&KZrTtvO!E]KIK|XH: rX.9#dZ*<AHnCZ3T?)m.R-tyK`e:L');
define('SECURE_AUTH_SALT', '`uEPrT5`MtofKxJp;%bmh~^_g=qdqr3xnH{H-Q{h_(_A/R2(~;-++DCBbTX@o`-g');
define('LOGGED_IN_SALT',   '>Ol-neFHW1#3k5b Mr=H+fmgP0@^)L/.4OnRN :*qU.7IGvlFcl-6|XT bPbEN_P');
define('NONCE_SALT',       '<uNZ2<{8of%6fLz3BRXu]m!lP?G_+OU}Mb|AZDH#+H}KnmrmK7u}+fcFj>${xoLv');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WPLANG', 'ko_KR');
$_SERVER['SERVER_SOFTWARE'] = 'Apache';
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
