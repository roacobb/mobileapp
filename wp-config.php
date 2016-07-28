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
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wpadmin');

/** MySQL database password */
define('DB_PASSWORD', 'hik21mah');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'psED]PLA/A]P1}y%bQbK2SIqa%a>Aazb@`+3NLo}I|it|*e|Er#CUM$w](2^I8r2');
define('SECURE_AUTH_KEY',  '~M/bzqcoP/$+L55pA/YuSr6JUjrba_?JDb2JnoZp8F,gEkMlYA4hh}bw7(A_s%+h');
define('LOGGED_IN_KEY',    'oK}9.!VeeZb]x_U.H4d|hI+DHnlvU74,nenD9j;I1 #uIusB7OC{ymdd_{}u(n&w');
define('NONCE_KEY',        'Y-}Y1<GupZQnh4)yU36SBzL_2w8iT~iF[f_d*x}qlf O@;f==D1|rYu3U80*x-sk');
define('AUTH_SALT',        '|l.s0bQ/s_cv0K5ne):zi[B*qnGLm>rLn*Dm&ls#pgGl`RqLp~6~qXl(<32K}bZV');
define('SECURE_AUTH_SALT', 'py(;>axsNTDR>SR^&xe)YMZ_*f{-,_*,G DI^g%Z&JA6ES@O Bc@;uuzZhC[()^r');
define('LOGGED_IN_SALT',   'p{2hhJ>FRG5UPk5aE{F}E7uHx>%YWbmz?e+)(/0w*Md-W29(NGUJxwXJ8AR!nrHh');
define('NONCE_SALT',       'Kf2XS;<ZDb;T,IJFEZ<6Qr..(/g,)oEJZ0KExOp$p-a$KY6TG~QZ6JUi/|.Ys1Ez');

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
define('WP_DEBUG', false);
define('FS_METHOD', 'direct'); // for automatic plugin installation

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
