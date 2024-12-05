<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache


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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cusom365d' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',         'B>c~bJ/mW!G%91$/0t_]NDgo/0:Cu`f;Mmj9_u|Ky.R~96iFr?F9%_Z?nhj2gRH=' );
define( 'SECURE_AUTH_KEY',  'ur96FvY-DfnTBYA+/N?QJMH*^,)L1lxxcsGGVD?AaawV}GFrxyFt6I%b5u8:0gzn' );
define( 'LOGGED_IN_KEY',    'men:R?1O_;NW9<7Xn=^@*<q +P3Y?&!3XGr89#<rSPt}$8h%AEL T?J-_WPPYg68' );
define( 'NONCE_KEY',        'I2mWLcD0HLNg#WAJW%(4/fp!-q[y{R1}[7}%6cAz,d@*qz~dSw/qj,K7G/yE<OGJ' );
define( 'AUTH_SALT',        '5;HEq7gjMRrMWkp~)!N3*5py%hE8PNNZ#7HYzy|,70*@c)UxS~dyiezgR#X^IG`R' );
define( 'SECURE_AUTH_SALT', '?VZ7u+le;]s7,*nY &&?PcOJ,o6P6R x?R0<acde%UTPVqlqZF+DuKF[b+|zjW]b' );
define( 'LOGGED_IN_SALT',   '.HG0TQ^u$e3>$Xh$dd%PK%w^lkj(PbbXv-,sNqCDllwd}]EOK4ur~$lyuJ:/F`St' );
define( 'NONCE_SALT',       'Z0d4YM)8_qji#sZAgbkI6DDE`PTM`zo10}Xm}4u8$2N~JV[ 8O.=dS%e:1B(|;9v' );


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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

define('WP_DEBUG_LOG', true);

define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

define('FS_METHOD', 'direct');

// Redis configuration
define('WP_REDIS_HOST', '127.0.0.1'); // Redis 服务器地址
define('WP_REDIS_PORT', 6379); // Redis 端口
define('WP_REDIS_DATABASE', 4); // 将 1 替换为你想使用的 Redis 数据库编号
define('WP_REDIS_PASSWORD', '');
/* Add any custom values between this line and the "stop editing" line. */




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
